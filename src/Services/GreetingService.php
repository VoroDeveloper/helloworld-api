<?php

/*
 * Copyright 2026 VoroDeveloper
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://apache.org
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace App\Services;

final class GreetingService
{
    /** @var array<string, array{language: string, greeting: string}> */
    private array $languages;

    public function __construct()
    {
        $this->languages = require __DIR__ . '/../Config/languages.php';
    }

    /**
     * @return array{
     *     code: string,
     *     language: string,
     *     greeting: array{
     *         original: string,
     *         lowercase: string,
     *         titleCase: string,
     *         uppercase: string
     *     }
     * }|null
     */
    public function getByLanguageCode(string $languageCode): ?array
    {
        $normalizedCode = strtolower(trim($languageCode));

        if (!isset($this->languages[$normalizedCode])) {
            return null;
        }

        $baseGreeting = $this->languages[$normalizedCode]['greeting'];

        return [
            'code' => $normalizedCode,
            'language' => $this->languages[$normalizedCode]['language'],
            'greeting' => [
                'original' => $baseGreeting,
                'lowercase' => mb_strtolower($baseGreeting, 'UTF-8'),
                'titleCase' => mb_convert_case($baseGreeting, MB_CASE_TITLE, 'UTF-8'),
                'uppercase' => mb_strtoupper($baseGreeting, 'UTF-8'),
            ],
        ];
    }

    /**
     * @return list<array{code: string, language: string}>
     */
    public function getSupportedLanguages(): array
    {
        $supported = [];

        foreach ($this->languages as $code => $info) {
            $supported[] = [
                'code' => $code,
                'language' => $info['language'],
            ];
        }

        return $supported;
    }
}
