<?php

namespace App\Form;

use App\Entity\DossierFichierProjet;
use App\Entity\FichierProjet;
use App\Entity\Projet;
use App\File\FileHandler\ProjectFileHandler;
use App\Service\FichierProjetService;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\ProductPrivilegeCheker;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Contracts\Translation\TranslatorInterface;

class FichierProjetType extends AbstractType
{
    private ProjectFileHandler $projectFileHandler;
    private FichierProjetService $fichierProjetService;
    private TranslatorInterface $translator;

    public function __construct(
        ProjectFileHandler $projectFileHandler,
        FichierProjetService $fichierProjetService,
        TranslatorInterface $translator
    )
    {
        $this->projectFileHandler = $projectFileHandler;
        $this->fichierProjetService = $fichierProjetService;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $projet = $options['projet'];

        $hasPrivilegeFichierProjetAccesses = ProductPrivilegeCheker::checkProductPrivilege(
            $projet->getSociete(),
            ProductPrivileges::FICHIER_PROJET_ACCESSES
        );

        $builder
            ->add('fichier', FichierType::class, [
                'label' => false,
                'fileHandler' => $this->projectFileHandler,
            ])
            ->add('accessesChoices', ChoiceType::class, [
                'label' => 'Les droits de visibilité (Par défaut : Tous)',
                'multiple'    => true,
                'expanded' 	  => false,
                'attr' => [
                    'class' => 'select-2 form-control',
                    'title' => !$hasPrivilegeFichierProjetAccesses ? $this->translator->trans('product_privilege_no_dispo') : false,
                ],
                'disabled' => !$hasPrivilegeFichierProjetAccesses,
                'choices' => FichierProjetService::getChoicesForAddFileAccess($projet),
            ])
            ->add('dossierFichierProjet', EntityType::class, [
                'label' => 'Les dossiers du projet',
                'class' => DossierFichierProjet::class,
                'choice_label' => 'nom',
                'query_builder' => function (EntityRepository $er) use ($projet) {
                    return $er->createQueryBuilder('dfp')
                        ->where('dfp.projet = :projet')
                        ->orderBy('dfp.nom', 'ASC')
                        ->setParameter('projet',$projet->getId())
                        ;
                },
                'placeholder' => 'Sélectionner un dossier ...',
                'attr' => [
                    'class' => 'select-2 form-control',
                ],
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function ($event) use ($projet){
                $this->uploadFichier($event,$projet);
            })
        ;
    }

    public function uploadFichier(PostSubmitEvent $event, Projet $projet)
    {
        $fichierProjet = $event->getData();

        if (!$fichierProjet instanceof FichierProjet) {
            return;
        }

        // In case no file has been selected
        if (null === $fichierProjet) {
            return;
        }

        $fichier = $fichierProjet->getFichier();

        // In case file has already been imported and entity is just updating
        if (null !== $fichier->getId() && (null === $fichier->getFile() || null !== $fichier->getExternalLink())) {
            return;
        }

        $fichierProjet->setProjet($projet);

        $this->projectFileHandler->upload($fichierProjet);
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
