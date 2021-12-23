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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FichierProjet::class,
        ]);
    }
}
