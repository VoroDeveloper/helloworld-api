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

final class Router
{
    /** @var array<string, array<string, array{class-string, string}>> */
    private array $routes = [];

    /**
     * @param array{class-string, string} $handler
     */
    public function get(string $pattern, array $handler): void
    {
        $this->addRoute('GET', $pattern, $handler);
    }

    /**
     * @param array{class-string, string} $handler
     */
    public function addRoute(string $method, string $pattern, array $handler): void
    {
        $this->routes[$pattern][strtoupper($method)] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = basename($scriptName) === 'index.php' ? dirname($scriptName) : '';
        $baseDir = preg_replace('#/public$#', '', $scriptDir) ?? '';

        if ($baseDir !== '' && $baseDir !== '/' && $baseDir !== '\\') {
            $baseDir = rtrim($baseDir, '/');

            if ($path === $baseDir) {
                $path = '/';
            } elseif (str_starts_with($path, $baseDir . '/')) {
                $path = substr($path, strlen($baseDir));
            }
        }

        $path = rtrim($path, '/') ?: '/';

        foreach ($this->routes as $pattern => $methods) {
            $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $pattern);
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $path, $matches) === 1) {
                // The route exists, but the HTTP method is not allowed
                if (!isset($methods[$method])) {
                    $allowedMethods = implode(', ', array_keys($methods));

                    JsonResponse::error(
                        "The {$method} method is not allowed for this endpoint.",
                        405,
                        'METHOD_NOT_ALLOWED',
                        ['allowedMethods' => array_keys($methods)],
                        ['Allow' => $allowedMethods]
                    );
                }

                // Valid route and method
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                [$controllerClass, $action] = $methods[$method];
                $controller = new $controllerClass();
                $controller->$action($params);
                return;
            }
        }

        // No route matched the requested URL
        JsonResponse::error('The requested endpoint was not found.', 404, 'ENDPOINT_NOT_FOUND');
    }
}
