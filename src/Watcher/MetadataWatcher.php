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

namespace Mp3StreamTitle\Watcher;

use Generator;
use InvalidArgumentException;
use Mp3StreamTitle\Mp3StreamTitle;

final class MetadataWatcher
{
    /**
     * Constructor method for initializing the class with necessary dependencies and configuration.
     *
     * @param Mp3StreamTitle $client Instance of the Mp3StreamTitle client.
     * @param int $interval Time interval in seconds, must be greater than zero.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the provided interval is less than 1.
     */
    public function __construct(
        private readonly Mp3StreamTitle $client,
        private readonly int $interval
    ) {
        if ($interval < 1) {
            throw new InvalidArgumentException(
                'Interval must be greater than zero.'
            );
        }
    }

    /**
     * Watches a given URL for changes in the stream title and yields new titles as they are detected.
     *
     * @param string $url The URL of the stream to monitor for title changes.
     *
     * @return Generator<int, string> Yields the new stream title whenever it changes.
     */
    public function watch(string $url): Generator
    {
        $lastTitle = null; // ?string

        while (true) {
            $title = $this->client->fetchStreamTitle($url);

            if ($title !== $lastTitle) {
                $lastTitle = $title;

                yield $title;
            }

            sleep($this->interval);
        }
    }
}