<?php

namespace App\Form;

use App\Entity\Projet;
use App\Form\Custom\FichierProjetsType;
use App\MultiSociete\UserContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetFichierProjetsType extends AbstractType
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fichierProjets', FichierProjetsType::class, [
                'projet' => $builder->getData(),
                'entry_options' => array('projet' => $builder->getData()),
                'label' => false,
            ])
            ->addEventListener(FormEvents::SUBMIT, [$this, 'setFichierProjetFaitMarquant'])
        ;
    }

    public function setFichierProjetFaitMarquant(SubmitEvent $event)
    {
        $projet = $event->getData();

        foreach ($projet->getFichierProjets() as $fichierProjet) {
            if (null !== $fichierProjet->getId()) {
                continue;
            }

            $fichierProjet
                ->setUploadedBy($this->userContext->getSocieteUser())
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
        ]);
    }
}
