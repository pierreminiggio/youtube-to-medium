<?php

namespace PierreMiniggio\YoutubeToMedium\Connection;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class DatabaseConnectionFactory
{
    public function makeFromConfig(array $config): DatabaseConnection
    {
        
        return new DatabaseConnection(
            $config['host'],
            $config['database'],
            $config['username'],
            $config['password'],
            DatabaseConnection::UTF8_MB4
        );
    }
}
