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

namespace Mp3StreamTitle\Infrastructure\Http;

use InvalidArgumentException;
use LogicException;

final class IcyMetadataStreamParser
{
    private ?int $offset = null;

    /**
     * @var string
     */
    private string $buffer = '';

    /**
     * @var string|null
     */
    private ?string $metadata = null;

    public function __construct(
        private readonly int $metaMaxLength
    ) {
        if ($metaMaxLength <= 0) {
            throw new InvalidArgumentException('Meta-max length must be greater than 0');
        }
    }

    public function append(string $chunk): bool
    {
        if ($this->offset === null) {
            throw new LogicException(
                'Metadata offset is not initialized'
            );
        }

        // Save the data part into a variable.
        $this->buffer .= $chunk;

        // Find out how many bytes of data need to get.
        $requiredLength = $this->offset + 1 + $this->metaMaxLength;

        if (strlen($this->buffer) < $requiredLength) {
            return false;
        }

        // Find out the length of the metadata.
        $metaLength = ord($this->buffer[$this->offset]) * 16;

        if ($metaLength === 0) {
            $this->metadata = '';
            return true;
        }

        // ICY metadata block structure:
        // [offset]      = length byte (metadata length / 16)
        // [offset + 1]  = start of actual metadata (e.g., StreamTitle='...';)
        // Metadata format example: StreamTitle='artist name and song name';
        $this->metadata = substr(
            $this->buffer,
            $this->offset + 1,
            $metaLength
        );

        return true;
    }

    public function setOffset(int $offset): void
    {
        if ($this->offset !== null) {
            throw new LogicException(
                'Metadata offset already initialized'
            );
        }

        $this->offset = $offset;
    }

    /**
     * Retrieves the metadata associated with the object.
     *
     * @return string|null The metadata string if available, or null if no metadata is set.
     */
    public function getMetadata(): ?string
    {
        return $this->metadata;
    }
}