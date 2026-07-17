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

use Mp3StreamTitle\Encoding\HtmlEntityDecoder;
use Mp3StreamTitle\Encoding\FallbackEncodingConverter;

$decoder = new HtmlEntityDecoder();

// Example input: raw bytes as they might arrive from an ISO-8859-1 stream
$rawTitle = hex2bin('416e746f6e696f205069f165726f202d204d6174656f');

try {
    $converter = new FallbackEncodingConverter();

    if ($rawTitle === false) {
        throw new RuntimeException('Invalid example data');
    }

    echo $decoder->decode(
        $converter->convertToUtf8($rawTitle)
    ) . PHP_EOL; // Antonio Piñero - Mateo
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
