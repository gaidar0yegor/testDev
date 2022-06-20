<?php

namespace App\RegisterLabo\Form;

use App\Entity\LabApp\UserBook;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserBookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label' => 'Titre',
                'attr' => [
                    'class' => 'form-control-lg',
                ],
                'help' => 'Titre de votre cahier de laboratoire.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserBook::class,
            'validation_groups' => ['registration'],
        ]);
    }
}
