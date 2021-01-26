<?php

namespace App\Validator;

use ReflectionClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Validateur pour vérifier que deux dates sont bien dans l'ordre chronologique.
 * Exemple : Projet->dateFin est bien > à Projet->dateDebut.
 */
class DatesOrderedValidator extends ConstraintValidator
{
    public function validate($entity, Constraint $constraint)
    {
        if (!is_object($entity)) {
            throw new UnexpectedValueException($entity, 'object');
        }

        $class = new ReflectionClass(get_class($entity));
        $propertyDateStart = $class->getProperty($constraint->start);
        $propertyDateEnd = $class->getProperty($constraint->end);

        $propertyDateStart->setAccessible(true);
        $propertyDateEnd->setAccessible(true);

        $dateStart = $propertyDateStart->getValue($entity);
        $dateEnd = $propertyDateEnd->getValue($entity);

        if (null === $dateStart || null === $dateEnd) {
            return;
        }

        if ($dateStart > $dateEnd) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath($constraint->end)
                ->addViolation()
            ;
        }
    }
}
