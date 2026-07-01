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

require_once dirname(__DIR__) . '/src/Mp3StreamTitle.php';

use Mp3StreamTitle\Mp3StreamTitle;

$client = new Mp3StreamTitle();

try {
    var_dump($client->fetchStreamTitle('https://cast1.torontocast.com:4450/stream/1/'));
} catch (Throwable $e) {
    var_dump($e->getMessage());
}
