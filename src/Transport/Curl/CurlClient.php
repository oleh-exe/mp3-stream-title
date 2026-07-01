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

namespace Mp3StreamTitle\Transport\Curl;

use Mp3StreamTitle\Exception\CurlHttpException;
use Mp3StreamTitle\Http\Request\HttpRequest;
use Mp3StreamTitle\Icy\IcyHeaderHandler;
use Mp3StreamTitle\Icy\IcyMetadataHandler;
use Mp3StreamTitle\ValueObject\StreamUri;

readonly class CurlClient
{
    /**
     * @var StreamUri $remoteAddress
     */
    private StreamUri $remoteAddress;

    /**
     * @var HttpRequest $request
     */
    private HttpRequest $request;

    /**
     * @var CurlClientConfig $curlClientConfig
     */
    private CurlClientConfig $curlClientConfig;

    /**
     * @var CurlHeaderSerializer $headerSerializer
     */
    private CurlHeaderSerializer $headerSerializer;

    /**
     * @var IcyHeaderHandler $headerHandler
     */
    private IcyHeaderHandler $headerHandler;

    /**
     * @var IcyMetadataHandler $metadataHandler
     */
    private IcyMetadataHandler $metadataHandler;

    /**
     * Constructor method for initializing the class with required dependencies.
     *
     * @param StreamUri $remoteAddress The remote address to connect to.
     * @param HttpRequest $request The HTTP request object.
     * @param CurlClientConfig $curlClientConfig Configuration for the CURL client.
     * @param CurlHeaderSerializer $headerSerializer Serializer for HTTP headers.
     * @param IcyHeaderHandler $headerHandler Handler for ICY headers.
     * @param IcyMetadataHandler $metadataHandler Handler for ICY metadata.
     *
     * @return void
     */
    public function __construct(
        StreamUri $remoteAddress,
        HttpRequest $request,
        CurlClientConfig $curlClientConfig,
        CurlHeaderSerializer $headerSerializer,
        IcyHeaderHandler $headerHandler,
        IcyMetadataHandler $metadataHandler
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->request = $request;
        $this->curlClientConfig = $curlClientConfig;
        $this->headerSerializer = $headerSerializer;
        $this->headerHandler = $headerHandler;
        $this->metadataHandler = $metadataHandler;
    }

    /**
     * Initiates a cURL session to stream data from the specified remote address.
     *
     * @return void
     *
     * @throws CurlHttpException If there is a cURL error, HTTP error, or if the session fails to initialize.
     */
    public function getStream(): void
    {
        // Initialize the cURL session.
        $ch = curl_init();

        if ($ch === false) {
            throw new CurlHttpException('Failed to initialize cURL');
        }

        $manuallyInterrupted = false;

        // Set the parameters for the session.
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->remoteAddress->toString(),
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_SSL_VERIFYPEER => $this->curlClientConfig->verifyPeer,
            CURLOPT_SSL_VERIFYHOST => $this->curlClientConfig->verifyHost,
            CURLOPT_TIMEOUT => $this->curlClientConfig->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->curlClientConfig->connectTimeout,
            CURLOPT_HTTPHEADER => $this->headerSerializer->serialize(
                $this->request->headers()
            ),
            CURLOPT_FOLLOWLOCATION => $this->curlClientConfig->followLocation,
            CURLOPT_MAXREDIRS => $this->curlClientConfig->maxRedirects,
            CURLOPT_HEADERFUNCTION => function ($ch, string $header): int {
                $this->headerHandler->handle($header);

                return strlen($header);
            },
            CURLOPT_WRITEFUNCTION => function (
                $ch,
                string $chunk
            ) use (
                &$manuallyInterrupted
            ): int {
                $continueStreaming = $this->metadataHandler->handle($chunk);

                if ($continueStreaming === false) {
                    $manuallyInterrupted = true;

                    // Interrupt receiving data (with an error "curl_errno: 23").
                    return -1;
                }

                // Return the number of received data bytes.
                return strlen($chunk);
            },
        ]);

        // Execute the request.
        curl_exec($ch);

        // If there are errors, we save them into variables.
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // End the session.
        curl_close($ch);

        // If we intentionally interrupted the transfer → not an error
        if ($manuallyInterrupted && $errno === CURLE_WRITE_ERROR) {
            return;
        }

        if ($errno !== 0) {
            throw new CurlHttpException(
                sprintf('cURL error (%d): %s', $errno, $error),
                $errno
            );
        }

        if ($httpCode >= 400) {
            throw new CurlHttpException(
                sprintf('HTTP error: %d', $httpCode),
                $httpCode
            );
        }
    }
}
