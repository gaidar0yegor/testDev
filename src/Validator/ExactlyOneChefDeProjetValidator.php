<?php

namespace App\Validator;

use App\Entity\ProjetParticipant;
use App\Security\Role\RoleProjet;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ExactlyOneChefDeProjetValidator extends ConstraintValidator
{
    public function validate($projetParticipants, Constraint $constraint)
    {
        if (!is_iterable($projetParticipants)) {
            throw new UnexpectedValueException($projetParticipants, 'iterable');
        }

        $numberOfChefDeProjet = 0;

        foreach ($projetParticipants as $projetParticipant) {
            if (!$projetParticipant instanceof ProjetParticipant) {
                throw new UnexpectedValueException($projetParticipants, 'ProjetParticipant[]');
            }

            if ($projetParticipant->getRole() === RoleProjet::CDP) {
                ++$numberOfChefDeProjet;

                if ($numberOfChefDeProjet > 1) {
                    $this->context
                        ->buildViolation('Must have exactly one Chef de projet, found more than 1')
                        ->addViolation()
                    ;

                    return;
                }
            }
        }

        if (1 !== $numberOfChefDeProjet) {
            $this->context
                ->buildViolation('Must have exactly one Chef de projet, found '.$numberOfChefDeProjet)
                ->addViolation()
            ;
        }
    }
}
