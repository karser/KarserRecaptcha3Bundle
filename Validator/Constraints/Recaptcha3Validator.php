<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Validator\Constraints;

use Karser\Recaptcha3Bundle\Services\IpResolverInterface;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class Recaptcha3Validator extends ConstraintValidator
{
    /** @var ReCaptcha */
    private $recaptcha;

    /** @var bool */
    private $enabled;

    /** @var IpResolverInterface */
    private $ipResolver;

    public function __construct($recaptcha, bool $enabled, IpResolverInterface $ipResolver)
    {
        $this->recaptcha = $recaptcha;
        $this->enabled = $enabled;
        $this->ipResolver = $ipResolver;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Recaptcha3) {
            throw new UnexpectedTypeException($constraint, Recaptcha3::class);
        }

        if (!$this->enabled) {
            return;
        }

        $ip = $this->ipResolver->resolveIp();

        $response = $this->recaptcha->verify($value, $ip);
        if (!$response->isSuccess()) {
            $this->context->addViolation($constraint->message);
        }
    }
}
