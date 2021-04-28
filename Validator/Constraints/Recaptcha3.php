<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class Recaptcha3 extends Constraint
{
    const INVALID_FORMAT_ERROR = '7147ffdb-0af4-4f7a-bd5e-e9dcfa6d7a2d';

    protected static $errorNames = [
        self::INVALID_FORMAT_ERROR => 'INVALID_FORMAT_ERROR',
    ];

    public $message = 'Your computer or network may be sending automated queries';
    public $messageMissingValue = 'The captcha value is missing';
}
