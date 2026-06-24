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

namespace Mp3StreamTitle\Transport\Stream;

use InvalidArgumentException;

final readonly class StreamConnectionConfig
{
    /**
     * @var float
     */
    public float $timeout;

    /**
     * @var int
     */
    public int $followLocation;

    /**
     * @var int
     */
    public int $maxRedirects;

    /**
     * @var int
     */
    public int $streamTimeout;

    /**
     * @var int
     */
    public int $readChunkSize;

    public function __construct(
        float $timeout = 30.0,
        int $followLocation = 1,
        int $maxRedirects = 5,
        int $streamTimeout = 30,
        int $readChunkSize = 8192,
    ) {
        if ($timeout <= 0) {
            throw new InvalidArgumentException('Timeout must be a positive number');
        }

        if (!in_array($followLocation, [0, 1], true)) {
            throw new InvalidArgumentException('followLocation must be 0 or 1');
        }

        if ($maxRedirects < 0) {
            throw new InvalidArgumentException('maxRedirects must be greater than or equal to 0');
        }

        if ($streamTimeout <= 0) {
            throw new InvalidArgumentException('Connection timeout must be greater than 0 seconds');
        }

        if ($readChunkSize <= 0) {
            throw new InvalidArgumentException('Read chunk size must be greater than 0 bytes');
        }

        $this->timeout = $timeout;
        $this->followLocation = $followLocation;
        $this->maxRedirects = $maxRedirects;
        $this->streamTimeout = $streamTimeout;
        $this->readChunkSize = $readChunkSize;
    }
}