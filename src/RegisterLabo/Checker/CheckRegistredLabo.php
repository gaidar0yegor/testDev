<?php

namespace App\RegisterLabo\Checker;

use App\Entity\LabApp\Labo;
use App\Entity\LabApp\UserBook;
use App\Exception\RdiException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

/**
 * Service qui s'occupe de vérifier le labo choisir ou créer lors
 * de la création d'un cahier de laboratoire
 */
class CheckRegistredLabo
{
    /**
     * @throws RdiException Si $user n'est pas valide.
     */
    public function checkLabo(UserBook $userBook, FormInterface $form): ?Labo
    {
        $newLabo = $form->getData();
        $existedLabo = $form->get('existedLabo')->getData();

        if (null !== $newLabo->getName() && null !== $newLabo->getRnsr()){
            $newLabo->addUserBook($userBook);
            return $newLabo;
        }

        if (null !== $existedLabo){
            $existedLabo->addUserBook($userBook);
            return $existedLabo;
        }

        $form->addError(new FormError('Veillez choisir ou créer un laboratoire.'));

        return null;
    }
}
