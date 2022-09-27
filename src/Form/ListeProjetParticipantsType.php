<?php

namespace App\Form;

use App\Entity\Projet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListeProjetParticipantsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('projetParticipants', CollectionType::class, [
                'entry_type' => ProjetParticipantType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->addEventListener(FormEvents::SUBMIT, [$this, 'putAllParticipantOnProjet'])
        ;
    }

    /**
     * Affecte tous les participants, notamment les nouveaux, sur ce projet.
     */
    public function putAllParticipantOnProjet(FormEvent $event)
    {
        $projet = $event->getData();

        foreach ($projet->getProjetParticipants() as $projetParticipant) {
            $projetParticipant->setProjet($projet);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
        ]);
    }
}
