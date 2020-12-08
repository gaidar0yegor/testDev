<?php

namespace App\Form;

use App\Entity\Projet;
use App\Form\Custom\FichierProjetsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProjetFichierProjetsType extends AbstractType
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fichierProjets', FichierProjetsType::class, [
                'projet' => $builder->getData(),
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
                ->setUploadedBy($this->tokenStorage->getToken()->getUser())
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
