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

namespace App\Http;

use App\Services\RequestLoggerService;

final class JsonResponse
{
    public static function send(mixed $data, int $statusCode = 200, array $headers = []): never
    {
        RequestLoggerService::log($statusCode);

        http_response_code($statusCode);

        if ($statusCode !== 204) {
            header('Content-Type: application/json; charset=utf-8');
        } else {
            header_remove('Content-Type');
        }

        foreach ($headers as $name => $value) {
            header("{$name}: {$value}");
        }

        if ($statusCode !== 204) {
            echo json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );
        }

        exit;
    }

    public static function error(
        string $message,
        int $statusCode = 400,
        string $code = 'BAD_REQUEST',
        array $details = [],
        array $headers = []
    ): never {
        $payload = [
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        if (!empty($details)) {
            $payload['error']['details'] = $details;
        }

        self::send($payload, $statusCode, $headers);
    }
}
