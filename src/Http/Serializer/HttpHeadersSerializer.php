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

namespace Mp3StreamTitle\Http\Serializer;

use Mp3StreamTitle\Http\Request\HeaderCollection;

final readonly class HttpHeadersSerializer
{
    /**
     * Converts the given collection of headers into a formatted string representation.
     *
     * @param HeaderCollection $headers The collection of headers to be converted.
     *
     * @return string The string representation of the headers, where each header is separated by a carriage return and newline.
     */
    public function toString(HeaderCollection $headers): string
    {
        $lines = [];

        foreach ($headers->all() as $name => $value) {
            $lines[] = $name . ': ' . $value;
        }

        return implode("\r\n", $lines);
    }
}