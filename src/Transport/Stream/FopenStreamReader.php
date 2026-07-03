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

use RuntimeException;
use Throwable;

/**
 * Handles reading data from a stream connection, ensuring data length constraints are respected.
 */
final class FopenStreamReader
{
    /**
     * Reads data from the given stream connection until the specified target length is reached
     * or the maximum allowed size is exceeded.
     *
     * @param StreamConnection $stream The stream connection to read data from.
     * @param string $initialBuffer The initial buffer containing any pre-existing data.
     * @param int $targetLength The desired length of the data to be read.
     * @param int $maxAllowed The maximum allowed size of data to be read.
     *
     * @return string The resulting buffer containing the data read from the stream.
     *
     * @throws Throwable
     */
    public function read(
        StreamConnection $stream,
        string $initialBuffer,
        int $targetLength,
        int $maxAllowed
    ): string {
        if (strlen($initialBuffer) < $targetLength) {
            $this->readUntilLength($stream, $initialBuffer, $targetLength, $maxAllowed);
        }

        return $initialBuffer;
    }

    /**
     * Continues reading data from the provided stream connection until the target length is reached
     * or the maximum allowed size is exceeded.
     *
     * @param StreamConnection $stream The stream connection to read data from.
     * @param string &$initialBuffer A reference to the initial buffer that will be appended with read data.
     * @param int $targetLength The required length of data to be read into the buffer.
     * @param int $maxAllowed The maximum allowable size of the buffer to prevent excessive data reading.
     *
     * @return void
     *
     * @throws RuntimeException|Throwable If the buffer size exceeds the maximum allowed limit.
     */
    private function readUntilLength(
        StreamConnection $stream,
        string &$initialBuffer,
        int $targetLength,
        int $maxAllowed
    ): void {
        while (strlen($initialBuffer) < $targetLength) {
            $initialBuffer .= $stream->read();

            if (strlen($initialBuffer) > $maxAllowed) {
                throw new RuntimeException(
                    sprintf('Stream body exceeded maximum allowed length (%d bytes)', $maxAllowed)
                );
            }
        }
    }
}
