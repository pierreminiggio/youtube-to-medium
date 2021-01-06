<?php

namespace PierreMiniggio\YoutubeToMedium\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class VideoToUploadRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function insertVideoIfNeeded(
        string $postId,
        int $mediumWebsiteId,
        int $youtubeVideoId
    ): void
    {
        $this->connection->start();
        $postQueryParams = [
            'website_id' => $mediumWebsiteId,
            'post_id' => $postId
        ];
        $findPostIdQuery = ['
            SELECT id FROM website_post
            WHERE website_id = :website_id
            AND post_id = :post_id
            ;
        ', $postQueryParams];
        $queriedIds = $this->connection->query(...$findPostIdQuery);
        
        if (! $queriedIds) {
            $this->connection->exec('
                INSERT INTO website_post (website_id, post_id)
                VALUES (:website_id, :post_id)
                ;
            ', $postQueryParams);
            $queriedIds = $this->connection->query(...$findPostIdQuery);
        }

        $postId = (int) $queriedIds[0]['id'];
        
        $pivotQueryParams = [
            'website_id' => $postId,
            'youtube_id' => $youtubeVideoId
        ];

        $queriedPivotIds = $this->connection->query('
            SELECT id FROM website_post_youtube_video
            WHERE website_id = :website_id
            AND youtube_id = :youtube_id
            ;
        ', $pivotQueryParams);
        
        if (! $queriedPivotIds) {
            $this->connection->exec('
                INSERT INTO website_post_youtube_video (website_id, youtube_id)
                VALUES (:website_id, :youtube_id)
                ;
            ', $pivotQueryParams);
        }

        $this->connection->stop();
    }
}
