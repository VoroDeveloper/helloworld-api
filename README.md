# Hello World API

Lightweight REST API built in **pure PHP 8.1+** (framework-free) that returns the **`Hello world`** greeting across multiple languages and letter-case formats in JSON.

For each language, it returns the greeting in the following formats:

| Format | Result |
| :--- | :--- |
| Original | `Hello world` |
| lowercase | `hello world` |
| Title Case | `Hello World` |
| UPPERCASE | `HELLO WORLD` |

#### Supported Languages

| Code | Language | Base Greeting |
| :--- | :--- | :--- |
| `de` | Deutsch | Hallo Welt |
| `en` | English | Hello world |
| `es` | Español | Hola mundo |
| `fr` | Français | Bonjour le monde |
| `it` | Italiano | Ciao mondo |
| `pt` | Português | Olá mundo |

Designed following modern HTTP standards: Front Controller architecture, PSR-4 autoloading, CORS headers, semantic HTTP status code handling (`200`, `204`, `404`, `405` with `Allow` header), and structured error responses.

[![Run in Postman](https://img.shields.io/badge/Run_in_Postman-VoroDeveloper_APIs-FF6C37?logo=postman)](https://www.postman.com/vorodev/vorodeveloper-apis)

---

🌐 **Live Production Demo:**

- [https://vorodev.com/api/helloworld](https://vorodev.com/api/helloworld)
- [https://vorodev.com/api/helloworld/languages](https://vorodev.com/api/helloworld/languages)
- [https://vorodev.com/api/helloworld/en](https://vorodev.com/api/helloworld/en)

---

## Requirements

- **PHP** 8.1 or higher
- **PHP Extension** `mbstring` (required for UTF-8 case transformations with accents)
- **Composer 2** (to generate the PSR-4 autoloader; the API has no third-party production dependencies)
- **PHPUnit 10.5** (development dependency, installed with Composer to run the test suite)
- **Apache** with `mod_rewrite` enabled (for production deployment)

---

## Installation & Local Execution

1. Clone the repository and generate the autoloader from the project root:

```bash
cd helloworld-api
composer install
```

2. Start PHP's built-in development server pointing to `public/`:

```bash
php -S 127.0.0.1:8000 -t public public/index.php
```

> **Note:** Passing `public/index.php` as the router script ensures the built-in server routes all requests that do not match existing static files directly to the Front Controller.

3. Test the API from another terminal:

```bash
curl -i http://127.0.0.1:8000/en
```

---

## Tests

The unit tests cover the greeting service: supported and unsupported languages, code normalization, the supported-language list, and text transformations with accented characters.

Install the development dependencies from the project root and run PHPUnit:

```bash
composer install
./vendor/bin/phpunit --configuration phpunit.xml
```

The test suite is run manually and does not require starting the HTTP server. PHPUnit is a development dependency and is not required to run the API in production.

---

## Production Deployment

From the project root, run the script to prepare a production copy in the sibling directory `helloworld-api-prod`:

```bash
./build-production.sh
```

The script requires Composer and PHPUnit installed in the project. It runs the test suite first and stops without creating the production copy if any test fails. If all tests pass, it prepares the runtime files: `public/` (the API and public documentation), `src/`, `stats.php`, `vendor/` generated with production dependencies, `composer.json`, and `composer.lock`. README files, tests, caches, local logs, IDE configuration, `.env` files, `LICENSE`, and `NOTICE` are not copied. If the destination already exists, the script stops without overwriting it.

Choose how to deploy the package:

1. **Upload `vendor/` too:** copy the generated package to the server. Composer does not need to be installed there.

2. **Generate `vendor/` on the server:** do not upload the `vendor/` directory, but upload `composer.json` and `composer.lock`. From the project directory on the server, run `composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist`.

In either case, preserve the directory structure expected by the deployment. The server must be able to create and write to `logs/` to record requests; existing logs are neither copied nor removed from the server.

---

## Endpoints

**URL Base:**

- **Local:** `http://127.0.0.1:8000`

- **Production:** [https://vorodev.com/api/helloworld](https://vorodev.com/api/helloworld)


### 1. General API Information

```http
GET /
```

**Example**

- **Local:** `http://127.0.0.1:8000`

- **Production:** `GET` [https://vorodev.com/api/helloworld](https://vorodev.com/api/helloworld)

Returns general service metadata, available endpoints, and the list of supported languages.


#### Example Request

```http
GET https://vorodev.com/api/helloworld
```

#### Response `200 OK`

```json
{
  "service": "Hello World API",
  "endpoints": {
    "apiInfo": "/",
    "listLanguages": "/languages",
    "getGreeting": "/{languageCode}"
  },
  "supportedLanguages": [
    { "code": "de", "language": "Deutsch" },
    { "code": "en", "language": "English" },
    { "code": "es", "language": "Español" },
    { "code": "fr", "language": "Français" },
    { "code": "it", "language": "Italiano" },
    { "code": "pt", "language": "Português" }
  ]
}
```

---

### 2. List Supported Languages

```http
GET /languages
```

**Example**

  - **Local:** `GET http://127.0.0.1:8000/languages`

  - **Production:** `GET` [https://vorodev.com/api/helloworld/languages](https://vorodev.com/api/helloworld/languages)

Returns the total count and full catalog of language codes and names supported by the API.


#### Example Request

```http
GET https://vorodev.com/api/helloworld/languages
```

#### Response `200 OK`

```json
{
  "total": 6,
  "languages": [
    { "code": "de", "language": "Deutsch" },
    { "code": "en", "language": "English" },
    { "code": "es", "language": "Español" },
    { "code": "fr", "language": "Français" },
    { "code": "it", "language": "Italiano" },
    { "code": "pt", "language": "Português" }
  ]
}
```

---

### 3. Get Greeting by Language

```http
GET /{languageCode}
```

**Example**

  - **Local:** `GET http://127.0.0.1:8000/en`

  - **Production** `GET` [https://vorodev.com/api/helloworld/en](https://vorodev.com/api/helloworld/en)

Return the greeting in the specified language.

`{languageCode}` is **case-insensitive**.


#### Example Request

```http
GET https://vorodev.com/api/helloworld/en
```

#### Response `200 OK`

```json
{
  "code": "en",
  "language": "English",
  "greeting": {
    "original": "Hello world",
    "lowercase": "hello world",
    "titleCase": "Hello World",
    "uppercase": "HELLO WORLD"
  }
}
```

---

## Interactive Documentation (Scalar / OpenAPI)

The API includes an **OpenAPI 3.1** specification and an interactive visual interface powered by **Scalar** (featuring a theme switcher and light/dark mode) to explore endpoints and run live tests from your browser:

- **Local**

  - **Interactive UI:** `http://127.0.0.1:8000/docs/index.html`

  - **OpenAPI Contract:** `http://127.0.0.1:8000/docs/openapi.yaml`


- **Production**

  - **Interactive UI:** [https://vorodev.com/api/helloworld/docs](https://vorodev.com/api/helloworld/docs)

  - **OpenAPI Contract:** [https://vorodev.com/api/helloworld/docs/openapi.yaml](https://vorodev.com/api/helloworld/docs/openapi.yaml)


In production, deploy `public/docs/index.html`, `public/docs/styles.css`, and `public/docs/openapi.yaml` inside the same public directory serving the API under `/api/helloworld`. The Scalar UI loads the contract via the relative path `./openapi.yaml`, so these files must remain together.

---

## Error Handling

All error responses return `Content-Type: application/json; charset=utf-8` with a consistent structure:

| HTTP Status | Error Code | Description |
| :--- | :--- | :--- |
| `404 Not Found` | `UNSUPPORTED_LANGUAGE` | The requested language code does not exist. Includes `supportedLanguages` in `details` |
| `404 Not Found` | `ENDPOINT_NOT_FOUND` | The requested route is not defined in the router |
| `405 Method Not Allowed` | `METHOD_NOT_ALLOWED` | An HTTP method other than `GET` was used. Includes the `Allow: GET` header |
| `500 Internal Server Error` | `INTERNAL_SERVER_ERROR` | Unexpected server error handled by the global exception handler |


#### Example Response `404 Not Found` (`UNSUPPORTED_LANGUAGE`)

```json
{
  "error": {
    "code": "UNSUPPORTED_LANGUAGE",
    "message": "Language code 'xx' is not supported.",
    "details": {
      "supportedLanguages": [
        { "code": "de", "language": "Deutsch" },
        { "code": "en", "language": "English" },
        { "code": "es", "language": "Español" },
        { "code": "fr", "language": "Français" },
        { "code": "it", "language": "Italiano" },
        { "code": "pt", "language": "Português" }
      ]
    }
  }
}
```

---

## CORS

The API allows requests from any origin (`Access-Control-Allow-Origin: *`) for `GET` and `OPTIONS` methods, permitting the `Content-Type` header. *Preflight* `OPTIONS` requests respond with `204 No Content`.

---

## Project Structure

```text
helloworld-api/
├── public/                # Only directory publicly exposed on the web
│   ├── docs/
│   │   ├── index.html     # Interactive visual UI (Scalar API Reference)
│   │   ├── styles.css     # Theme switcher styles for the documentation
│   │   └── openapi.yaml   # OpenAPI 3.1 specification
│   ├── .htaccess          # Apache rewrite rules, security headers, and UTF-8 encoding
│   ├── index.php          # Front controller and route registration
│   └── favicon.ico
├── src/
│   ├── Config/
│   │   └── languages.php  # Dictionary of language codes, names, and base greetings
│   ├── Controllers/
│   │   └── HelloWorldController.php
│   ├── Http/
│   │   ├── JsonResponse.php
│   │   └── Router.php
│   └── Services/
│       ├── GreetingService.php
│       └── RequestLoggerService.php
├── vendor/                # Autoloader generated by Composer (ignored in Git)
├── logs/                  # Monthly anonymized request logs YYYYMM.jsonl (ignored in Git)
├── .gitignore
├── composer.json
├── composer.lock
├── LICENSE                # Apache 2.0 License
├── NOTICE                 # Attribution notice file
├── README.md
├── stats.php              # CLI script to inspect monthly usage statistics
└── build-production.sh    # Script to prepare a production copy
```

---

## Request Logging & Usage Statistics

The API responses are automatically logged via `RequestLoggerService` into monthly JSON Lines files (`logs/YYYYMM.jsonl`) stored in the private root directory outside `public/`.

- **Privacy-First (GDPR Compliant):** Client IP addresses are irreversibly anonymized before writing to disk (masking the last byte for IPv4 and the last 10 bytes for IPv6).

- **Non-Blocking:** Logging errors are caught silently so they never interrupt API responses.


### Viewing Usage Reports (CLI)

You can generate a summary report of total requests, unique anonymized IPs, HTTP status code distribution, and top requested endpoints directly from the command line using `stats.php`:

```bash
# View report for the current month
php stats.php

# View report for a specific month (YYYYMM)
php stats.php 202611
```

---

## Links

- **Repository:** [https://github.com/vorodeveloper/helloworld-api](https://github.com/vorodeveloper/helloworld-api)

- **Live API:** [https://vorodev.com/api/helloworld](https://vorodev.com/api/helloworld)

- **Interactive Documentation:** [https://vorodev.com/api/helloworld/docs/](https://vorodev.com/api/helloworld/docs/)

- **Postman Collection:** [https://www.postman.com/vorodev/vorodeveloper-apis](https://www.postman.com/vorodev/vorodeveloper-apis)

---

## Changelog

All notable changes to this project are documented following the [Keep a Changelog](https://keepachangelog.com) format and [Semantic Versioning](https://semver.org).

### v1.0.0 - 2026-11-22

#### Added
- Initial release of the REST API in pure PHP 8.1+ with PSR-4 autoloading
- `GET /` endpoint returning general service metadata and available endpoints
- `GET /languages` endpoint returning the complete catalog of supported languages
- `GET /{languageCode}` endpoint supporting 6 languages (`de`, `en`, `es`, `fr`, `it`, `pt`) and returning a `greeting` object with typographic variants (`original`, `lowercase`, `titleCase`, `uppercase`)
- Standard HTTP error handling (`404` and `405` with `Allow: GET` header) and CORS support (`OPTIONS`)
- Interactive OpenAPI 3.1 documentation powered by **Scalar** at `/docs/` with theme switcher and light/dark mode
- PHPUnit 10.5 unit test suite for `GreetingService`, run manually
- `build-production.sh` script that runs the test suite and, if it passes, prepares a production copy with optimized dependencies and without development files


---

## Author

**VoroDeveloper**
*Full-Stack Developer*

[![GitHub](https://img.shields.io/badge/GitHub-vorodeveloper-373839?style=flat-square&logo=github&logoColor=white)](https://github.com/vorodeveloper)
[![Email](https://img.shields.io/badge/Email-contact@vorodeveloper.com-3A3B3C?style=flat-square&logo=gmail&logoColor=white)](mailto:contact@vorodeveloper.com)
[![Website](https://img.shields.io/badge/Website-vorodeveloper.com-3D3E3F?style=flat-square&logo=google-chrome&logoColor=white)](https://vorodeveloper.com)

---

## Contributing

Contributions, suggestions, and feedback are very welcome!

Whether you want to report a bug, propose a new feature, or help expand `Hello World API` with new languages and greetings, feel free to get involved:

- **Suggest a Language:** Open an [Issue](https://github.com/vorodeveloper/helloworld-api/issues) specifying the language code, native name, and greeting.

- **Submit a Pull Request:** *Fork* the repository, add the new language code, native name, and base greeting in `src/Config/languages.php`, and open a *Pull Request*.

- **Share Feedback:** If you use this API in one of your projects or have ideas to improve it, let us know via GitHub [Issues](https://github.com/vorodeveloper/helloworld-api/issues) or by email at [apidev@vorodeveloper.com](mailto:apidev@vorodeveloper.com).

---

## Attribution & Terms of Use

- **Source Code:** Licensed under the **Apache License 2.0**. If you redistribute or build upon this code, you must retain the original `copyright`, `license`, and `NOTICE` attribution files (`Powered by VoroDeveloper - https://vorodeveloper.com`).

- **Hosted Public API (`vorodev.com`):** Free to use for both personal and commercial projects. If you integrate our endpoints into a publicly accessible website or application, you must include a visible attribution to [VoroDeveloper](https://vorodeveloper.com) in your app's credits, footer, or "About" section (e.g., *"Powered by VoroDeveloper"*).

- **Privacy & Logging:** Basic, anonymized request metrics (including masked IP addresses, requested endpoints, and HTTP status codes) are logged solely for service monitoring, usage statistics, and abuse prevention.

---

## License

Apache-2.0 © 2026 [VoroDeveloper](https://github.com/VoroDeveloper)

This project is licensed under the **Apache License 2.0**. See the [LICENSE](LICENSE) and [NOTICE](NOTICE) files for details.

---

*Last updated: November 2026*
