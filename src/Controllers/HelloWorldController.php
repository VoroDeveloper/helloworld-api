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

namespace App\Controllers;

use App\Http\JsonResponse;
use App\Services\GreetingService;

final class HelloWorldController
{
    private GreetingService $greetingService;

    public function __construct()
    {
        $this->greetingService = new GreetingService();
    }

    public function indexGet(): void
    {
        JsonResponse::send([
            'service' => 'Hello World API',
            'endpoints' => [
                'apiInfo' => '/',
                'listLanguages' => '/languages',
                'getGreeting' => '/{languageCode}',
            ],
            'supportedLanguages' => $this->greetingService->getSupportedLanguages(),
        ], 200);
    }

    public function languagesGet(): void
    {
        $languages = $this->greetingService->getSupportedLanguages();

        JsonResponse::send([
            'total' => count($languages),
            'languages' => $languages,
        ], 200);
    }

    /** @param array{languageCode: string} $params */
    public function helloWorldGet(array $params): void
    {
        $languageCode = $params['languageCode'];
        $greetings = $this->greetingService->getByLanguageCode($languageCode);

        if ($greetings === null) {
            JsonResponse::error(
                "Language code '{$languageCode}' is not supported.",
                404,
                'UNSUPPORTED_LANGUAGE',
                ['supportedLanguages' => $this->greetingService->getSupportedLanguages()]
            );
        }

        JsonResponse::send($greetings, 200);
    }
}
