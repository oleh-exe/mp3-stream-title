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
    private ?int $metaInt = null;

    public function __construct(
        private readonly HttpHeaderBuffer $httpHeaderBuffer,
        private readonly HttpResponseParser $httpResponseParser,
        private readonly IcyMetaIntParser $icyMetaIntParser
    ) {
    }

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