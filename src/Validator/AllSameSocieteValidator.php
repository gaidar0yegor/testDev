<?php

namespace App\Validator;

use App\Service\SocieteChecker;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Validateur pour vérifier qu'une liste d'entités
 * qui implémente HasSocieteInterface
 * sont toutes dans la même société.
 */
class AllSameSocieteValidator extends ConstraintValidator
{
    private $societeChecker;

    public function __construct(SocieteChecker $societeChecker)
    {
        $this->societeChecker = $societeChecker;
    }

    public function validate($entities, Constraint $constraint)
    {
        if (!is_iterable($entities)) {
            throw new UnexpectedValueException($entities, 'HasSocieteInterface[]');
        }

        if (!$this->societeChecker->allSameSociete($entities)) {
            $this->context
                ->buildViolation('All users must be in the same Societe')
                ->addViolation()
            ;
        }
    }
}
