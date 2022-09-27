<?php

namespace App\Form;

use App\Entity\ProjetParticipant;
use App\Entity\ProjetPlanning;
use App\Entity\ProjetPlanningTask;
use App\Repository\ProjetParticipantRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetPlanningTaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $projetPlanning = $builder->getOption('projetPlanning');
        $projet = $projetPlanning->getProjet();

        $builder
            ->add('participants', EntityType::class, [
                'placeholder' => 'Liste des utilisateurs',
                'class' => ProjetParticipant::class,
                'query_builder' => function(ProjetParticipantRepository $repo) use ($projet) {
                    return $repo->createByProjetQueryBuilder($projet);
                },
                'choice_label' => function (ProjetParticipant $choice, $key, $value): string {
                    return $choice->getSocieteUser()->getUser()->getFullnameOrEmail();
                },
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['projetPlanning'] = $options['projetPlanning'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjetPlanningTask::class,
            'projetPlanning' => null
        ]);
        $resolver->setDefined([
            'projetPlanning',
        ]);
        $resolver->setRequired([
            'projetPlanning',
        ]);
        $resolver->setAllowedTypes('projetPlanning', [
            ProjetPlanning::class,
        ]);
    }
}
