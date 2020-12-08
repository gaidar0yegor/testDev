<?php

namespace App\Form;

use App\Entity\Fichier;
use App\Service\FichierService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;


class FichierType extends AbstractType
{
    private FichierService $fichierService;

    public function __construct(FichierService $fichierService)
    {
        $this->fichierService = $fichierService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'label' => false,
                'required' 	=> true,
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'uploadFichier'])
        ;
    }

    public function uploadFichier(PostSubmitEvent $event)
    {
        $fichier = $event->getData();

        // In case no file has been selected
        if (null === $fichier) {
            return;
        }

        // In case file has already been imported and entity is just updating
        if (null === $fichier->getFile()) {
            return;
        }

        $this->fichierService->upload($fichier);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Fichier::class,
        ]);
    }
}
