<?php

namespace App\Form;

use App\Entity\DossierFichierProjet;
use App\Entity\FichierProjet;
use App\Service\FichierProjetService;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\ProductPrivilegeCheker;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class FichierProjetModifierType extends AbstractType
{
    private FichierProjetService $fichierProjetService;
    private TranslatorInterface $translator;

    public function __construct(FichierProjetService $fichierProjetService, TranslatorInterface $translator)
    {
        $this->fichierProjetService = $fichierProjetService;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fichierProjet = $options['data'];
        $projet = $fichierProjet->getProjet();

        $hasPrivilegeFichierProjetAccesses = ProductPrivilegeCheker::checkProductPrivilege(
            $projet->getSociete(),
            ProductPrivileges::FICHIER_PROJET_ACCESSES
        );

        $builder
            ->add('fichier', FichierModifierType::class )
            ->add('accessesChoices', ChoiceType::class, [
                'label' => 'Droits de visibilité',
                'required'    => false,
                'multiple'    => true,
                'expanded' 	  => false,
                'attr' => [
                    'class' => 'select-2',
                    'data-placeholder' => 'Droits de visibilité (Par défaut : Tous)',
                    'title' => !$hasPrivilegeFichierProjetAccesses ? $this->translator->trans('product_privilege_no_dispo') : false,
                ],
                'disabled' => !$hasPrivilegeFichierProjetAccesses,
                'choices' => FichierProjetService::getChoicesForAddFileAccess($projet),
            ])
            ->add('dossierFichierProjet', EntityType::class, [
                'label' => 'Sélectionner un dossier',
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
                'required' => false,
                'attr' => [
                    'class' => 'select-2 form-control',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FichierProjet::class,
        ]);
    }
}
