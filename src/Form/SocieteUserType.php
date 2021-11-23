<?php

namespace App\Form;

use App\Entity\SocieteUser;
use App\Entity\SocieteUserPeriod;
use App\Form\Custom\DatePickerType;
use App\Form\Custom\FoRoleCardChoiceType;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocieteUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('role',  FoRoleCardChoiceType::class)
        ->add('heuresParJours', NumberType::class, [
            'help' => 'Pour cet utilisateur uniquement, vous pouvez remplacer ici le nombre d\'heure défini globalement au niveau de la société.',
            'required' => false,
            'attr' => [
                'placeholder' => $this->getHeuresPlaceholder($builder),
            ],
        ])
        ->add('societeUserPeriods', CollectionType::class, [
            'label' => false,
            'entry_type' => SocieteUserPeriodType::class,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
        ])
        ->addEventListener(FormEvents::SUBMIT, [$this, 'checkDates'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SocieteUser::class,
        ]);
    }

    private function getHeuresPlaceholder(FormBuilderInterface $builder): string
    {
        $defaultHeuresParJour = $builder->getData()->getSociete()->getHeuresParJours();

        if (null === $defaultHeuresParJour) {
            return '';
        }

        return sprintf('Par défaut : %.2f', $defaultHeuresParJour);
    }

    public function checkDates(SubmitEvent $event): void
    {
        $forms = $event->getForm()->get('societeUserPeriods');

        $dateLeaveLast = null;
        foreach ($forms as $societeUserPeriodForm) {
            $dateEntry = $societeUserPeriodForm->getData()->getDateEntry();
            $dateLeave = $societeUserPeriodForm->getData()->getDateLeave();
            if (
                $dateEntry && $dateLeave && $societeUserPeriodForm->getData()->getDateEntry() > $societeUserPeriodForm->getData()->getDateLeave() ||
                $dateEntry && $dateLeaveLast && $dateEntry < $dateLeaveLast
            ) {
                $societeUserPeriodForm->addError(new FormError("Les dates d'entrée / sortie ne sont pas cohérentes !"));
            }
            $dateLeaveLast = $dateLeave;
        }
    }
}
