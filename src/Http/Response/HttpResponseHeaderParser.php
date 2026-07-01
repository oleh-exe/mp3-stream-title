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

namespace Mp3StreamTitle\Http\Response;

use Mp3StreamTitle\Http\Request\HeaderCollection;
use RuntimeException;

final readonly class HttpResponseHeaderParser
{
    /**
     * Parses the HTTP response header into a structured HttpResponse object.
     *
     * @param array $httpResponseHeader The raw HTTP response header lines.
     *
     * @return HttpResponse A structured representation of the HTTP response, including protocol version, status code,
     * reason, headers, and an empty body.
     */
    public function parse(array $httpResponseHeader): HttpResponse
    {
        [$status, $headers] = $this->parseHeaderLines($httpResponseHeader);
        $headers = new HeaderCollection($headers);

        return new HttpResponse(
            protocolVersion: $status['version'],
            statusCode: $status['code'],
            reason: $status['reason'],
            headers: $headers,
            body: '',
        );
    }

    /**
     * Parses raw HTTP header lines into a structured status array and headers array.
     *
     * @param array $lines The raw HTTP header lines to be parsed.
     *
     * @return array An associative array containing two elements:
     *               - The first element is an array with status information
     *                 (protocol version, status code, reason phrase).
     *               - The second element is an associative array of normalized header names
     *                 and their corresponding values.
     *
     * @throws RuntimeException If the HTTP status line is not found in the provided header lines.
     */
    private function parseHeaderLines(array $lines): array
    {
        $status = null;
        $headers = array();

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            // 1. Status line (find once)
            if (
                ($status === null)
                && str_starts_with($line, 'HTTP/')
            ) {
                $status = $this->parseStatusLine($line);
                continue;
            }

            // 2. Header lines
            if (!str_contains($line, ':')) {
                // tolerant mode
                continue;
            }

            [$name, $value] = explode(':', $line, 2);

            $name = trim($name);
            $value = trim($value);

            // --- light filtering (NOT strict validation) ---
            if ($name === '') {
                continue;
            }

            $normalizedName = $this->normalizeHeaderName($name);

            // Option: the last value wins
            $headers[$normalizedName] = $value;
        }

        if ($status === null) {
            throw new RuntimeException('Status line not found');
        }

        return [$status, $headers];
    }

    /**
     * Parses the HTTP status line and extracts the protocol version, status code, and reason phrase.
     *
     * @param string $statusLine The HTTP status line to parse.
     *
     * @return array An associative array containing:
     *               - 'version' (string): The protocol version (e.g., "1.1").
     *               - 'code' (int): The HTTP status code (e.g., 200).
     *               - 'reason' (string): The reason phrase (e.g., "OK").
     *
     * @throws RuntimeException If the status line is not in a valid format.
     */
    private function parseStatusLine(string $statusLine): array
    {
        $statusLine = preg_replace('/\s+/', ' ', $statusLine) ?? $statusLine;

        // HTTP protocol version <= 1.1
        if (
            !preg_match(
                '#^HTTP/(\d\.\d)\s+(\d{3})(?:\s+(.*))?$#',
                $statusLine,
                $matches,
                PREG_UNMATCHED_AS_NULL
            )
        ) {
            throw new RuntimeException(
                'Could not parse the status line in the HTTP response'
            );
        }

        return [
            'version' => $matches[1], // 1.1
            'code' => (int) $matches[2], // 200
            'reason' => $matches[3] ?? '', // OK
        ];
    }

    /**
     * Normalizes an HTTP header name by converting it to a consistent format.
     *
     * @param string $name The header name to normalize.
     *
     * @return string The normalized header name, where each segment is capitalized,
     *                and segments are separated by hyphens.
     */
    private function normalizeHeaderName(string $name): string
    {
        $splitName = array_map(
            static fn(string $part): string => ucfirst(strtolower($part)),
            explode('-', $name)
        );

        return implode('-', $splitName);
    }
}
