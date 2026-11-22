<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\GreetingService;
use PHPUnit\Framework\TestCase;

final class GreetingServiceTest extends TestCase
{
    /** Checks the greeting and its transformations for a supported language. */
    public function testReturnsGreetingForSupportedLanguage(): void
    {
        $service = new GreetingService();

        $result = $service->getByLanguageCode('en');

        self::assertSame([
            'code' => 'en',
            'language' => 'English',
            'greeting' => [
                'original' => 'Hello world',
                'lowercase' => 'hello world',
                'titleCase' => 'Hello World',
                'uppercase' => 'HELLO WORLD',
            ],
        ], $result);
    }

    /** Checks that an unsupported language code returns null. */
    public function testReturnsNullForUnsupportedLanguage(): void
    {
        $service = new GreetingService();

        self::assertNull($service->getByLanguageCode('xx'));
    }

    /** Checks that the language code is normalized to lowercase. */
    public function testNormalizesUppercaseLanguageCode(): void
    {
        $service = new GreetingService();

        $result = $service->getByLanguageCode('EN');

        self::assertNotNull($result);
        self::assertSame('en', $result['code']);
    }

    /** Checks that leading and trailing whitespace is removed from the code. */
    public function testTrimsWhitespaceAroundLanguageCode(): void
    {
        $service = new GreetingService();

        $result = $service->getByLanguageCode(' EN ');

        self::assertNotNull($result);
        self::assertSame('en', $result['code']);
    }

    /** Checks the complete list of supported languages and their order. */
    public function testReturnsSupportedLanguages(): void
    {
        $service = new GreetingService();

        self::assertSame([
            ['code' => 'de', 'language' => 'Deutsch'],
            ['code' => 'en', 'language' => 'English'],
            ['code' => 'es', 'language' => 'Español'],
            ['code' => 'fr', 'language' => 'Français'],
            ['code' => 'it', 'language' => 'Italiano'],
            ['code' => 'pt', 'language' => 'Português'],
        ], $service->getSupportedLanguages());
    }

    /** Checks that transformations preserve accented characters correctly. */
    public function testTransformsAccentedGreetingCorrectly(): void
    {
        $service = new GreetingService();

        $result = $service->getByLanguageCode('pt');

        self::assertNotNull($result);
        self::assertSame([
            'original' => 'Olá mundo',
            'lowercase' => 'olá mundo',
            'titleCase' => 'Olá Mundo',
            'uppercase' => 'OLÁ MUNDO',
        ], $result['greeting']);
    }
}