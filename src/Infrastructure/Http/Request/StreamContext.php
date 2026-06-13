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

namespace Mp3StreamTitle\Infrastructure\Http\Request;

use InvalidArgumentException;
use Mp3StreamTitle\Infrastructure\Http\HttpHeadersSerializer;

final readonly class StreamContext
{
    private HttpHeadersSerializer $headersSerializer;

    public function __construct(
        private HttpRequest $request,
        private HttpHeadersSerializer $serializer,
        private float $timeout,
    ) {
        if ($timeout <= 0) {
            throw new InvalidArgumentException(
                'Timeout must be greater than 0 seconds'
            );
        }
    }

    public function create()
    {
        return stream_context_create([
            'http' => [
                'method' => $this->request->method()->value,
                'header' => $this->serializer->toString($this->request->headers()),
                'follow_location' => 1,
                'max_redirects' => 5,
                'protocol_version' => $this->request->version()->value,
                'timeout' => $this->timeout,
            ],
        ]);
    }
}