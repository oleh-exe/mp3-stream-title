<?php

/**
 * Example code from the "Mp3StreamTitle" project
 * Copyright 2020-2026 Oleh Kovalenko
 *
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Note: This is example/demo code. Use at your own risk ("AS IS").
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Mp3StreamTitle\Mp3StreamTitle;

$client = new Mp3StreamTitle();
$streamUrl = 'https://cast1.torontocast.com:4450/stream/1/';

try {
    echo $client->fetchStreamTitle($streamUrl) . PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
