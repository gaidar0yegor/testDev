<?php

namespace App\Form;

use App\Entity\Projet;
use App\Entity\RdiDomain;
use App\Form\Custom\RadioChoiceColorsType;
use App\Form\Custom\DatePickerType;
use App\MultiSociete\UserContext;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class ProjetFormType extends AbstractType
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $usedProjectColors = ($options['data'])->getSociete()->getUsedProjectColors();
        $usedProjectColors = is_array($usedProjectColors) ? $usedProjectColors : [];

        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre descriptif',
                'required' => true,
            ])
            ->add('colorCode', ColorType::class, [
                'label' => 'Code couleur',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control-lg border-0 p-0',
                    'title' => 'Palette de couleur',
                ],
            ])
            ->add('usedColorCodes', RadioChoiceColorsType::class, [
                'label' => false,
                'mapped' => false,
                'expanded' => true,
                'required' => false,
                'choices' => $usedProjectColors,
            ])
            ->add('acronyme', TextType::class, [
                'label' => 'Titre réduit / Acronyme',
                'required' => true,
            ])
            ->add('resume', CKEditorType::class, [
                'label' => 'Résumé',
                'required' => true,
                'attr' => [
                    'class' => 'ckeditor-instance',
                ],
            ])
            ->add('dateDebut', DatePickerType::class, [
                'required' => false,
                'label' => 'Date de début',
            ])
            ->add('dateFin', DatePickerType::class, [
                'required' => false,
                'label' => 'Date de fin',
            ])
            ->add('etp', NumberType::class, [
                'label' => 'ETP',
                'help' => 'projet.etp.help',
                'help_html' => true,
                'required' => false,
            ])
            ->add('projetUrls', CollectionType::class, [
                'label' => 'projet.projetUrls',
                'help' => 'projet.projetUrls.help',
                'entry_type' => ProjetUrlType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('projetCollaboratif', CheckboxType::class, [
                'label' => 'Projet en collaboration avec au moins un partenaire externe',
                'required' => false,
                'label_attr' => ['class' => 'switch-custom'],
            ])
            ->add('projetPpp', CheckboxType::class, [
                'label' => 'Projet en collaboration avec un partenaire universitaire',
                'required' => false,
                'label_attr' => ['class' => 'switch-custom'],
            ])
            ->add('projetInterne', CheckboxType::class, [
                'label' => 'Projet réalisé en interne par la société (avec ou sans prestataires)',
                'required' => false,
                'label_attr' => ['class' => 'switch-custom'],
            ])
            ->add('dossierFichierProjets', CollectionType::class, [
                'label' => 'projet.dossierFichierProjets',
                'help' => 'projet.dossierFichierProjets.help',
                'entry_type' => DossierFichierProjetType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('rdiDomains', EntityType::class, [
                'label' => 'Domaine(s) du projet',
                'class' => RdiDomain::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('rdiDomain')
                        ->where('rdiDomain.level != 0')
                        ->orderBy('rdiDomain.nom', 'ASC');
                },
                'choice_label' => function (RdiDomain $rdiDomain) {
                    return $rdiDomain->getNom();
                },
                'required' => false,
                'multiple'    => true,
                'expanded' 	  => false,
                'attr' => [
                    'class' => 'select-2 form-control',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Soumettre',
                'attr' => [
                    'class' => 'mt-5 btn btn-success',
                ],
            ])
            ->addEventListener(FormEvents::SUBMIT, [$this, 'setDossierFichierProjet'])
        ;
    }

    public function setDossierFichierProjet(SubmitEvent $event)
    {
        foreach ($event->getData()->getDossierFichierProjets() as $dossierFichierProjet) {
            if (null !== $dossierFichierProjet->getId()) {
                continue;
            }

            $dossierFichierProjet->setDefaultFolderName();
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
        ]);
    }
}
