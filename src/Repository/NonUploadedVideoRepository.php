<?php

namespace PierreMiniggio\YoutubeToMedium\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class NonUploadedVideoRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function findByWebsiteAndYoutubeChannelIds(int $mediumWebsiteId, int $youtubeChannelId): array
    {
        $this->connection->start();

        $postedWebsitePostIds = $this->connection->query('
            SELECT w.id
            FROM medium_post as w
            RIGHT JOIN medium_post_youtube_video as mpyv
            ON w.id = mpyv.medium_id
            WHERE w.website_id = :website_id
        ', ['website_id' => $mediumWebsiteId]);
        $postedWebsitePostIds = array_map(fn ($entry) => (int) $entry['id'], $postedWebsitePostIds);

        $postsToPost = $this->connection->query('
            SELECT
                y.id,
                y.title,
                y.url,
                y.thumbnail,
                y.description,
                y.tags
            FROM youtube_video as y
            ' . (
                $postedWebsitePostIds
                    ? 'LEFT JOIN medium_post_youtube_video as mpyv
                    ON y.id = mpyv.youtube_id
                    AND mpyv.medium_id IN (' . implode(', ', $postedWebsitePostIds) . ')'
                    : ''
            ) . '
            
            WHERE y.channel_id = :channel_id
            ' . ($postedWebsitePostIds ? 'AND mpyv.id IS NULL' : '') . '
            ;
        ', [
            'channel_id' => $youtubeChannelId
        ]);
        $this->connection->stop();

        return $postsToPost;
    }
}
