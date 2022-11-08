<?php

namespace App\Form\LabApp;

use App\Entity\Fichier;
use App\File\FileHandler\EtudeBannerHandler;
use App\Form\FichierType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class EtudeBannerType extends FichierType
{
    private EtudeBannerHandler $etudeBannerHandler;

    public function __construct(
        EtudeBannerHandler $etudeBannerHandler
    )
    {
        $this->etudeBannerHandler = $etudeBannerHandler;
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
            'fileHandler' => $this->etudeBannerHandler,
        ]);
    }
}
