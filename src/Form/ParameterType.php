<?php

namespace App\Form;

use App\Entity\Parameter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParameterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $label = null;
        $help = null;
        $parameter = isset($options['data']) ? $options['data'] : null;

        if ($parameter instanceof Parameter) {
            $label = 'parameter.'.$parameter->getName();
            $help = $parameter->getHelpText();
        }

        $builder
            ->add('value', TextType::class, [
                'label' => $label,
                'help' => $help,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Parameter::class,
        ]);
    }
}
