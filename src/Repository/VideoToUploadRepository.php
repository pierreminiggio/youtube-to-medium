<?php

namespace PierreMiniggio\YoutubeToMedium\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class VideoToUploadRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function insertVideoIfNeeded(
        string $mediumPostId,
        int $mediumWebsiteId,
        int $youtubeVideoId
    ): void
    {
        $this->connection->start();
        $postQueryParams = [
            'website_id' => $mediumWebsiteId,
            'post_id' => $mediumPostId
        ];
        $findPostIdQuery = ['
            SELECT id FROM medium_post
            WHERE website_id = :website_id
            AND post_id = :post_id
            ;
        ', $postQueryParams];
        $queriedIds = $this->connection->query(...$findPostIdQuery);
        
        if (! $queriedIds) {
            $this->connection->exec('
                INSERT INTO medium_post (website_id, post_id)
                VALUES (:website_id, :post_id)
                ;
            ', $postQueryParams);
            $queriedIds = $this->connection->query(...$findPostIdQuery);
        }

        $postId = (int) $queriedIds[0]['id'];
        
        $pivotQueryParams = [
            'medium_id' => $postId,
            'youtube_id' => $youtubeVideoId
        ];

        $queriedPivotIds = $this->connection->query('
            SELECT id FROM medium_post_youtube_video
            WHERE medium_id = :medium_id
            AND youtube_id = :youtube_id
            ;
        ', $pivotQueryParams);
        
        if (! $queriedPivotIds) {
            $this->connection->exec('
                INSERT INTO medium_post_youtube_video (medium_id, youtube_id)
                VALUES (:medium_id, :youtube_id)
                ;
            ', $pivotQueryParams);
        }

        $this->connection->stop();
    }
}
