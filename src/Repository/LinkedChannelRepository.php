<?php

namespace PierreMiniggio\YoutubeToMedium\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class LinkedChannelRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function findAll(): array
    {
        $this->connection->start();
        $channels = $this->connection->query('
            SELECT
            mwyc.youtube_id as y_id,
                w.id as w_id,
                w.token
            FROM medium_website as w
            RIGHT JOIN medium_website_youtube_channel as mwyc
                ON w.id = mwyc.medium_id
        ', []);
        $this->connection->stop();

        return $channels;
    }
}
