<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Validateur pour vérifier qu'au moins un des champs est rempli.
 */
class NotBlankEitherValidator extends ConstraintValidator
{
    public function validate($entity, Constraint $constraint)
    {
        if (!is_object($entity)) {
            throw new UnexpectedValueException($entity, 'object');
        }

        foreach ($constraint->fields as $field) {
            if (null !== $entity->{'get'.$field}()) {
                return;
            }
        }

        foreach ($constraint->fields as $field) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath($field)
                ->addViolation()
            ;
        }
    }
}
