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

use InvalidArgumentException;
use RuntimeException;

/**
 * A class that provides functionality to convert a given string to UTF-8 encoding
 * using a list of fallback encodings. This is useful in scenarios where the
 * source encoding of the input string is unknown or unreliable.
 *
 * @experimental This API is not yet considered stable. The fallback encoding
 * strategy and supported encodings may change in future versions.
 */
final readonly class FallbackEncodingConverter
{
    /**
     * Constructor method for initializing encodings.
     *
     * @param array<int, string> $encodings An array of encoding schemes. Must contain at least one encoding.
     *
     * @return void
     *
     * @throws InvalidArgumentException If no encodings are provided.
     */
    public function __construct(
        private array $encodings = [
            'ISO-8859-1',
            'Windows-1252',
            'CP1251',
        ],
    ) {
        if ($encodings === []) {
            throw new InvalidArgumentException(
                'At least one encoding must be specified'
            );
        }
    }

    /**
     * Converts the input string to UTF-8 encoding.
     *
     * @param string $value The input string to be converted.
     *
     * @return string The UTF-8 encoded string. If conversion fails, returns the original string.
     *
     * @throws RuntimeException If the mbstring extension is not loaded.
     */
    public function convertToUtf8(string $value): string
    {
        if (!extension_loaded('mbstring')) {
            throw new RuntimeException(
                'The mbstring extension is required'
            );
        }

        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        foreach ($this->encodings as $encoding) {
            $converted = mb_convert_encoding(
                $value,
                'UTF-8',
                $encoding
            );

            if ($converted === false) {
                throw new RuntimeException(
                    sprintf('Failed to convert string to UTF-8 using encoding "%s"', $encoding)
                );
            }

            if (mb_check_encoding($converted, 'UTF-8')) {
                return $converted;
            }
        }

        return $value;
    }
}
