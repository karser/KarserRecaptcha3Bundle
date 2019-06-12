<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Services;

use Symfony\Component\HttpFoundation\RequestStack;

final class IpResolver implements IpResolverInterface
{
    /** @var RequestStack */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function resolveIp(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return null;
        }
        return $request->getClientIp();
    }
}
