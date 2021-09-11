<?php

declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Services;

use ReCaptcha\ReCaptcha;

final class HostProvider implements HostProviderInterface
{
    /** @var string */
    private $host;

    public function __construct(string $host)
    {
        $this->host = $host;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getVerifyUrl(): string
    {
        return str_replace(self::DEFAULT_HOST, $this->host, ReCaptcha::SITE_VERIFY_URL);
    }
}