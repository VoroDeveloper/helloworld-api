<?php

/**
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

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be executed from the command line.' . PHP_EOL);
}

$period = $argv[1] ?? date('Ym');

if (preg_match('/^\d{6}$/', $period) !== 1) {
    fwrite(STDERR, "Invalid period format. Use YYYYMM (e.g., php stats.php 202611)" . PHP_EOL);
    exit(1);
}

$logFile = __DIR__ . "/logs/{$period}.jsonl";

if (!is_file($logFile) || !is_readable($logFile)) {
    echo "No log file found for period {$period} ({$logFile})." . PHP_EOL;
    exit(0);
}

$handle = fopen($logFile, 'r');
if ($handle === false) {
    fwrite(STDERR, "Unable to open log file: {$logFile}" . PHP_EOL);
    exit(1);
}

$totalRequests = 0;
$uniqueIps = [];
$statusCounts = [];
$endpointCounts = [];

while (($line = fgets($handle)) !== false) {
    $line = trim($line);
    if ($line === '') {
        continue;
    }

    $entry = json_decode($line, true);
    if (!is_array($entry)) {
        continue;
    }

    $totalRequests++;

    $ip = (string) ($entry['ip'] ?? '0.0.0.0');
    $uniqueIps[$ip] = true;

    $status = (string) ($entry['status'] ?? '0');
    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;

    $method = (string) ($entry['method'] ?? 'GET');
    $uri = (string) ($entry['uri'] ?? '/');
    $endpoint = "{$method} {$uri}";
    $endpointCounts[$endpoint] = ($endpointCounts[$endpoint] ?? 0) + 1;
}

fclose($handle);

arsort($endpointCounts);
ksort($statusCounts);

echo "===================================" . PHP_EOL;
echo " API USAGE REPORT - PERIOD: {$period}" . PHP_EOL;
echo "===================================" . PHP_EOL;
echo " Total Requests : {$totalRequests}" . PHP_EOL;
echo " Unique IPs     : " . count($uniqueIps) . PHP_EOL;
echo "-----------------------------------" . PHP_EOL;
echo " HTTP Status Codes:" . PHP_EOL;

foreach ($statusCounts as $status => $count) {
    printf("   [%s] -> %d requests\n", $status, $count);
}

echo "----------------------------------" . PHP_EOL;
echo " Top 10 Endpoints:" . PHP_EOL;

$topEndpoints = array_slice($endpointCounts, 0, 10, true);
foreach ($topEndpoints as $endpoint => $count) {
    printf("   %4d | %s\n", $count, $endpoint);
}

echo "==================================" . PHP_EOL;
