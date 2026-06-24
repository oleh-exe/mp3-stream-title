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

use LogicException;

final class IcyMetadataBuffer
{
    /**
     * @var string
     */
    private string $buffer = '';

    private ?int $requiredLength = null;

    public function append(string $chunk): bool
    {
        if ($this->requiredLength === null) {
            throw new LogicException(
                'Required length is not initialized'
            );
        }

        // Save the data part into a variable.
        $this->buffer .= $chunk;

        if (strlen($this->buffer) < $this->requiredLength) {
            return false;
        }

        return true;
    }

    public function buffer(): string
    {
        return $this->buffer;
    }

    public function setRequiredLength(int $requiredLength): void
    {
        if ($this->requiredLength !== null) {
            throw new LogicException(
                'Required length already initialized'
            );
        }

        $this->requiredLength = $requiredLength;
    }
}