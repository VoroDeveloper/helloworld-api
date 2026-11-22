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

use App\Controllers\HelloWorldController;
use App\Http\JsonResponse;
use App\Http\Router;


$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',                           // Local (helloworld-api/public/index.php)
    __DIR__ . '/../../../apis/helloworld-api/vendor/autoload.php', // Production (public_html/api/helloworld/index.php)
];

$autoloadLoaded = false;

foreach ($autoloadPaths as $file) {
    if (is_file($file) && is_readable($file)) {
        require_once $file;
        $autoloadLoaded = true;
        break;
    }
}

if (!$autoloadLoaded) {
    error_log('Critical error: Composer autoloader not found (run "composer install"). Checked paths: ' . implode(', ', $autoloadPaths));
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => [
            'code' => 'INTERNAL_SERVER_ERROR',
            'message' => 'The service is temporarily unavailable due to an internal error.',
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}


// Standard CORS headers for public APIs
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    JsonResponse::send(null, 204);
}

// Global handler for uncaught exceptions
set_exception_handler(function (Throwable $e): void {
    error_log($e->getMessage());
    JsonResponse::error('An unexpected internal server error occurred.', 500, 'INTERNAL_SERVER_ERROR');
});

// Route definitions
$router = new Router();

// 1. Static routes ALWAYS first
$router->get('/', [HelloWorldController::class, 'indexGet']);
$router->get('/languages', [HelloWorldController::class, 'languagesGet']);

// 2. Dynamic routes AFTER static routes
$router->get('/{languageCode}', [HelloWorldController::class, 'helloWorldGet']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
