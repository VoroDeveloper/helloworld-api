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

final class RequestLoggerService
{
    public static function log(int $statusCode): void
    {
        try {
            // Root directory of the private project (two levels up from src/Services)
            $logsDir = dirname(__DIR__, 2) . '/logs';

            if (!is_dir($logsDir)) {
                mkdir($logsDir, 0755, true);
            }

            $filePath = $logsDir . '/' . date('Ym') . '.jsonl';

            $entry = [
                'timestamp' => date('c'),
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
                'uri' => $_SERVER['REQUEST_URI'] ?? '/',
                'status' => $statusCode,
                'ip' => self::anonymizeIp(self::extractClientIp()),
                'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            ];

            $line = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

            file_put_contents($filePath, $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable) {
            // Never interrupt the API response if logging fails
        }
    }

    private static function extractClientIp(): string
    {
        $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if ($forwarded !== '') {
            $clientIp = trim(explode(',', $forwarded, 2)[0]);
            if (filter_var($clientIp, FILTER_VALIDATE_IP) !== false) {
                return $clientIp;
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private static function anonymizeIp(string $ip): string
    {
        // IPv4: mask last octet (e.g., 83.45.120.14 -> 83.45.120.0)
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $parts = explode('.', $ip);
            $parts[3] = '0';
            return implode('.', $parts);
        }

        // IPv6: mask last 80 bits
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            $packed = inet_pton($ip);
            if ($packed !== false) {
                $masked = substr($packed, 0, 6) . str_repeat("\0", 10);
                $result = inet_ntop($masked);
                if ($result !== false) {
                    return $result;
                }
            }
        }

        return '0.0.0.0';
    }
}
