<?php

/**
 * Copyright 2026 Oleh Kovalenko
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Mp3StreamTitle\Config;

use InvalidArgumentException;

final readonly class Mp3StreamTitleConfig
{
    /**
     * @var StreamTransport $streamTransport
     */
    public StreamTransport $streamTransport;

    /**
     * The contents of our "User-Agent" HTTP-header.
     *
     * @var string
     */
    public string $userAgent;

    /**
     * Maximum metadata length in bytes.
     *
     * @var int
     */
    public int $metaMaxLength;

    /**
     * Constructor for initializing the Mp3StreamTitle object with specified parameters.
     *
     * @param StreamTransport $streamTransport The transport type used for sending requests.
     *                                         Default is StreamTransport::CURL.
     * @param string $userAgent The user agent string for HTTP requests. Cannot be empty.
     * @param int $metaMaxLength The maximum length of metadata in bytes. Must not exceed 4080 bytes.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the user agent is empty or metaMaxLength exceeds 4080 bytes.
     */
    public function __construct(
        StreamTransport $streamTransport = StreamTransport::CURL,
        string $userAgent = 'Mp3StreamTitle/1.0 (PHP 8.2; +https://github.com/oleh-exe/mp3-stream-title)',
        int $metaMaxLength = 4080
    ) {
        if ($userAgent === '') {
            throw new InvalidArgumentException('User-Agent cannot be empty');
        }

        if ($metaMaxLength > 4080) {
            throw new InvalidArgumentException('metaMaxLength must be no more than 4080 bytes');
        }

        $this->streamTransport = $streamTransport;
        $this->userAgent = $userAgent;
        $this->metaMaxLength = $metaMaxLength;
    }
}
