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

namespace Mp3StreamTitle\Http\Enum;

/**
 * Represents the various states of a connection lifecycle.
 *
 * Each case corresponds to a specific stage or status that a connection can
 * occupy during its lifetime, such as being initialized, actively connecting,
 * established, or encountering an error.
 */
enum ConnectionState: string
{
    /**
     * Initial state of the connection.
     */
    case INITIAL = 'initial';

    /**
     * State representing that the connection is currently being established.
     */
    case CONNECTING = 'connecting';

    /**
     * State indicating that the connection has been successfully established.
     */
    case CONNECTED = 'connected';

    /**
     * Indicates that the system is in a reading state.
     */
    case READING = 'reading';

    /**
     * State indicating that the connection is in the process of writing.
     */
    case WRITING = 'writing';

    /**
     * Represents the closed state of the connection.
     */
    case CLOSED = 'closed';

    /**
     * Represents an error state of the connection.
     */
    case ERROR = 'error';
}
