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

namespace Mp3StreamTitle\ValueObject;

final readonly class StreamUri
{
    /**
     * Constructor method.
     *
     * @param StreamEndpoint $endpoint The stream endpoint instance.
     *
     * @return void
     */
    public function __construct(
        private StreamEndpoint $endpoint
    ) {
    }

    /**
     * Converts the endpoint information to a string representation.
     *
     * @return string The formatted remote address as a string.
     */
    public function toString(): string
    {
        $remoteAddress = sprintf('%s://%s', $this->endpoint->getScheme()->value, $this->endpoint->getHost());

        // Add port if it's not the default port for the scheme
        if ($this->endpoint->getPort() !== 80 && $this->endpoint->getPort() !== 443) {
            $remoteAddress .= ':' . $this->endpoint->getPort();
        }

        $remoteAddress .= $this->endpoint->getRequestTarget();

        return $remoteAddress;
    }
}
