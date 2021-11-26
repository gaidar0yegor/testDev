<?php

namespace App\Form;

use App\Entity\Projet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class DateSuspendProjetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('suspendedAt', DateType::class, [
                'label' => 'Date de suspension du projet',
                'attr' => ['format' => 'yyyy-MM-dd'],
                'required' => true,
            ])
            ->addEventListener(FormEvents::SUBMIT, [$this, 'checkSuspendedDate'])
        ;
    }

    public function checkSuspendedDate(SubmitEvent $event)
    {
        $form = $event->getForm()->get('suspendedAt');

        if ($event->getData()->getSuspendedAt() > (new \DateTime())){
            $form->addError(new FormError("Cette valeur doit être inférieure ou égale à " . (new \DateTime())->format('d M Y')));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
        ]);
    }
}
