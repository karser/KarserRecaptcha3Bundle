<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Validator\Constraints;

use Karser\Recaptcha3Bundle\Services\IpResolverInterface;
use ReCaptcha\ReCaptcha;
use ReCaptcha\Response;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class Recaptcha3Validator extends ConstraintValidator
{
    /** @var ReCaptcha */
    private $recaptcha;
    private $enabled;
    private $ipResolver;

    /** @var Response|null */
    private $lastResponse;

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
        $this->validateCaptcha($value, $constraint);
    }

    public function getLastResponse(): ?Response
    {
        return $this->lastResponse;
    }

    private function validateCaptcha(string $value, Recaptcha3 $constraint): void
    {
        if ($value === '') {
            $this->buildViolation($constraint->messageMissingValue, $value);
            return;
        }
        $ip = $this->ipResolver->resolveIp();
        $this->lastResponse = $response = $this->recaptcha->verify($value, $ip);
        if (!$response->isSuccess()) {
            $errorCodes = implode('; ', array_map([$this, 'getErrorMessage'], $response->getErrorCodes()));
            $this->buildViolation($constraint->message, $value, $errorCodes);
        }
    }

    private function getErrorMessage(string $errorCode): string
    {
        $messages = [
            'missing-input-secret' => 'The secret parameter is missing',
            'invalid-input-secret' => 'The secret parameter is invalid or malformed',
            'missing-input-response' => 'The response parameter is missing',
            'invalid-input-response' => 'The response parameter is invalid or malformed',
            'bad-request' => 'The request is invalid or malformed',
            'timeout-or-duplicate' => 'The response is no longer valid: either is too old or has been used previously',
            'challenge-timeout' => 'Challenge timeout',
            'score-threshold-not-met' => 'Score threshold not met',
            'bad-response' => 'Did not receive a 200 from the service',
            'connection-failed' => 'Could not connect to service',
            'invalid-json' => 'Invalid JSON received',
            'unknown-error' => 'Not a success, but no error codes received',
            'hostname-mismatch' => 'Expected hostname did not match',
            'apk_package_name-mismatch' => 'Expected APK package name did not match',
            'action-mismatch' => 'Expected action did not match',
        ];
        return $messages[$errorCode] ?? $errorCode;
    }

    private function buildViolation(string $message, string $value, string $errorCodes = ''): void
    {
        $this->context->buildViolation($message)
            ->setParameter('{{ value }}', $this->formatValue($value))
            ->setParameter('{{ errorCodes }}', $this->formatValue($errorCodes))
            ->setCode(Recaptcha3::INVALID_FORMAT_ERROR)
            ->addViolation();
    }
}
