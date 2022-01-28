<?php

namespace App\Form;

use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use App\Repository\SocieteUserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocieteUserSuperiorType extends AbstractType
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mySuperior', EntityType::class, [
                'label' => false,
                'class' => SocieteUser::class,
                'required' => false,
                'query_builder' => function (SocieteUserRepository $repository) {
                    $societeUser = $this->userContext->getSocieteUser();

                    return $repository
                        ->whereSociete($societeUser->getSociete())
                        ->andWhere('societeUser.invitationToken is null')
                        ->andWhere('societeUser != :me')
                        ->setParameter('me', $this->userContext->getSocieteUser())
                        ->andWhere('societeUser.enabled = true');
                },
                'choice_label' => function (SocieteUser $societeUser) {
                    return $societeUser->getUser()->getFullname();
                },
                'placeholder' => 'Sélectionnez votre supérieur (N+1)',
                'attr' => [
                    'class' => 'select-2 form-control',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SocieteUser::class,
        ]);
    }
}
