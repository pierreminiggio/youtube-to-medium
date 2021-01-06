<?php

require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PierreMiniggio\YoutubeToMedium\App;

try {
    exit((new App())->run());
} catch (Throwable $e) {
    echo get_class($e) . ' : ' . $e->getMessage();
    exit;
}
