<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class Recaptcha3 extends Constraint
{
    const INVALID_FORMAT_ERROR = '7147ffdb-0af4-4f7a-bd5e-e9dcfa6d7a2d';

    protected const ERROR_NAMES = [
        self::INVALID_FORMAT_ERROR => 'INVALID_FORMAT_ERROR',
    ];

    protected static $errorNames = self::ERROR_NAMES;

    public string $message = 'Your computer or network may be sending automated queries';
    public string $messageMissingValue = 'The captcha value is missing';

    /**
     * @param array<string, mixed>|string|null $options
     * @param string[]|null $groups
     * @param mixed $payload
     */
    public function __construct(
        array|string|null $options = null,
        ?string $message = null,
        ?string $messageMissingValue = null,
        ?array $groups = null,
        mixed $payload = null
    ) {
        // Handle both old array-based and new named-parameter style
        if (is_array($options)) {
            // Extract named parameters from options array for backward compatibility
            $message = $message ?? $options['message'] ?? null;
            $messageMissingValue = $messageMissingValue ?? $options['messageMissingValue'] ?? null;
            $groups = $groups ?? $options['groups'] ?? null;
            $payload = $payload ?? $options['payload'] ?? null;
        }

        // Set properties directly in constructor (Symfony 7.4+ requirement)
        if ($message !== null) {
            $this->message = $message;
        }
        if ($messageMissingValue !== null) {
            $this->messageMissingValue = $messageMissingValue;
        }

        // Pass null as first argument to avoid legacy option processing
        // This prevents the deprecation warning in Symfony 7.4+
        parent::__construct(null, $groups, $payload);
    }
}
