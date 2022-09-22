<?php

namespace App\Form;

use App\Entity\Patchnote;
use App\Form\Custom\DatePickerType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PatchnoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('notes', CKEditorType:: class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'ckeditor-instance',
                ],
                'constraints'=>[
                    new NotBlank(),
                ]
            ])
            ->add('date', DatePickerType::class, [
                'label' => 'Date',
                'attr' => [
                    'class' => 'text-center date-picker',
                ],
            ])
            ->add('version', TextType::class, [
                'label' => 'Version',
                'attr' => [
                    'class' => 'text-center date-picker',
                ],
                'disabled' => true
            ])
            ->add('isDraft', CheckboxType::class, [
                'label' => 'Enregistrer en tant que brouillon',
                'required' => false,
                'label_attr' => [
                    'class' => 'switch-custom',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-success',
                ],
            ])
          ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Patchnote::class
        ]);
    }
}
