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
        if ($value !== null && !is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }
        if (!$constraint instanceof Recaptcha3) {
            throw new UnexpectedTypeException($constraint, Recaptcha3::class);
        }
        if (!$this->enabled) {
            return;
        }
        $value = null !== $value ? (string) $value : '';
        if (!$this->validateCaptcha($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(Recaptcha3::INVALID_FORMAT_ERROR)
                ->addViolation();
        }
    }

    private function validateCaptcha(string $value): bool
    {
        if ($value === '') {
            return false;
        }
        $ip = $this->ipResolver->resolveIp();
        $response = $this->recaptcha->verify($value, $ip);

        return $response->isSuccess();
    }
}
