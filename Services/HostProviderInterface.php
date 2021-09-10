<?php

declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Services;

interface HostProviderInterface
{
    public const DEFAULT_HOST = 'google.com';
    public const ALT_HOST = 'recaptcha.net';

    public function getHost(): string;

    public function getVerifyUrl(): string;
}