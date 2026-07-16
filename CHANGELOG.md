# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

No unreleased changes documented on `master`.

## [1.0.0] - 2026-07-16

### Upgrade Notes

- PHP 8.2 or newer is now required.
- Composer installs now require the PHP cURL extension because `composer.json` declares `ext-curl`, and the default transport is `StreamTransport::CURL`.
- The package now uses Composer PSR-4 autoloading for the `Mp3StreamTitle\` namespace.
- The public entry point changed from `sendRequest($streaming_url)` to `fetchStreamTitle(string $streamingUrl): string`.
- Runtime options are now configured through `Mp3StreamTitleConfig` and `StreamTransport` instead of public mutable properties such as `$send_type`, `$user_agent`, `$show_errors`, and `$meta_max_length`.
- The main library file moved from `Mp3StreamTitle.php` to `src/Mp3StreamTitle.php`. Projects that include the file directly must update the path.

### Added

- Added Composer package metadata with PHP `^8.2`, `ext-curl`, and PSR-4 autoloading.
- Added typed configuration via `Mp3StreamTitleConfig`.
- Added `StreamTransport` enum with `CURL`, `STREAM`, and `SOCKET` transport choices.
- Added `MetadataWatcher` for polling a stream and yielding title changes.
- Added `FallbackEncodingConverter` for converting metadata strings to UTF-8 through fallback encodings.
- Added `HtmlEntityDecoder` for decoding HTML entities in metadata strings.
- Added development dependencies for PHPUnit, PHP_CodeSniffer, and PHPStan.
- Added `.gitignore` with common local, dependency, build, IDE, OS, log, Composer lock, and PHPStan cache exclusions.
- Added `var/.gitkeep` so the PHPStan cache directory parent exists in fresh checkouts.
- Added README badges for Ukrainian support and project origin.

### Changed

- Refactored the library for PHP 8.2 with `strict_types`, typed properties, enums, readonly configuration, and explicit return types.
- Replaced the previous public API with a smaller service API centered on `fetchStreamTitle()`.
- Replaced silent `0` or string error results with exceptions for invalid configuration, connection failures, cURL failures, and stream parsing failures.
- Added stricter stream URL parsing: only HTTP and HTTPS schemes are supported, default ports are resolved, query strings are preserved in the request target, and URLs with userinfo are rejected.
- Added structured HTTP request/response handling, including validated headers, `Icy-MetaData: 1` requests, HTTP status-line parsing, redirect limits, timeouts, and bounded stream reads.
- Reduced the default maximum metadata length from `5228` bytes to `4080` bytes and validates that configured values do not exceed that limit.
- Moved the main library class from `Mp3StreamTitle.php` to `src/Mp3StreamTitle.php`.
- Updated README and example usage for the new `fetchStreamTitle()` API and the `src/Mp3StreamTitle.php` location; Composer PSR-4 autoloading is now available for package consumers.
- Updated the README requirements section and package metadata for PHP 8.2.
- Removed the Buy Me a Coffee section from the README.
- Updated license copyright year.
- Reworked the stream transport to open the connection with `fopen()` and read incrementally from the stream resource instead of relying on a single `file_get_contents()` call with offset and length arguments.

### Removed

- Removed the old public properties and transport-specific public methods: `sendRequest()`, `getSongInfo()`, `getOffset()`, `sendCurl()`, `sendSocket()`, and `sendFGC()`.

## [0.1.0] - 2020-04-05

### Added

- Initial tagged release of the `Mp3StreamTitle\Mp3StreamTitle` class.
- Added support for fetching ICY stream metadata from online radio streams.
- Added transport options using cURL, sockets, or `file_get_contents`.
- Added basic example usage and Apache 2.0 licensing.
