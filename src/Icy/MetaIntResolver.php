<?php

/**
 * Copyright 2026 Oleh Kovalenko
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Mp3StreamTitle\Icy;

use Mp3StreamTitle\Http\Response\HttpHeaderBuffer;
use Mp3StreamTitle\Http\Response\HttpResponseParser;

final class MetaIntResolver
{
    /**
     * @var int|null $metaInt
     */
    private ?int $metaInt = null;

    /**
     * Constructor method.
     *
     * @param HttpHeaderBuffer $httpHeaderBuffer An instance of HttpHeaderBuffer.
     * @param HttpResponseParser $httpResponseParser An instance of HttpResponseParser.
     * @param IcyMetaIntParser $icyMetaIntParser An instance of IcyMetaIntParser.
     *
     * @return void
     */
    public function __construct(
        private readonly HttpHeaderBuffer $httpHeaderBuffer,
        private readonly HttpResponseParser $httpResponseParser,
        private readonly IcyMetaIntParser $icyMetaIntParser
    ) {
    }

    /**
     * Resolves and retrieves the metadata interval.
     *
     * This method calculates the metadata interval if it has not been already resolved
     * and returns the resolved value. The resolution process involves parsing the
     * HTTP response buffer and extracting the interval using the IcyMetaIntParser.
     *
     * @return int The resolved metadata interval.
     */
    public function resolve(): int
    {
        if ($this->metaInt !== null) {
            return $this->metaInt;
        }

        $buffer = $this->httpHeaderBuffer->buffer();
        $httpResponse = $this->httpResponseParser->parse($buffer);
        $this->metaInt = $this->icyMetaIntParser->getMetaInt(
            $httpResponse
        );

        return $this->metaInt;
    }
}
