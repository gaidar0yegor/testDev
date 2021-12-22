<?php

namespace App\Form;

use App\Entity\FichierProjet;
use App\Entity\Projet;
use App\File\FileHandler\ProjectFileHandler;
use App\Service\FichierProjetService;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;

class FichierProjetType extends AbstractType
{
    private ProjectFileHandler $projectFileHandler;
    private FichierProjetService $fichierProjetService;

    public function __construct(ProjectFileHandler $projectFileHandler, FichierProjetService $fichierProjetService)
    {
        $this->projectFileHandler = $projectFileHandler;
        $this->fichierProjetService = $fichierProjetService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $projet = $options['projet'];

        $builder
            ->add('fichier', FichierType::class, [
                'label' => false,
                'fileHandler' => $this->projectFileHandler,
            ])
            ->add('societeUser', ChoiceType::class, [
                'label' => false,
                'multiple'    => true,
                'expanded' 	  => false,
                'attr' => [
                    'class' => 'select-2 form-control',
                    'data-placeholder' => 'Droits de visibilité (Par défaut : Tous)'
                ],
                'choices' => FichierProjetService::getChoicesForAddFileAccess($projet),
                'mapped' => false
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function($event) use ($projet) {
                $this->fileAccessManagement($event, $projet);
            })
        ;
    }

    public function fileAccessManagement(PostSubmitEvent $event, Projet $projet)
    {
        $fichierProjet = $event->getData();
        $accessChoices = $event->getForm()->get('societeUser')->getData();

        if (
            $fichierProjet instanceof FichierProjet &&
            (
                !$fichierProjet->getId() || ($fichierProjet->getId() && $this->fichierProjetService->isAccessibleFichierProjet($fichierProjet))
            )
        ) {
            $this->fichierProjetService->setAccessChoices($fichierProjet, $projet, $accessChoices);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FichierProjet::class,
            'projet' => null,
        ]);
        $resolver->setDefined([
            'projet',
        ]);
        $resolver->setRequired([
            'projet',
        ]);
        $resolver->setAllowedTypes('projet', [
            Projet::class,
        ]);
    }
}
