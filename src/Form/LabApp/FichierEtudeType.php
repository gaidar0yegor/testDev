<?php

namespace App\Form\LabApp;

use App\Entity\LabApp\Etude;
use App\Entity\LabApp\FichierEtude;
use App\File\FileHandler\EtudeFileHandler;
use App\Form\FichierType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Contracts\Translation\TranslatorInterface;

class FichierEtudeType extends AbstractType
{
    private EtudeFileHandler $etudeFileHandler;
    private TranslatorInterface $translator;

    public function __construct(
        EtudeFileHandler $etudeFileHandler,
        TranslatorInterface $translator
    )
    {
        $this->etudeFileHandler = $etudeFileHandler;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $etude = $options['etude'];

        $builder
            ->add('fichier', FichierType::class, [
                'label' => false,
                'fileHandler' => $this->etudeFileHandler,
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function ($event) use ($etude){
                $this->uploadFichier($event,$etude);
            })
        ;
    }

    public function uploadFichier(PostSubmitEvent $event, Etude $etude)
    {
        $fichierEtude = $event->getData();

        if (!$fichierEtude instanceof FichierEtude) {
            return;
        }

        // In case no file has been selected
        if (null === $fichierEtude) {
            return;
        }

        $fichier = $fichierEtude->getFichier();

        // In case file has already been imported and entity is just updating
        if (null === $fichier->getFile()) {
            return;
        }

        $fichierEtude->setEtude($etude);

        $this->etudeFileHandler->upload($fichierEtude);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FichierEtude::class,
            'etude' => null,
        ]);
        $resolver->setDefined([
            'etude',
        ]);
        $resolver->setRequired([
            'etude',
        ]);
        $resolver->setAllowedTypes('etude', [
            Etude::class,
        ]);
    }
}
