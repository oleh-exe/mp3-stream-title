<?php

/**
 * Example code from the "Mp3StreamTitle" project
 * Copyright 2026 Oleh Kovalenko
 *
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Note: This is example/demo code. Use at your own risk ("AS IS").
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Mp3StreamTitle\Mp3StreamTitle;
use Mp3StreamTitle\Watcher\MetadataWatcher;

$watcher = new MetadataWatcher(
    client: new Mp3StreamTitle(),
    interval: 10, // In seconds
);

$streamUrl = 'https://cast1.torontocast.com:4450/stream/1/'; // Blues Never Die

try {
    foreach ($watcher->watch($streamUrl) as $title) {
        echo '[' . date('H:i:s') . '] ' . $title . PHP_EOL;
    }
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
