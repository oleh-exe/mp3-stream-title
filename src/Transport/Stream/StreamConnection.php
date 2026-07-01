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

namespace Mp3StreamTitle\Transport\Stream;

use LogicException;
use Mp3StreamTitle\Exception\StreamConnectionException;
use Mp3StreamTitle\Http\Enum\ConnectionState;
use Mp3StreamTitle\Http\Request\StreamContextFactory;
use Mp3StreamTitle\ValueObject\StreamUri;
use Throwable;

final class StreamConnection
{
    /**
     * @var resource|null $fp
     */
    private $fp = null;

    /**
     * @var array|null $httpResponseHeader The HTTP response headers from the last HTTP request,
     *                                     or null if no request was made.
     */
    private ?array $httpResponseHeader = null;

    /**
     * The current state of the connection.
     *
     * @var ConnectionState $state
     */
    private ConnectionState $state = ConnectionState::INITIAL;

    /**
     * @param StreamUri $remoteAddress The remote address of the stream.
     * @param StreamContextFactory $streamContext The factory for creating stream contexts.
     * @param StreamConnectionConfig $config The configuration for the stream connection.
     *
     * @return void
     */
    public function __construct(
        private readonly StreamUri $remoteAddress,
        private readonly StreamContextFactory $streamContext,
        private readonly StreamConnectionConfig $config,
    ) {
    }

    /**
     * Opens a connection to the specified remote address using a stream context
     * and transitions the connection state accordingly.
     *
     * @return void
     *
     * @throws LogicException If the connection cannot be opened due to the current state.
     * @throws StreamConnectionException If the stream operation fails or the connection cannot be established.
     * @throws Throwable If an unexpected error occurs during the connection process.
     */
    public function open(): void
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

        error_clear_last();

        $fp = fopen(
            $this->remoteAddress->toString(),
            'r',
            false,
            $this->streamContext->create()
        );

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

            if (!stream_set_timeout($fp, $this->config->streamTimeout)) {
                throw new StreamConnectionException(
                    'Unable to set stream timeout'
                );
            }

            if (!isset($http_response_header)) {
                throw new StreamConnectionException(
                    'HTTP response headers are not available'
                );
            }

            // TODO: This feature has been DEPRECATED as of PHP 8.5.0
            $this->httpResponseHeader = $http_response_header;
            $this->fp = $fp;
            $this->state = ConnectionState::CONNECTED;
        } catch (Throwable $e) {
            $this->fail($e);
        }
    }

    /**
     * Reads a chunk of data from the stream.
     *
     * @return string The data read from the stream.
     *
     * @throws StreamConnectionException If reading fails, times out, reaches EOF unexpectedly,
     *                                   or produces an empty read.
     * @throws Throwable If an unexpected error occurs during the read process.
     */
    public function read(): string
    {
        $this->assertConnected();

        $this->state = ConnectionState::READING;

        try {
            $chunk = fread($this->fp, $this->config->readChunkSize);

            if ($chunk === false) {
                throw new StreamConnectionException(
                    'Failed to read from stream'
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

    /**
     * Closes the current resource if it is open and updates the connection state.
     *
     * @return void
     */
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

    /**
     * Retrieves the HTTP response headers.
     *
     * @return array The HTTP response headers.
     *
     * @throws LogicException If the response headers are not available.
     */
    public function headers(): array
    {
        if ($this->httpResponseHeader === null) {
            throw new LogicException(
                'Response headers are not available'
            );
        }

        return $this->httpResponseHeader;
    }

    /**
     * Validates if the current state is CONNECTED and that the stream resource is available.
     *
     * @return void
     *
     * @throws LogicException If the state is not CONNECTED or the stream resource is unavailable.
     */
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
                'Stream resource is not available'
            );
        }
    }

    /**
     * Handles a critical failure in the connection and transitions the connection state to an error state.
     *
     * @param Throwable $e The exception causing the failure.
     *
     * @return never Throws the provided exception and does not return.
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
