<?php
/**
 * Copyright 2020-2026 Oleh Kovalenko
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

namespace Mp3StreamTitle;

use Mp3StreamTitle\Config\Mp3StreamTitleConfig;
use Mp3StreamTitle\Config\StreamTransport;
use Mp3StreamTitle\Http\Request\StreamContextFactory;
use Mp3StreamTitle\Http\Request\StreamRequestFactory;
use Mp3StreamTitle\Http\Response\HttpHeaderBuffer;
use Mp3StreamTitle\Http\Response\HttpResponseHeaderParser;
use Mp3StreamTitle\Http\Response\HttpResponseParser;
use Mp3StreamTitle\Http\Serializer\HttpHeadersSerializer;
use Mp3StreamTitle\Icy\IcyHeaderHandler;
use Mp3StreamTitle\Icy\IcyMetadataBuffer;
use Mp3StreamTitle\Icy\IcyMetadataExtractor;
use Mp3StreamTitle\Icy\IcyMetadataHandler;
use Mp3StreamTitle\Icy\IcyMetaIntParser;
use Mp3StreamTitle\Icy\MetaIntResolver;
use Mp3StreamTitle\Metadata\RequiredLengthCalculator;
use Mp3StreamTitle\Metadata\StreamTitleExtractor;
use Mp3StreamTitle\Transport\Curl\CurlClient;
use Mp3StreamTitle\Transport\Curl\CurlClientConfig;
use Mp3StreamTitle\Transport\Curl\CurlHeaderSerializer;
use Mp3StreamTitle\Transport\Socket\SocketConnection;
use Mp3StreamTitle\Transport\Socket\SocketConnectionConfig;
use Mp3StreamTitle\Transport\Socket\SocketHttpClient;
use Mp3StreamTitle\Transport\Socket\SocketStreamReader;
use Mp3StreamTitle\Transport\Stream\FopenStreamReader;
use Mp3StreamTitle\Transport\Stream\StreamConnection;
use Mp3StreamTitle\Transport\Stream\StreamConnectionConfig;
use Mp3StreamTitle\ValueObject\StreamEndpoint;
use Mp3StreamTitle\ValueObject\StreamUri;
use RuntimeException;
use Throwable;

final class Mp3StreamTitle
{
    /**
     * Configuration settings for the application.
     *
     * @var Mp3StreamTitleConfig|null
     */
    private ?Mp3StreamTitleConfig $config;

    /**
     * Constructor to initialize the Mp3StreamTitle class with a configuration object.
     * If no configuration object is provided, a default instance of Mp3StreamTitleConfig is created.
     *
     * @param Mp3StreamTitleConfig|null $config The configuration object for Mp3StreamTitle. Defaults to null.
     * @return void
     */
    public function __construct(?Mp3StreamTitleConfig $config = null)
    {
        $this->config = $config ?? new Mp3StreamTitleConfig();
    }

    /**
     * The function takes as an argument a direct link to the stream of
     * any online radio station and uses the function specified in the
     * settings to send requests to the stream-server.
     *
     * @param string $streamingUrl
     *
     * @return string
     *
     * @throws Throwable
     */
    public function fetchStreamTitle(string $streamingUrl): string
    {
        return match ($this->config->streamTransport) {
            StreamTransport::CURL => $this->fetchUsingCurl($streamingUrl),
            StreamTransport::SOCKET => $this->fetchUsingSocket($streamingUrl),
            StreamTransport::STREAM => $this->fetchUsingStream($streamingUrl),
        };
    }

    /**
     * The fetchUsingCurl-function takes as an argument a direct link to the stream
     * of the online radio station and sends a cURL request to the stream
     * server. As a result, the function returns information about the song
     * in the following format "artist name and song name".
     *
     * @param string $streamingUrl A direct URL to the online radio stream.
     *
     * @return string Metadata containing song information.
     *
     * @throws RuntimeException If cURL is unavailable or metadata cannot be retrieved.
     */
    private function fetchUsingCurl(string $streamingUrl): string
    {
        // Checking if we can use cURL.
        if (!extension_loaded('curl') || !function_exists('curl_init')) {
            throw new RuntimeException(
                'The ext-curl extension is required to use Mp3StreamTitle'
            );
        }

        $endpoint = StreamEndpoint::fromString($streamingUrl);

        $remoteAddress = new StreamUri(
            $endpoint
        );
        $streamRequestFactory = new StreamRequestFactory();

        $httpRequest = $streamRequestFactory->create(
            $endpoint,
            $this->config
        );

        $curlHeaderSerializer = new CurlHeaderSerializer();
        $httpHeaderBuffer = new HttpHeaderBuffer();
        $httpResponseParser = new HttpResponseParser();
        $icyMetaIntParser = new IcyMetaIntParser();
        $metaIntResolver = new MetaIntResolver(
            $httpHeaderBuffer,
            $httpResponseParser,
            $icyMetaIntParser
        );
        $icyMetadataBuffer = new IcyMetadataBuffer();
        $requiredLengthCalculator = new RequiredLengthCalculator();
        $headerHandler = new IcyHeaderHandler(
            $httpHeaderBuffer,
            $metaIntResolver,
            $icyMetadataBuffer,
            $requiredLengthCalculator,
            $this->config
        );
        $metadataHandler = new IcyMetadataHandler(
            $icyMetadataBuffer
        );
        $curlClient = new CurlClient(
            $remoteAddress,
            $httpRequest,
            new CurlClientConfig(),
            $curlHeaderSerializer,
            $headerHandler,
            $metadataHandler
        );

        $curlClient->getStream();

        $icyMetadataExtractor = new IcyMetadataExtractor();

        $metaInt = $metaIntResolver->resolve();
        $metadata = $icyMetadataExtractor->extract($icyMetadataBuffer->buffer(), $metaInt);

        $streamTitleExtractor = new StreamTitleExtractor();

        return $streamTitleExtractor->extract($metadata);
    }

    /**
     * The fetchUsingStream-function takes as an argument a direct link to an online
     * radio station stream and opens the stream using the set HTTP headers.
     * As a result, the function returns information about the song
     * in the following format "artist name and song name".
     *
     * @param string $streamingUrl
     *
     * @return string
     *
     * @throws Throwable
     */
    private function fetchUsingStream(string $streamingUrl): string
    {
        $endpoint = StreamEndpoint::fromString($streamingUrl);

        $streamRequestFactory = new StreamRequestFactory();
        $headersSerializer = new HttpHeadersSerializer();
        $remoteAddress = new StreamUri(
            $endpoint
        );

        $httpRequest = $streamRequestFactory->create(
            $endpoint,
            $this->config
        );

        $streamConnectionConfig = new StreamConnectionConfig();
        $streamContext = new StreamContextFactory(
            $httpRequest,
            $headersSerializer,
            $streamConnectionConfig
        );
        $stream = new StreamConnection(
            $remoteAddress,
            $streamContext,
            $streamConnectionConfig
        );
        $headerParser = new HttpResponseHeaderParser();
        $icyMetaIntParser = new IcyMetaIntParser();
        $streamReader = new FopenStreamReader();

        try {
            $stream->open();

            $httpResponse = $headerParser->parse($stream->headers());
            $initialBuffer = $httpResponse->body;
            // Find out from which byte the metadata will begin
            $offset = $icyMetaIntParser->getMetaInt($httpResponse);
            $targetLength = $offset + 1 + $this->config->metaMaxLength;
            $safetyMargin = $streamConnectionConfig->readChunkSize;
            $maxAllowed = $targetLength + $safetyMargin;
            $bodyBuffer = $streamReader->read($stream, $initialBuffer, $targetLength, $maxAllowed);
        } finally {
            $stream->close();
        }

        $icyMetadataExtractor = new IcyMetadataExtractor();
        $metadata = $icyMetadataExtractor->extract($bodyBuffer, $offset);

        $streamTitleExtractor = new StreamTitleExtractor();

        return $streamTitleExtractor->extract($metadata);
    }

    /**
     * The fetchUsingSocket-function takes as an argument a direct link to the stream
     * of the online radio station and sends an HTTP request to the stream
     * server. As a result, the function returns information about the song
     * in the following format "artist name and song name".
     *
     * @param string $streamingUrl
     *
     * @return string
     *
     * @throws RuntimeException|Throwable
     */
    private function fetchUsingSocket(string $streamingUrl): string
    {
        $endpoint = StreamEndpoint::fromString($streamingUrl);

        $socketConfig = new SocketConnectionConfig();
        $socket = new SocketConnection(
            $endpoint,
            $socketConfig,
        );
        $streamRequestFactory = new StreamRequestFactory();
        $httpClient = new SocketHttpClient($socket, $socketConfig);

        $httpRequest = $streamRequestFactory->create(
            $endpoint,
            $this->config
        );

        $icyMetaIntParser = new IcyMetaIntParser();
        $streamReader = new SocketStreamReader();

        try {
            $socket->open();

            $httpResponse = $httpClient->send($httpRequest);
            $initialBuffer = $httpResponse->body;
            // Find out from which byte the metadata will begin
            $offset = $icyMetaIntParser->getMetaInt($httpResponse);
            $targetLength = $offset + 1 + $this->config->metaMaxLength;
            $safetyMargin = $socketConfig->readChunkSize;
            $maxAllowed = $targetLength + $safetyMargin;
            $bodyBuffer = $streamReader->read($socket, $initialBuffer, $targetLength, $maxAllowed);
        } finally {
            $socket->close();
        }

        $icyMetadataExtractor = new IcyMetadataExtractor();
        $metadata = $icyMetadataExtractor->extract($bodyBuffer, $offset);

        $streamTitleExtractor = new StreamTitleExtractor();

        return $streamTitleExtractor->extract($metadata);
    }
}
