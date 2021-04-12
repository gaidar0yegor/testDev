<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotBlankEither extends Constraint
{
    public $fields;

    public $message = 'Au moins un de ces champs doit être rempli.';

    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
