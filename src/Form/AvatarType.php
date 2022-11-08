<?php

namespace App\Form;

use App\File\FileHandler\AvatarHandler;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class AvatarType extends FichierType
{
    private AvatarHandler $avatarHandler;

    public function __construct(AvatarHandler $avatarHandler)
    {
        $this->avatarHandler = $avatarHandler;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('nomFichier')
            ->remove('externalLink')
            ->add('file', FileType::class, [
                'label' => false,
                'required' 	=> false,
                'attr' => [
                    'class' => 'upload-img',
                ],
                'constraints' => [
                    new Image([
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'fileHandler' => $this->avatarHandler,
        ]);
    }
}
