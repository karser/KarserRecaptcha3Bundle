<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Tests\fixtures;

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MockHttpClientCallback
{
    public function __invoke(string $method, string $url, array $options = []): ResponseInterface
    {
        return new MockResponse('{"success": true, "score": 1}');
    }
}
