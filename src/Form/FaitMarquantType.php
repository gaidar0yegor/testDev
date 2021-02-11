<?php

namespace App\Form;

use App\Entity\FaitMarquant;
use App\Form\Custom\FichierProjetsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FaitMarquantType extends AbstractType
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', null, [
                'attr' => ['class' => 'form-control-lg'],
            ])
            ->add('description', TextareaType:: class, [
                'attr' => [
                    'class' => 'text-justify',
                    'rows' => 9
                ]
            ])
            ->add('fichierProjets', FichierProjetsType::class, [
                'projet' => $builder->getData()->getProjet(),
                'label' => 'Fichiers joints',
            ])
            ->addEventListener(FormEvents::SUBMIT, [$this, 'setFichierProjetFaitMarquant'])
        ;
    }

    public function setFichierProjetFaitMarquant(SubmitEvent $event)
    {
        $faitMarquant = $event->getData();

        foreach ($faitMarquant->getFichierProjets() as $fichierProjet) {
            if (null !== $fichierProjet->getId()) {
                continue;
            }

            $fichierProjet
                ->setProjet($faitMarquant->getProjet())
                ->setFaitMarquant($faitMarquant)
                ->setUploadedBy($this->tokenStorage->getToken()->getUser())
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FaitMarquant::class,
        ]);
    }
}
