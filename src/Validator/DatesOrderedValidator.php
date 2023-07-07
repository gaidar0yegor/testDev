<?php

namespace App\Validator;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
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
        $propertyDateStart = self::getProperty($class, $constraint->start);
        $propertyDateEnd = self::getProperty($class, $constraint->end);

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

    /**
     * Return reflection property of a class, or its parents.
     */
    private static function getProperty(ReflectionClass $class, string $propertyName, ReflectionException $rootException = null): ReflectionProperty
    {
        try {
            return $class->getProperty($propertyName);
        } catch (ReflectionException $e) {
            $parent = $class->getParentClass();

            if (null === $rootException) {
                $rootException = $e;
            }

            if (!$parent) {
                throw $rootException;
            }

            return self::getProperty($parent, $propertyName, $rootException);
        }
    }
}
