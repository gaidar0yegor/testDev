<?php

namespace App\Form;

use App\Entity\FichierProjet;
use App\Entity\Projet;
use App\Service\FichierProjetService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FichierProjetRenameType extends AbstractType
{
    private FichierProjetService $fichierProjetService;

    public function __construct(FichierProjetService $fichierProjetService)
    {
        $this->fichierProjetService = $fichierProjetService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fichierProjet = $options['data'];
        $projet = $fichierProjet->getProjet();

        $builder
            ->add('fichier', FichierRenameType::class )
            ->add('societeUser', ChoiceType::class, [
                'label' => 'Droits de visibilité',
                'required'    => false,
                'multiple'    => true,
                'expanded' 	  => false,
                'attr' => [
                    'class' => 'select-2',
                    'placeholder' => 'Ce fichier sera accessible par ...'
                ],
                'choices' => FichierProjetService::getChoicesForAddFileAccess($projet),
                'data' => $fichierProjet->getAccessesChoices(),
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

        if ($fichierProjet instanceof FichierProjet) {
            $fichierProjet->getSocieteUsers()->map(function($societeUser) use ($fichierProjet) {
                $fichierProjet->removeSocieteUser($societeUser); return true;
            });

            $this->fichierProjetService->setAccessChoices($fichierProjet, $projet, $accessChoices);

        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FichierProjet::class,
        ]);
    }
}
