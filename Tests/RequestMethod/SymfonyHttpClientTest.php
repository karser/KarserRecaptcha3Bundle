<?php

declare(strict_types=1);

namespace RequestMethod;

use Karser\Recaptcha3Bundle\RequestMethod\SymfonyHttpClient;
use PHPUnit\Framework\TestCase;
use ReCaptcha\RequestParameters;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class SymfonyHttpClientTest extends TestCase
{
    public function testSubmit(): void
    {
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) {
            self::assertSame('POST', $method);
            self::assertSame('https://www.google.com/recaptcha/api/siteverify', $url);
            self::assertSame('secret=secret&response=response', $options['body']);

            return new MockResponse('RESPONSEBODY');
        });

        $method = new SymfonyHttpClient($httpClient);
        $response = $method->submit(new RequestParameters('secret', 'response'));

        self::assertSame('RESPONSEBODY', $response);
    }

    public function testOverrideSiteVerifyUrl()
    {
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) {
            self::assertSame('POST', $method);
            self::assertSame('http://override/', $url);
            self::assertSame('secret=secret&response=response', $options['body']);

            return new MockResponse('RESPONSEBODY');
        });

        $method = new SymfonyHttpClient($httpClient, 'http://override/');
        $response = $method->submit(new RequestParameters('secret', 'response'));

        self::assertSame('RESPONSEBODY', $response);
    }

    public function testResponseError()
    {
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) {
            self::assertSame('POST', $method);
            self::assertSame('https://www.google.com/recaptcha/api/siteverify', $url);
            self::assertSame('secret=secret&response=response', $options['body']);

            return new MockResponse('fail', ['http_code' => 400]);
        });

        $method = new SymfonyHttpClient($httpClient);
        $response = $method->submit(new RequestParameters('secret', 'response'));

        self::assertSame('{"success": false, "error-codes": ["bad-response"]}', $response);
    }
}
