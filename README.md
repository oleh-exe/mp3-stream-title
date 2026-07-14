# 🎵 MP3 Stream Title

![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-777bb3.svg?logo=php&logoColor=white)
![License](https://img.shields.io/badge/license-Apache%202.0-green.svg)
[![Packagist Version](https://img.shields.io/packagist/v/oleh-exe/mp3-stream-title.svg)](https://packagist.org/packages/oleh-exe/mp3-stream-title)
[![Total Downloads](https://img.shields.io/packagist/dt/oleh-exe/mp3-stream-title.svg)](https://packagist.org/packages/oleh-exe/mp3-stream-title)

[![Stand with Ukraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/badges/StandWithUkraine.svg)](https://stand-with-ukraine.pp.ua)
[![Made in Ukraine](https://img.shields.io/badge/made_in-Ukraine-ffd700.svg?labelColor=0057b7)](https://stand-with-ukraine.pp.ua)

MP3 Stream Title is a small PHP library for reading the current `StreamTitle`
metadata from Icecast and Shoutcast MP3 radio streams.

It sends an ICY metadata request, reads only the part of the stream needed to
reach the metadata block, and returns the current `StreamTitle` metadata as a string.

## Features

- Fetches current track metadata from online MP3 radio streams.
- Supports cURL, PHP streams, and socket-based transports.
- Uses typed PHP 8.2 APIs, strict validation, and exceptions.
- Parses ICY metadata through focused request, response, and metadata helpers.
- Includes helpers for UTF-8 fallback conversion and HTML entity decoding.
- Provides `MetadataWatcher` for polling a stream and yielding title changes.
- Ships with Composer PSR-4 autoloading.

## Requirements

- PHP 8.2 or newer.
- PHP cURL extension.
- Composer.

`composer.json` declares `ext-curl`, and the default transport is
`StreamTransport::CURL`. Although stream and socket transports are available, the package currently requires the PHP cURL extension.

## Installation

```bash
composer require oleh-exe/mp3-stream-title
```

For development from a checkout, install dependencies first:

```bash
composer install
```

All examples below assume Composer autoloading is available through
`vendor/autoload.php`.

## Usage

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Mp3StreamTitle\Mp3StreamTitle;

$client = new Mp3StreamTitle();

try {
    echo $client->fetchStreamTitle('https://example.com/radio-stream'); // B.B. King - The Thrill Is Gone
} catch (Throwable $exception) {
    echo $exception->getMessage();
}
```

## Transport Configuration

The default transport is cURL. To choose another transport, pass an
`Mp3StreamTitleConfig` instance:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Mp3StreamTitle\Mp3StreamTitle;
use Mp3StreamTitle\Config\Mp3StreamTitleConfig;
use Mp3StreamTitle\Config\StreamTransport;

$client = new Mp3StreamTitle(
    new Mp3StreamTitleConfig(
        streamTransport: StreamTransport::SOCKET,
        userAgent: 'MyRadioApp/1.0',
        metaMaxLength: 4080,
    )
);

echo $client->fetchStreamTitle('https://example.com/radio-stream');
```

Available transports:

- `StreamTransport::CURL`
- `StreamTransport::STREAM`
- `StreamTransport::SOCKET`

`FOLLOWLOCATION` support is available for `StreamTransport::CURL` and
`StreamTransport::STREAM`. `StreamTransport::SOCKET` does not support
`FOLLOWLOCATION` / follow-location behavior at the moment because this feature
is not implemented for the socket transport.

## Metadata Encoding

Some streams publish metadata in legacy encodings or with HTML entities. The
library ships with small helpers that can be used when you need to normalize
metadata values outside the main `fetchStreamTitle()` flow.

The example below uses `hex2bin()` only to create reproducible sample bytes for
the README. In real code, pass the raw metadata string you received from the
stream:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Mp3StreamTitle\Encoding\HtmlEntityDecoder;
use Mp3StreamTitle\Encoding\FallbackEncodingConverter;

$decoder = new HtmlEntityDecoder();
$converter = new FallbackEncodingConverter();

// Example input: raw bytes as they might arrive from an ISO-8859-1 stream
$rawTitle = hex2bin('416e746f6e696f205069f165726f202d204d6174656f');

$title = $decoder->decode(
    $converter->convertToUtf8($rawTitle)
);

echo $title; // Antonio Piñero - Mateo
```

## Watching Title Changes

`MetadataWatcher` polls a stream and yields a title only when it changes:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Mp3StreamTitle\Mp3StreamTitle;
use Mp3StreamTitle\Watcher\MetadataWatcher;

$watcher = new MetadataWatcher(
    client: new Mp3StreamTitle(),
    interval: 10,
);

foreach ($watcher->watch('https://example.com/radio-stream') as $title) {
    echo $title . PHP_EOL;
}
```

## Experimental API

The following APIs are considered experimental and may change or be removed in
future versions without prior notice:

- `Mp3StreamTitle\Encoding\FallbackEncodingConverter`
    - The fallback encoding strategy and supported fallback encodings may evolve
      as additional ICY metadata edge cases are identified.

- `Mp3StreamTitle\Encoding\HtmlEntityDecoder`
    - The entity decoding behavior and integration with metadata normalization
      workflows may change based on real-world usage and feedback.

- `Mp3StreamTitle\Watcher\MetadataWatcher`
    - The metadata watching API, polling behavior, and iteration model may
      change in future versions.

These APIs are provided as optional helpers and are not considered part of the
library's stable core functionality at this time.

## Upgrade Notes

Version `1.0.0` is a PHP 8.2 rewrite and changes the public API.

- Use `fetchStreamTitle(string $streamingUrl): string` instead of
  `sendRequest($streaming_url)`.
- Use `Mp3StreamTitleConfig` and `StreamTransport` instead of mutable public
  properties such as `$send_type`, `$user_agent`, `$show_errors`, and
  `$meta_max_length`.
- Use Composer autoloading instead of requiring only `src/Mp3StreamTitle.php`.
- Handle exceptions instead of checking for `0` or error strings.

## Development

```bash
composer install
vendor/bin/phpcs
vendor/bin/phpstan
```

## Author

[Oleh Kovalenko](https://github.com/oleh-exe)

## License

[Apache 2.0](LICENSE)
