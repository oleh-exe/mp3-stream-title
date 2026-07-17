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

namespace Mp3StreamTitle\Exception;

use RuntimeException;

/**
 * Represents an exception thrown when a cURL-related error occurs during an HTTP request.
 *
 * This exception extends the RuntimeException and provides a way to handle
 * errors specifically related to cURL operations in an HTTP context.
 */
final class CurlHttpException extends RuntimeException
{
}
