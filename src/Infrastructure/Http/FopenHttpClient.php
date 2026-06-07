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

use Mp3StreamTitle\Infrastructure\Http\Request\HeaderCollection;
use Throwable;

final class FopenHttpClient
{
    /**
     * @var StreamConnection
     */
    private StreamConnection $stream;

    public function __construct(StreamConnection $stream)
    {
        $this->stream = $stream;
    }

    /**
     * @return HttpResponse
     * @throws Throwable
     */
    public function read(): HttpResponse
    {
        $headers = array();

        foreach ($this->stream->httpResponseHeader() as $line) {
            if (!str_contains($line, ':')) {
                continue; // HTTP/1.0 200 OK
            }

            [$name, $value] = explode(':', $line, 2);

            $normalizedName = strtolower(trim($name));

            $headers[$normalizedName] = trim($value);
        }

        $headers = new HeaderCollection($headers);

        return new HttpResponse(
            protocolVersion: '',
            statusCode: 1,
            reason: '',
            headers: $headers,
            body: '',
        );
    }
}