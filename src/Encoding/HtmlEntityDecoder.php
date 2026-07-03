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

namespace Mp3StreamTitle\Encoding;

/**
 * A utility class for decoding HTML entities in a string.
 *
 * This class provides a method to decode HTML entities into their corresponding characters. It uses
 * specific flags and encoding to handle the transformation.
 */
final class HtmlEntityDecoder
{
    /**
     * A constant that combines ENT_QUOTES and ENT_HTML5,
     * used for handling quotes and HTML5 entities in certain functions.
     */
    private const FLAGS = ENT_QUOTES | ENT_HTML5;

    /**
     * The encoding used for decoding HTML entities.
     */
    private const ENCODING = 'UTF-8';

    /**
     * Decodes HTML entities in the given string based on specified flags and encoding.
     *
     * @param string $value The string containing HTML entities to decode.
     *
     * @return string The decoded string.
     */
    public function decode(string $value): string
    {
        // If there are no entities there, we don't touch them
        if (!str_contains($value, '&')) {
            return $value;
        }

        return html_entity_decode(
            $value,
            self::FLAGS,
            self::ENCODING
        );
    }
}
