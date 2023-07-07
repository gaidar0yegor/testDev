<?php

namespace App\Form;

use App\DTO\RecommandationMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecommandationMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from', EmailType::class, [
                'label' => 'De',
                'disabled' => true,
            ])
            ->add('to', EmailType::class, [
                'label' => 'À',
                'required' => true,
            ])
            ->add('subject', null, [
                'label' => 'Sujet',
                'required' => true,
            ])
            ->add('customText', TextareaType::class, [
                'label' => 'Texte personnalisé',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RecommandationMessage::class,
        ]);
    }
}
