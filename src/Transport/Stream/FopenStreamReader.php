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

final class FopenStreamReader
{
    /**
     * @param StreamConnection $stream
     * @param string $initialBuffer
     * @param int $targetLength
     * @param int $maxAllowed
     * @return string
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
     * @param StreamConnection $stream
     * @param string $initialBuffer
     * @param int $targetLength
     * @param int $maxAllowed
     * @return void
     * @throws Throwable
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