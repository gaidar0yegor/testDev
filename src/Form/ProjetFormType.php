<?php

namespace App\Form;

use App\Entity\Projet;
use App\Entity\RdiDomain;
use App\Form\Custom\RadioChoiceColorsType;
use App\Form\Custom\DatePickerType;
use App\MultiSociete\UserContext;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\ProductPrivilegeCheker;
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
use Symfony\Contracts\Translation\TranslatorInterface;


class ProjetFormType extends AbstractType
{
    private UserContext $userContext;
    private TranslatorInterface $translator;

    public function __construct(UserContext $userContext, TranslatorInterface $translator)
    {
        $this->userContext = $userContext;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $projet = $builder->getData();

        $societeCurrency = $projet->getSociete()->getCurrency();
        $usedProjectColors = $projet->getSociete()->getUsedProjectColors();
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
                'label' => 'ETP <i class="fa fa-question-circle" title="'. $this->translator->trans('projet.etp.label.help') .'"></i>',
                'label_html' => true,
                'help' => 'projet.etp.help',
                'help_html' => true,
                'required' => false,
            ])
            ->add('budgetEuro', NumberType::class, [
                'label' => 'Budget (' . $societeCurrency .') <i class="fa fa-question-circle" title="'. $this->translator->trans('projet.budget_euro.label.help') .'"></i>',
                'label_html' => true,
                'required' => false,
            ])
            ->add('roiEnabled', CheckboxType::class, [
                'label' => 'ROI <i class="fa fa-question-circle" title="'. $this->translator->trans('return_on_investment') .'"></i>',
                'label_html' => true,
                'required' => false,
                'label_attr' => [
                    'class' => 'switch-custom',
                ],
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
            ->addEventListener(FormEvents::SUBMIT, [$this, 'setDossierFichierProjet'])
            ->addEventListener(FormEvents::SUBMIT, [$this, 'setProjetUrls'])
        ;

        if (ProductPrivilegeCheker::checkProductPrivilege($projet->getSociete(),ProductPrivileges::PLANIFICATION_PROJET_AVANCE)){
            $builder
                ->add('nbrDaysNotifTaskEcheance', NumberType::class, [
                    'label' => $this->translator->trans('projet.nbrDaysNotifTaskEcheance.label') . ' <i class="fa fa-question-circle" title="'. $this->translator->trans('projet.nbrDaysNotifTaskEcheance.label.help') .'"></i>',
                    'label_html' => true,
                    'empty_data' => 3,
                    'required' => false,
                ]);
        }
    }

    public function setDossierFichierProjet(SubmitEvent $event)
    {
        $projet = $event->getData();

        foreach ($projet->getDossierFichierProjets() as $dossierFichierProjet) {
            if (null !== $dossierFichierProjet->getId()) {
                continue;
            }

            if (null === $dossierFichierProjet->getNom()) {
                $projet->removeDossierFichierProjet($dossierFichierProjet);
                continue;
            }

            $dossierFichierProjet->setDefaultFolderName();
        }
    }

    public function setProjetUrls(SubmitEvent $event)
    {
        $projet = $event->getData();

        foreach ($projet->getProjetUrls() as $projetUrl) {
            if (null === $projetUrl->getUrl()) {
                $projet->removeProjetUrl($projetUrl);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
        ]);
    }
}
