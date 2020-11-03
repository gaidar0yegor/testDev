<?php

namespace App\Validator;

use App\Entity\TempsPasse;
use App\Exception\TempsPassesPercentException;
use App\Service\SocieteChecker;
use App\Service\TempsPasseService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Validateur pour vérifier que les pourcentages
 * des temps passés sont valides (entre 0 et 100, pas de negatifs)
 */
class TempsPassesValidValidator extends ConstraintValidator
{
    private $tempsPasseService;

    public function __construct(TempsPasseService $tempsPasseService)
    {
        $this->tempsPasseService = $tempsPasseService;
    }

    /**
     * @param TempsPasse[] $entities
     * @param Constraint $constraint
     */
    public function validate($entities, Constraint $constraint)
    {
        if (!is_iterable($entities)) {
            throw new UnexpectedValueException($entities, 'TempsPasse[]');
        }

        try {
            $this->tempsPasseService->checkPercents($entities);
        } catch (TempsPassesPercentException $e) {
            $this->context
                ->buildViolation($e->getMessage())
                ->addViolation()
            ;
        }
    }
}
