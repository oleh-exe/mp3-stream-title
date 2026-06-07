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

use InvalidArgumentException;
use LogicException;
use Mp3StreamTitle\Domain\ValueObject\Scheme;
use Mp3StreamTitle\Domain\ValueObject\Transport;
use Mp3StreamTitle\Exception\Http\StreamConnectionException;
use Mp3StreamTitle\Infrastructure\Http\Enum\ConnectionState;
use Mp3StreamTitle\Infrastructure\Http\Request\HttpRequest;
use Throwable;

final class StreamConnection
{
    /**
     * @var resource|null
     */
    private $fp = null;

    /**
     * The current state of the connection.
     *
     * @var ConnectionState $state
     */
    private ConnectionState $state = ConnectionState::INITIAL;

    /**
     * @param Scheme $scheme
     * @param string $host
     * @param int $port
     * @param string $target
     * @param int $timeout
     */
    public function __construct(
        private readonly Scheme $scheme,
        private readonly string $host,
        private readonly int $port,
        private readonly string $target,
        private readonly int $timeout
    ) {
        if ($timeout <= 0) {
            throw new InvalidArgumentException(
                'Timeout must be greater than 0 seconds'
            );
        }
    }

    /**
     * @param HttpRequest $request
     * @return void
     * @throws Throwable
     */
    public function open(HttpRequest $request): void
    {
        if ($this->state === ConnectionState::ERROR) {
            throw new LogicException(
                'Connection cannot be reused after failure'
            );
        }

        if (
            !in_array($this->state, [ConnectionState::INITIAL, ConnectionState::CLOSED], true)
        ) {
            throw new LogicException(
                sprintf(
                    'Connection cannot be opened from state %s',
                    $this->state->value
                )
            );
        }

        $this->state = ConnectionState::CONNECTING;

        $remoteAddress = sprintf('%s://%s', $this->scheme->value, $this->host);

        // Add port if it's not the default port for the scheme
        if ($this->port !== 80 && $this->port !== 443) {
            $remoteAddress .= ':' . $this->port;
        }

        $remoteAddress .= $this->target;

        error_clear_last();

        $headersSerializer = new HttpHeadersSerializer();

        $context = stream_context_create([
            'http' => [
                'method' => $request->method()->value,
                'timeout' => $this->timeout,
                'header' => $headersSerializer->toString($request->headers()),
                //'header'  => "User-Agent: MyApp\r\n",
            ],
        ]);

        $fp = fopen($remoteAddress, 'r', false, $context);

        if ($fp === false) {
            $error = error_get_last();

            $errorMessage = sprintf(
                'Connection failed: %s',
                $error['message'] ?? 'Unknown error'
            );

            $this->fail(new StreamConnectionException($errorMessage));
        }

        try {
            if (!stream_set_blocking($fp, true)) {
                throw new StreamConnectionException(
                    'Unable to set stream to blocking mode'
                );
            }

            if (!stream_set_timeout($fp, $this->timeout)) {
                throw new StreamConnectionException(
                    'Unable to set stream timeout'
                );
            }

            $this->fp = $fp;
            $this->state = ConnectionState::CONNECTED;
        } catch (Throwable $e) {
            $this->fail($e);
        }
    }

    /*
    public function write(string $data): void
    {
        $this->assertConnected();

        $this->state = ConnectionState::WRITING;

        try {
            $length = strlen($data);
            $written = 0;

            while ($written < $length) {
                $chunk = substr($data, $written);
                $chunkLength = strlen($chunk);

                $bytes = fwrite($this->fp, $chunk);

                if ($bytes === false || $bytes === 0) {
                    throw new StreamConnectionException(
                        sprintf(
                            'Socket write failed (attempted %d bytes)',
                            $chunkLength
                        )
                    );
                }

                $written += $bytes;
            }

            $this->state = ConnectionState::CONNECTED;
        } catch (Throwable $e) {
            $this->fail($e);
        }
    }
    */

    /*
    public function read(): string
    {
        $this->assertConnected();

        $this->state = ConnectionState::READING;

        try {
            $length = 8192;
            $chunk = fread($this->fp, $length);

            if ($chunk === false) {
                throw new StreamConnectionException(
                    'Socket read failed'
                );
            }

            if ($chunk === '') {
                $meta = stream_get_meta_data($this->fp);

                if ($meta['timed_out']) {
                    throw new StreamConnectionException(
                        'Read timeout'
                    );
                }

                if ($meta['eof']) {
                    throw new StreamConnectionException(
                        'Unexpected EOF'
                    );
                }

                throw new StreamConnectionException(
                    'Empty read without EOF or timeout'
                );
            }

            $this->state = ConnectionState::CONNECTED;

            return $chunk;
        } catch (Throwable $e) {
            $this->fail($e);
        }
    }
    */

    /*
    public function close(): void
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }

        $this->fp = null;

        if ($this->state !== ConnectionState::ERROR) {
            $this->state = ConnectionState::CLOSED;
        }
    }
    */

    /*
    private function assertConnected(): void
    {
        if ($this->state !== ConnectionState::CONNECTED) {
            throw new LogicException(
                sprintf(
                    'Invalid state: expected CONNECTED, received %s',
                    $this->state->value
                )
            );
        }

        if (!is_resource($this->fp)) {
            throw new LogicException(
                'Socket resource is not available'
            );
        }
    }
    */

    /**
     * Handles a critical failure in the connection by closing the resource, clearing
     * the internal state, and throwing the provided exception.
     *
     * @param Throwable $e The exception to be thrown indicating the failure.
     *
     * @return never This method does not return a value as it always throws an exception.
     *
     * @throws Throwable
     */
    private function fail(Throwable $e): never
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }

        $this->fp = null;
        $this->state = ConnectionState::ERROR;

        throw $e;
    }
}