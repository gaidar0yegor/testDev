<?php

namespace App\LicenseGeneration\Form;

use App\Form\Custom\DatePickerType;
use App\License\DTO\License;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenerateLicenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('societe', SocieteType::class)
            ->add('name')
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('expirationDate', DatePickerType::class)
            ->add('quotas', QuotaType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => License::class,
        ]);
    }
}
