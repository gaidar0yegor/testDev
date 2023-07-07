<?php

namespace App\RegisterLabo\Checker;

use App\Entity\LabApp\Labo;
use App\Entity\LabApp\UserBook;
use App\Entity\LabApp\UserBookInvite;
use App\Exception\RdiException;
use App\MultiSociete\UserContext;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

/**
 * Service qui s'occupe de vérifier le cahier de laboratoire choisir ou créer lors
 * de l'intégration d'un laboratoire
 */
class CheckJoinedUserBook
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * @throws RdiException Si $user n'est pas valide.
     */
    public function checkUserBook(UserBookInvite $userBookInvite, FormInterface $form): ?UserBook
    {
        $labo = $userBookInvite->getLabo();
        $existedUserBook = $form->get('existedUserBook')->getData();
        $newUserBook = $form->getData();

        if (null !== $newUserBook->getTitle()){
            $newUserBook
                ->setLabo($labo)
                ->setUser($this->userContext->getUser())
                ->setRole($userBookInvite->getRole())
            ;
            return $newUserBook;
        }

        if (null !== $existedUserBook){
            $existedUserBook
                ->setLabo($labo)
                ->setRole($userBookInvite->getRole())
            ;
            return $existedUserBook;
        }

        $form->addError(new FormError('Veillez choisir ou créer un cahier de laboratoire.'));

        return null;
    }
}
