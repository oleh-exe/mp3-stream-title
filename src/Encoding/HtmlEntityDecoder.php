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

final class HtmlEntityDecoder
{
    private const FLAGS = ENT_QUOTES | ENT_HTML5;

    private const ENCODING = 'UTF-8';

    /**
     * @param string $value
     * @return string
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
