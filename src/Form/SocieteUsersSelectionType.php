<?php

namespace App\Form;

use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use App\Repository\SocieteUserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SocieteUsersSelectionType extends AbstractType
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('societeUsers', EntityType::class, [
                'block_prefix' => 'societe_users_card_choice',
                'class' => SocieteUser::class,
                'query_builder' => function (SocieteUserRepository $repository) {
                    $societeUser = $this->userContext->getSocieteUser();

                    return $repository
                        ->whereSociete($societeUser->getSociete())
                        ->andWhere('societeUser.invitationToken is null')
                        ->andWhere('societeUser != :me')
                        ->setParameter('me', $this->userContext->getSocieteUser())
                        ->andWhere('societeUser.enabled = true')
                    ;
                },
                'choice_label' => function (SocieteUser $societeUser) {
                    return $societeUser->getUser()->getFullname();
                },
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }
}
