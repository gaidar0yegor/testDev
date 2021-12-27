<?php

namespace App\Form;

use App\Entity\DossierFichierProjet;
use App\Entity\FichierProjet;
use App\Service\FichierProjetService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FichierProjetModifierType extends AbstractType
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
            ->add('fichier', FichierModifierType::class )
            ->add('accessesChoices', ChoiceType::class, [
                'label' => 'Droits de visibilité',
                'required'    => false,
                'multiple'    => true,
                'expanded' 	  => false,
                'attr' => [
                    'class' => 'select-2',
                    'data-placeholder' => 'Droits de visibilité (Par défaut : Tous)'
                ],
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
