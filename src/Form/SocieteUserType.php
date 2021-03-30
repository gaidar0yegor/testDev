<?php

namespace App\Form;

use App\Entity\SocieteUser;
use App\Form\Custom\DatePickerType;
use App\Security\Role\RoleSociete;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocieteUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('role',  ChoiceType::class, [
            'label' => 'Rôle',
            'choices' => [
                RoleSociete::USER => RoleSociete::USER,
                RoleSociete::CDP => RoleSociete::CDP,
                RoleSociete::ADMIN => RoleSociete::ADMIN,
            ],
        ])
        ->add('heuresParJours', NumberType::class, [
            'label' => 'Nombre d\'heures travaillées par jour',
            'help' => 'Pour cet utilisateur uniquement, vous pouvez remplacer ici le nombre d\'heure défini globalement au niveau de la société.',
            'required' => false,
            'attr' => [
                'placeholder' => $this->getHeuresPlaceholder($builder),
            ],
        ])
        ->add('dateEntree', DatePickerType::class, [
            'label' => 'Date d\'entrée',
            'required' => false,
        ])
        ->add('dateSortie', DatePickerType::class, [
            'label' => 'Date de sortie',
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
