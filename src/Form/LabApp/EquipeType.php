<?php

namespace App\Form\LabApp;

use App\Entity\LabApp\Equipe;
use App\Form\Custom\ListEtudesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $equipe = $builder->getData();

        $builder
            ->add('name', TextType::class, [
                'label' => 'equipe.name'
            ])
            ->add('etudes', CollectionType::class, [
                'entry_type' => ListEtudesType::class,
                'entry_options' => [
                    'choice_attr' => function($etude) use ($equipe) {
                        return $etude->getEquipe() !== null && $etude->getEquipe() !== $equipe ? ['disabled' =>  'disabled'] : [];
                    }
                ],
                'label' => 'menu.equipe.etudes',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->addEventListener(FormEvents::SUBMIT, [$this, 'putAllEtudeOnEquipe'])
        ;
    }

    /**
     * Affecte toutes les études, notamment les nouveaux, sur cette équipe.
     */
    public function putAllEtudeOnEquipe(FormEvent $event)
    {
        $equipe = $event->getData();

        foreach ($equipe->getEtudes() as $etude) {
            $etude->setEquipe($equipe);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Equipe::class,
        ]);
    }
}
