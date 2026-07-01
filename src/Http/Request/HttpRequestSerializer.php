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

namespace Mp3StreamTitle\Http\Request;

use Mp3StreamTitle\Http\Serializer\HttpHeadersSerializer;

final readonly class HttpRequestSerializer
{
    /**
     * @var HttpHeadersSerializer
     */
    private HttpHeadersSerializer $headersSerializer;

    /**
     * Initializes a new instance of the class and sets up the headers serializer.
     *
     * @return void
     */
    public function __construct()
    {
        $this->headersSerializer = new HttpHeadersSerializer();
    }

    /**
     * Converts the provided HttpRequest object into a string representation of an HTTP request.
     *
     * @param HttpRequest $request The request object containing method, target, version, and headers.
     *
     * @return string The formatted HTTP request as a string.
     */
    public function toString(HttpRequest $request): string
    {
        return sprintf(
            "%s %s HTTP/%s\r\n%s\r\n\r\n",
            $request->method()->value,
            $request->target(),
            $request->version()->value,
            $this->headersSerializer->toString($request->headers())
        );
    }
}
