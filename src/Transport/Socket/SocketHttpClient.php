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

namespace Mp3StreamTitle\Transport\Socket;

use Mp3StreamTitle\Http\Request\HttpRequest;
use Mp3StreamTitle\Http\Request\HttpRequestSerializer;
use Mp3StreamTitle\Http\Response\HttpResponse;
use Mp3StreamTitle\Http\Response\HttpResponseParser;
use RuntimeException;
use Throwable;

final class SocketHttpClient
{
    /**
     * @var SocketConnection
     */
    private SocketConnection $socket;

    /**
     * @var SocketConnectionConfig
     */
    private SocketConnectionConfig $config;

    /**
     * Constructor for initializing the class with a socket connection.
     *
     * @param SocketConnection $socket The socket connection instance.
     *
     * @return void
     */
    public function __construct(SocketConnection $socket, SocketConnectionConfig $config)
    {
        $this->socket = $socket;
        $this->config = $config;
    }

    /**
     * Sends an HTTP request through a socket connection and retrieves the HTTP response.
     *
     * @param HttpRequest $httpRequest The HTTP request to be sent.
     *
     * @return HttpResponse The HTTP response parsed from the server's reply.
     *
     * @throws RuntimeException|Throwable If the HTTP headers exceed the maximum allowed size.
     */
    public function send(HttpRequest $httpRequest): HttpResponse
    {
        $findHeaders = true;
        $buffer = '';
        $maxHeadersSize = $this->config->maxHeadersSize;
        $serializer = new HttpRequestSerializer();
        $httpRequestString = $serializer->toString($httpRequest);

        $this->socket->write($httpRequestString);

        while ($findHeaders) {
            $buffer .= $this->socket->read();

            if (strlen($buffer) > $maxHeadersSize) {
                throw new RuntimeException(
                    sprintf('HTTP headers exceeded maximum allowed size (%d bytes)', $maxHeadersSize)
                );
            }

            if (str_contains($buffer, "\r\n\r\n") !== false) {
                $findHeaders = false;
            }
        }

        $parser = new HttpResponseParser();
        return $parser->parse($buffer);
    }
}