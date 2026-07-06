# MP3 Stream Title

![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-777bb3.svg?logo=php&logoColor=white)
![License](https://img.shields.io/badge/license-Apache%202.0-green.svg)
[![Stand with Ukraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/badges/StandWithUkraine.svg)](https://stand-with-ukraine.pp.ua)
[![Made in Ukraine](https://img.shields.io/badge/made_in-Ukraine-ffd700.svg?labelColor=0057b7)](https://stand-with-ukraine.pp.ua)

MP3 Stream Title is a small PHP library for reading the current `StreamTitle`
metadata from Icecast and Shoutcast MP3 radio streams.

It sends an ICY metadata request, reads only the part of the stream needed to
reach the metadata block, and returns the currently playing title as a string.

## Features

- Fetches current track metadata from online MP3 radio streams.
- Supports cURL, PHP streams, and socket-based transports.
- Uses typed PHP 8.2 APIs, strict validation, and exceptions.
- Parses ICY metadata through focused request, response, and metadata helpers.
- Provides `MetadataWatcher` for polling a stream and yielding title changes.
- Ships with Composer PSR-4 autoloading.

## Requirements

- PHP 8.2 or newer.
- PHP cURL extension.
- Composer is recommended for autoloading.

`composer.json` declares `ext-curl`, and the default transport is
`StreamTransport::CURL`. The library also includes stream and socket transports,
but Composer installs still require cURL unless package metadata is changed.

## Installation

```bash
composer require oleh-exe/mp3-stream-title
```

For development from a checkout, install dependencies first:

```bash
composer install
```

## Usage

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Mp3StreamTitle\Mp3StreamTitle;

$client = new Mp3StreamTitle();

try {
    echo $client->fetchStreamTitle('https://example.com/radio-stream');
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

use Mp3StreamTitle\Config\Mp3StreamTitleConfig;
use Mp3StreamTitle\Config\StreamTransport;
use Mp3StreamTitle\Mp3StreamTitle;

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

## Upgrade Notes

Version `1.0.0` is a PHP 8.2 rewrite and changes the public API.

- Use `fetchStreamTitle(string $streamingUrl): string` instead of
  `sendRequest($streaming_url)`.
- Use `Mp3StreamTitleConfig` and `StreamTransport` instead of mutable public
  properties such as `$send_type`, `$user_agent`, `$show_errors`, and
  `$meta_max_length`.
- Use Composer autoloading instead of requiring only `src/Mp3StreamTitle.php`.
- Handle exceptions instead of checking for `0` or error strings.
- `Radio101RuTitle.php` and `examples/radio101rutitle.php` were removed.

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
