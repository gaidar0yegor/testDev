<?php

namespace App\RegisterSociete\Form;

use App\Form\Custom\VerificationCodeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AccountVerificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('verificationCode', VerificationCodeType::class, [
                'label' => 'Code de vérification',
                'help' => 'Veuillez saisir le code que vous avez reçu par email.'
            ])
        ;
    }
}
