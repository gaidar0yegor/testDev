<?php

namespace App\Form;

use App\Entity\User;
use App\Form\Custom\RdiMobilePhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonCompteType extends AbstractType
{
    private array $locales;

    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'firstname',
            ])
            ->add('nom', TextType::class, [
                'label' => 'lastname',
            ])
            ->add('telephone', RdiMobilePhoneNumberType::class, [
                'help' => 'if_provided_you_can_receive_important_notification_by_sms.',
            ])
            ->add('locale', ChoiceType::class, [
                'choices'  => array_flip($this->locales),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
