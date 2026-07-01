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

namespace Mp3StreamTitle\Http\Response;

use LogicException;

final class HttpHeaderBuffer
{
    /**
     * Variable to hold the buffer content.
     *
     * @var string $buffer
     */
    private string $buffer = '';

    /**
     * Appends the given header to the internal buffer.
     *
     * @param string $header The header string to append.
     *
     * @return bool Returns true if the header is exactly "\r\n", otherwise false.
     */
    public function append(string $header): bool
    {
        $this->buffer .= $header;

        return $header === "\r\n";
    }

    /**
     * Retrieves the content of the internal buffer.
     *
     * @return string Returns the content of the buffer.
     *
     * @throws LogicException If the buffer is empty.
     */
    public function buffer(): string
    {
        if ($this->buffer === '') {
            throw new LogicException(
                'Response headers are not available'
            );
        }

        return $this->buffer;
    }
}
