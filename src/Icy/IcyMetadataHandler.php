<?php

/**
 * Copyright 2026 Oleh Kovalenko
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Mp3StreamTitle\Icy;

final class IcyMetadataHandler
{
    /**
     * Constructor method for the class.
     *
     * @param IcyMetadataBuffer $buffer Instance of IcyMetadataBuffer.
     *
     * @return void
     */
    public function __construct(
        private readonly IcyMetadataBuffer $buffer,
    ) {
    }

    /**
     * Handles the given data chunk and appends it to the buffer.
     *
     * @param string $chunk The data chunk to be processed.
     *
     * @return bool True if the buffer is full, false otherwise.
     */
    public function handle(string $chunk): bool
    {
        return !$this->buffer->append($chunk);
    }
}
