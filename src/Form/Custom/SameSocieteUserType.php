<?php

namespace App\Form\Custom;

use App\Entity\SocieteUser;
use App\Repository\SocieteUserRepository;
use App\MultiSociete\UserContext;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Champ de formulaire qui sert à séléctionner un utilisateur
 * dans la même société.
 */
class SameSocieteUserType extends AbstractType
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => SocieteUser::class,
            'choice_label' => function (SocieteUser $choice, $key, $value): string {
                return $choice->getUser()->getFullnameOrEmail();
            },
            'query_builder' => function (SocieteUserRepository $repository) {
                $societeUser = $this->userContext->getSocieteUser();

                return $repository->whereSociete($societeUser->getSociete());
            },
            'choice_attr' => function($societeUser) {
                return $societeUser->getEnabled() ? [] : ['disabled' =>  'disabled'];
            }
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'same_societe_user';
    }
}
