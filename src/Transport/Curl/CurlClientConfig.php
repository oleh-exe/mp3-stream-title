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

namespace Mp3StreamTitle\Transport\Curl;

use InvalidArgumentException;

final readonly class CurlClientConfig
{
    /**
     * @var int $timeout
     */
    public int $timeout;

    /**
     * @var int $connectTimeout
     */
    public int $connectTimeout;

    /**
     * @var bool $followLocation
     */
    public bool $followLocation;

    /**
     * @var int $maxRedirects
     */
    public int $maxRedirects;

    /**
     * @var bool $verifyPeer
     */
    public bool $verifyPeer;

    /**
     * @var int $verifyHost
     */
    public int $verifyHost;

    /**
     * Constructor for the class.
     *
     * @param int $timeout The timeout duration in seconds for the request.
     * @param int $connectTimeout The connection timeout duration in seconds.
     * @param bool $followLocation Determines whether to follow redirects. Must be a boolean. Defaults to true.
     * @param int $maxRedirects The maximum number of redirects to follow. Must be greater than or equal to -1 (where -1 indicates no limit). Defaults to 5.
     * @param bool $verifyPeer Whether to verify the SSL certificate of the peer.
     * @param int $verifyHost The level of host verification to perform (must be 0 or 2).
     *
     * @return void
     *
     * @throws InvalidArgumentException If any of the provided parameters are invalid.
     */
    public function __construct(
        int $timeout = 30,
        int $connectTimeout = 10,
        bool $followLocation = true,
        int $maxRedirects = 5,
        bool $verifyPeer = true,
        int $verifyHost = 2,
    ) {
        if ($timeout <= 0) {
            throw new InvalidArgumentException('Timeout must be greater than 0 seconds');
        }

        if ($connectTimeout <= 0) {
            throw new InvalidArgumentException('Connection timeout must be greater than 0 seconds');
        }

        if (!is_bool($followLocation)) {
            throw new InvalidArgumentException('followLocation must be true or false');
        }

        if ($maxRedirects < -1) {
            throw new InvalidArgumentException('maxRedirects must be greater than or equal to -1');
        }

        if (!in_array($verifyHost, [0, 2], true)) {
            throw new InvalidArgumentException('verifyHost must be 0 or 2');
        }

        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;
        $this->followLocation = $followLocation;
        $this->maxRedirects = $maxRedirects;
        $this->verifyPeer = $verifyPeer;
        $this->verifyHost = $verifyHost;
    }
}