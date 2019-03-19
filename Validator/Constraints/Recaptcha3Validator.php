<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Validator\Constraints;

use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class Recaptcha3Validator extends ConstraintValidator
{
    /** @var ReCaptcha */
    private $recaptcha;

    /** @var bool */
    private $enabled;

    /** @var RequestStack */
    private $requestStack;

    public function __construct($recaptcha, bool $enabled, RequestStack $requestStack)
    {
        $this->recaptcha = $recaptcha;
        $this->enabled = $enabled;
        $this->requestStack = $requestStack;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Recaptcha3) {
            throw new UnexpectedTypeException($constraint, Recaptcha3::class);
        }

        if (!$this->enabled) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $ip = $request ? $request->server->get('HTTP_CF_CONNECTING_IP') ?? $request->getClientIp() : null;

        $response = $this->recaptcha->verify($value, $ip);
        if (!$response->isSuccess()) {
            $this->context->addViolation($constraint->message);
        }
    }
}
