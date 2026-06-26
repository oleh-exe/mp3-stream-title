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

use RuntimeException;

final readonly class FallbackEncodingConverter
{
    public function tryConvertToUtf8(
        string $value,
        array $encodings = [
            'Windows-1252',
            'ISO-8859-1',
            'CP1251',
        ],
    ): string {
        if (!extension_loaded('mbstring')) {
            throw new RuntimeException(
                'The mbstring extension is required'
            );
        }

        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        foreach ($encodings as $encoding) {
            $converted = mb_convert_encoding(
                $value,
                'UTF-8',
                $encoding
            );

            if (mb_check_encoding($converted, 'UTF-8')) {
                return $converted;
            }
        }

        return $value;
    }
}