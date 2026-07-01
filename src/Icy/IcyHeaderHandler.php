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

use Mp3StreamTitle\Config\Mp3StreamTitleConfig;
use Mp3StreamTitle\Http\Response\HttpHeaderBuffer;
use Mp3StreamTitle\Metadata\RequiredLengthCalculator;

final class IcyHeaderHandler
{
    /**
     * Constructor method for initializing the class with required dependencies.
     *
     * @param HttpHeaderBuffer $httpHeaderBuffer An instance of HttpHeaderBuffer managing HTTP headers.
     * @param MetaIntResolver $metaIntResolver An instance of MetaIntResolver for resolving metadata intervals.
     * @param IcyMetadataBuffer $buffer An instance of IcyMetadataBuffer for handling metadata.
     * @param RequiredLengthCalculator $calculator An instance of RequiredLengthCalculator
     *                                             for calculating required lengths.
     * @param Mp3StreamTitleConfig $config An instance of Mp3StreamTitleConfig holding configuration
     *                                     for MP3 stream titles.
     *
     * @return void
     */
    public function __construct(
        private readonly HttpHeaderBuffer $httpHeaderBuffer,
        private readonly MetaIntResolver $metaIntResolver,
        private readonly IcyMetadataBuffer $buffer,
        private readonly RequiredLengthCalculator $calculator,
        private readonly Mp3StreamTitleConfig $config,
    ) {
    }

    /**
     * Processes the provided HTTP header and updates the metadata buffer with the required length.
     *
     * @param string $header The HTTP header string to be processed.
     *
     * @return void
     */
    public function handle(string $header): void
    {
        if (!$this->httpHeaderBuffer->append($header)) {
            return;
        }

        $metaInt = $this->metaIntResolver->resolve();

        $requiredLength = $this->calculator->calculate(
            $metaInt,
            $this->config->metaMaxLength
        );

        $this->buffer->setRequiredLength($requiredLength);
    }
}
