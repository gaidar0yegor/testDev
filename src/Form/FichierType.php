<?php

namespace App\Form;

use App\Entity\Fichier;
use App\File\FileHandler\EtudeFileHandler;
use App\File\FileHandler\ProjectFileHandler;
use App\File\FileHandlerInterface;
use LogicException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;


class FichierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'label' => false,
                'required' 	=> false,
                'constraints' => [
                    new File([
                        'maxSize' => Fichier::MAX_FILE_SIZE,
                    ]),
                ],
            ])
            ->add('externalLink', TextType::class, [
                'label' => false,
                'required' 	=> false,
                'attr' => [
                    'placeholder' => 'Lien externe du fichier ...',
                ]
            ])
            ->add('nomFichier', TextType::class, [
                'label' => false,
                'required' 	=> true,
                'attr' => [
                    'class' => 'form-file-name',
                    'placeholder' => 'Nom du fichier ...',
                ]
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'uploadFichier'])
        ;
    }

    public function uploadFichier(PostSubmitEvent $event)
    {
        $fileHandler = $event->getForm()->getConfig()->getOption('fileHandler');

        if (!$fileHandler instanceof FileHandlerInterface) {
            throw new LogicException('$fileHandler must be an instance of '.FileHandlerInterface::class);
        }

        if ($fileHandler instanceof ProjectFileHandler || $fileHandler instanceof EtudeFileHandler){
            return;
        }

        $fichier = $event->getData();

        // In case no file has been selected
        if (null === $fichier) {
            return;
        }

        // In case file has already been imported and entity is just updating
        if (null === $fichier->getFile() || $fichier->getId() !== null) {
            return;
        }

        $fileHandler->upload($fichier);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Fichier::class,
        ]);

        $resolver->setRequired('fileHandler');
        $resolver->setAllowedTypes('fileHandler', FileHandlerInterface::class);
    }
}
