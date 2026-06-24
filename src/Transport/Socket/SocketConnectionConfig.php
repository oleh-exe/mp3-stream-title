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

namespace Mp3StreamTitle\Transport\Socket;

use InvalidArgumentException;

final readonly class SocketConnectionConfig
{
    /**
     * @var float
     */
    public float $timeout;

    /**
     * @var int
     */
    public int $streamTimeout;

    /**
     * @var int
     */
    public int $readChunkSize;

    /**
     * @var int
     */
    public int $maxHeadersSize;

    public function __construct(
        float $timeout = 30.0,
        int $streamTimeout = 30,
        int $readChunkSize = 8192,
        int $maxHeadersSize = 16384,
    ) {
        if ($timeout <= 0) {
            throw new InvalidArgumentException('Timeout must be greater than 0 seconds');
        }

        if ($streamTimeout <= 0) {
            throw new InvalidArgumentException('Connection timeout must be greater than 0 seconds');
        }

        if ($readChunkSize <= 0) {
            throw new InvalidArgumentException('Read chunk size must be greater than 0 bytes');
        }

        if ($maxHeadersSize <= 0) {
            throw new InvalidArgumentException('Max headers size must be greater than 0 bytes');
        }

        $this->timeout = $timeout;
        $this->streamTimeout = $streamTimeout;
        $this->readChunkSize = $readChunkSize;
        $this->maxHeadersSize = $maxHeadersSize;
    }
}