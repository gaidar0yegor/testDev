<?php

namespace App\Form;

use App\Entity\SocieteUser;
use App\Form\Custom\DatePickerType;
use App\Form\Custom\FoRoleCardChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocieteUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('role',  FoRoleCardChoiceType::class)
        ->add('heuresParJours', NumberType::class, [
            'help' => 'Pour cet utilisateur uniquement, vous pouvez remplacer ici le nombre d\'heure défini globalement au niveau de la société.',
            'required' => false,
            'attr' => [
                'placeholder' => $this->getHeuresPlaceholder($builder),
            ],
        ])
        ->add('dateEntree', DatePickerType::class, [
            'required' => false,
        ])
        ->add('dateSortie', DatePickerType::class, [
            'required' => false,
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SocieteUser::class,
        ]);
    }

    private function getHeuresPlaceholder(FormBuilderInterface $builder): string
    {
        $defaultHeuresParJour = $builder->getData()->getSociete()->getHeuresParJours();

        if (null === $defaultHeuresParJour) {
            return '';
        }

        return sprintf('Par défaut : %.2f', $defaultHeuresParJour);
    }
}
