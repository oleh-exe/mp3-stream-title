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

use Mp3StreamTitle\Application\Config\Mp3StreamTitleConfig;
use Mp3StreamTitle\Exception\Http\CurlHttpException;

readonly class CurlClient
{
    /**
     * @var StreamUri
     */
    private StreamUri $remoteAddress;

    /**
     * @var CurlClientConfig
     */
    private CurlClientConfig $curlClientConfig;

    /**
     * @var Mp3StreamTitleConfig
     */
    private Mp3StreamTitleConfig $config;

    /**
     * @var IcyHeaderHandler
     */
    private IcyHeaderHandler $headerHandler;

    /**
     * @var IcyMetadataHandler
     */
    private IcyMetadataHandler $metadataHandler;

    /**
     * @param StreamUri $remoteAddress
     * @param CurlClientConfig $curlClientConfig
     * @param Mp3StreamTitleConfig $config
     * @param IcyHeaderHandler $headerHandler
     * @param IcyMetadataHandler $metadataHandler
     */
    public function __construct(
        StreamUri $remoteAddress,
        CurlClientConfig $curlClientConfig,
        Mp3StreamTitleConfig $config,
        IcyHeaderHandler $headerHandler,
        IcyMetadataHandler $metadataHandler
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->curlClientConfig = $curlClientConfig;
        $this->config = $config;
        $this->headerHandler = $headerHandler;
        $this->metadataHandler = $metadataHandler;
    }

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
            CURLOPT_HTTPHEADER => $this->curlClientConfig->headers,
            CURLOPT_FOLLOWLOCATION => $this->curlClientConfig->followLocation,
            CURLOPT_MAXREDIRS => $this->curlClientConfig->maxRedirects,
            CURLOPT_USERAGENT => $this->config->userAgent,
            CURLOPT_HEADERFUNCTION => function ($ch, string $header): int {
                $this->headerHandler->handle($header);

                return strlen($header);
            },
            CURLOPT_WRITEFUNCTION => function ($ch, string $chunk) use (
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