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

namespace Mp3StreamTitle\Infrastructure\Http;

use Mp3StreamTitle\Domain\ValueObject\StreamEndpoint;

final readonly class RemoteAddressFactory
{
    public function __construct(
        private StreamEndpoint $endpoint
    ) {
    }

    public function create(): string
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