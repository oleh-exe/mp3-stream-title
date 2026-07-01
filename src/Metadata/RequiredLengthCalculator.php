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

namespace Mp3StreamTitle\Metadata;

final class RequiredLengthCalculator
{
    /**
     * Performs a calculation by incrementing the first parameter and adding it to the second parameter.
     *
     * @param int $metaInt The base integer to be incremented and used in the calculation.
     * @param int $metaMaxLength The additional integer to be added to the incremented value.
     *
     * @return int The result of the calculation.
     */
    public function calculate(
        int $metaInt,
        int $metaMaxLength
    ): int {
        return $metaInt + 1 + $metaMaxLength;
    }
}
