<?php

declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\RequestMethod;

use ReCaptcha\ReCaptcha;
use ReCaptcha\RequestMethod;
use ReCaptcha\RequestParameters;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SymfonyHttpClient implements RequestMethod
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $siteVerifyUrl;

    public function __construct(HttpClientInterface $httpClient, ?string $siteVerifyUrl = null)
    {
        $this->httpClient = $httpClient;
        $this->siteVerifyUrl = $siteVerifyUrl ?? ReCaptcha::SITE_VERIFY_URL;
    }

    public function submit(RequestParameters $params): string
    {
        $response = $this->httpClient->request('POST', $this->siteVerifyUrl, [
            'body' => $params->toArray(),
        ]);

        try {
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            return '{"success": false, "error-codes": ["'.ReCaptcha::E_CONNECTION_FAILED.'"]}';
        }

        if ($statusCode !== 200) {
            return '{"success": false, "error-codes": ["'.ReCaptcha::E_BAD_RESPONSE.'"]}';
        }

        return $response->getContent(false);
    }
}
