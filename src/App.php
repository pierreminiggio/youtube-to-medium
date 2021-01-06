<?php

namespace PierreMiniggio\YoutubeToMedium;

use PierreMiniggio\YoutubeToMedium\Connection\DatabaseConnectionFactory;
use PierreMiniggio\YoutubeToMedium\Repository\LinkedChannelRepository;
use PierreMiniggio\YoutubeToMedium\Repository\NonUploadedVideoRepository;
use PierreMiniggio\YoutubeToMedium\Repository\VideoToUploadRepository;

class App
{
    public function run(): int
    {
        $code = 0;

        $config = require(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.php');

        if (empty($config['db'])) {
            echo 'No DB config';

            return $code;
        }

        $databaseConnection = (new DatabaseConnectionFactory())->makeFromConfig($config['db']);
        $channelRepository = new LinkedChannelRepository($databaseConnection);
        $nonUploadedVideoRepository = new NonUploadedVideoRepository($databaseConnection);
        $videoToUploadRepository = new VideoToUploadRepository($databaseConnection);

        $linkedChannels = $channelRepository->findAll();

        if (! $linkedChannels) {
            echo 'No linked channels';

            return $code;
        }

        foreach ($linkedChannels as $linkedChannel) {
            echo PHP_EOL . PHP_EOL . 'Checking medium website ' . $linkedChannel['w_id'] . '...';

            $postsToPost = $nonUploadedVideoRepository->findByWebsiteAndYoutubeChannelIds($linkedChannel['w_id'], $linkedChannel['y_id']);
            echo PHP_EOL . count($postsToPost) . ' post(s) to post :' . PHP_EOL;

            echo PHP_EOL . 'Getting Medium\'s account id ...';

            $accountIdCurl = curl_init();
            curl_setopt_array($accountIdCurl, [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => 'https://api.medium.com/v1/me',
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $linkedChannel['token']
                ]
            ]);
            $accountIdResult = curl_exec($accountIdCurl);

            if (! $accountIdResult) {
                echo PHP_EOL . 'Failed to get Medium\'s account id, skipping !';
                continue;
            }

            $accountIdJsonResult = json_decode($accountIdResult, true);

            if (
                ! $accountIdJsonResult
                || ! isset($accountIdJsonResult['data'])
                || ! isset($accountIdJsonResult['data']['id'])
            ) {
                echo PHP_EOL . 'Failed to get Medium\'s account id, skipping !';
                continue;
            }

            echo PHP_EOL . 'Got Medium\'s account id !';

            $mediumAccountId = $accountIdJsonResult['data']['id'];
            
            foreach ($postsToPost as $postToPost) {
                echo PHP_EOL . 'Posting ' . $postToPost['title'] . ' ...';

                $youtubeTags = json_decode($postToPost['tags']);
                $mediumTags = [];
                
                if (count($youtubeTags) <= 5) {
                    $mediumTags = $youtubeTags;
                } else {
                    while (count($mediumTags) < 5) {
                        $randomTagKey = array_rand($youtubeTags);
                        $randomTag = $youtubeTags[$randomTagKey];

                        if (! in_array($randomTag, $mediumTags)) {
                            $mediumTags[] = $randomTag;
                        }
                    }
                }

                $description = $postToPost['description'];
                $splitedDescription = explode('Tags :', $description);
                
                if (count($splitedDescription) > 1) {
                    $description = $splitedDescription[0];
                }
        
                $matches = [];
                preg_match_all('@((www|http://|https://)[^ ]+)@', $description, $matches);
                $linksToHref = array_unique(array_filter(array_map(
                    fn ($link) => (trim(preg_split('/\r\n|\r|\n/', trim($link))[0])),
                    array_merge(...$matches)
                ), fn ($link) => $link !== 'http://' && $link !== 'https://'));
        
                foreach ($linksToHref as $linkToHref) {
                    $description = str_replace(
                        $linkToHref,
                        '<a href="'
                        . $linkToHref
                        . '" target="_blank">'
                        . str_replace(
                            'http://',
                            '',
                            str_replace(
                                'https://',
                                '',
                                $linkToHref
                            )
                        )
                        . '</a>',
                        $description
                    );
                }
        
                $description = '<p>' . implode('</p><p>', $this->breakLines($description)) . '</p>';
                $description = '<h1>' . $postToPost['title'] . '</h1>'
                    . '<iframe src="' . $postToPost['url'] . '"></iframe>'
                    . $description
                ;

                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_URL => 'https://api.medium.com/v1/users/' . $mediumAccountId . '/posts',
                    CURLOPT_POST => 1,
                    CURLOPT_POSTFIELDS => json_encode([
                        'title' => $postToPost['title'],
                        'contentFormat' => 'html',
                        'content' => $description,
                        'canonicalUrl' => $postToPost['url'],
                        'tags' => $mediumTags,
                        'publishStatus' => 'public',
                        'notifyFollowers' => true
                    ]),
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $linkedChannel['token']
                    ]
                ]);

                $curlResult = curl_exec($curl);
                $res = json_decode($curlResult, true);

                if (isset($res['data']) && isset($res['data']['id'])) {
                    $videoToUploadRepository->insertVideoIfNeeded(
                        $res['data']['id'],
                        $linkedChannel['w_id'],
                        $postToPost['id']
                    );
                    echo PHP_EOL . $postToPost['title'] . ' posted !';
                } else {
                    echo PHP_EOL . 'Error while posting ' . $postToPost['title'] . ':' . $curlResult;
                }
            }

            echo PHP_EOL . PHP_EOL . 'Done for medium website ' . $linkedChannel['w_id'] . ' !';
        }

        return $code;
    }

    private function breakLines(string $input): array
    {
        return preg_split('/\r\n|\r|\n/', $input);
    }
}
