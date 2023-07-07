<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DatesOrdered extends Constraint
{
    public $start;

    public $end;

    public $message = 'La date de fin doit être égale ou après la date de début.';

    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
