<?php

namespace App\Form;

use App\DTO\FilterUserEvenement;
use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use App\Repository\SocieteUserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class FilterUserEventType extends AbstractType
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('users', EntityType::class, [
                'class' => SocieteUser::class,
                'choice_label' => function (SocieteUser $choice, $key, $value): string {
                    return $choice->getUser()->getFullnameOrEmail();
                },
                'query_builder' => function (SocieteUserRepository $repository) use ($options) {
                    $qb = $options['forTeamMembers']
                        ? $repository->queryBuilderTeamMembers($this->userContext->getSocieteUser())
                        : $repository->whereSociete($this->userContext->getSocieteUser()->getSociete());
                    $qb->andWhere('societeUser.user is not NULL')
                        ->andWhere('societeUser.enabled = true');
                    return $qb;
                },
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'label' => 'Utilisateurs :',
            ])
            ->add('eventTypes', ChoiceType::class, [
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'label' => 'Type d\'évènements :',
                'choices' => array_combine($builder->getData()->getEventTypes(), $builder->getData()->getEventTypes())
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filtrer',
                'attr' => [
                    'class' => 'btn btn-success',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FilterUserEvenement::class,
            'forTeamMembers' => false
        ]);
    }
}
