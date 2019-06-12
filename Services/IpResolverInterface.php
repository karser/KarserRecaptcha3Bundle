<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Services;

interface IpResolverInterface
{
    public function resolveIp(): ?string;
}
