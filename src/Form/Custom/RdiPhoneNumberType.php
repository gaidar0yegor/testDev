<?php

namespace App\Form\Custom;

use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RdiPhoneNumberType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false,
            'label' => 'Mobile',
            'format' => PhoneNumberFormat::NATIONAL,
            'default_region' => 'FR',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return PhoneNumberType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'rdi_phone_number_type';
    }
}
