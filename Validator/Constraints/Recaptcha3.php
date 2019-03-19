<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Recaptcha3 extends Constraint
{
    public $message = 'Your computer or network may be sending automated queries';
}
