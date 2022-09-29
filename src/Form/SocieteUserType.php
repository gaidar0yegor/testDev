<?php

namespace App\Form;

use App\Entity\SocieteUser;
use App\Form\Custom\FoRoleCardChoiceType;
use App\Form\EventListener\CheckPeriodsDatesListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocieteUserType extends AbstractType
{
    private CheckPeriodsDatesListener $checkPeriodsDatesListener;

    public function __construct(CheckPeriodsDatesListener $checkPeriodsDatesListener)
    {
        $this->checkPeriodsDatesListener = $checkPeriodsDatesListener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('role', FoRoleCardChoiceType::class)
            ->add('heuresParJours', NumberType::class, [
                'label' => 'user.heuresParJours',
                'help' => 'user.heuresParJours.help',
                'required' => false,
                'attr' => [
                    'placeholder' => $this->getHeuresPlaceholder($builder),
                ],
            ])
            ->add('workStartTime', TextType::class, [
                'label' => 'user.workStartTime',
                'help' => 'user.workStartTime.help',
                'required' => false,
                'attr' => [
                    'placeholder' => $this->getWorkStartTimePlaceholder($builder),
                ],
            ])
            ->add('workEndTime', TextType::class, [
                'label' => 'user.workEndTime',
                'help' => 'user.workEndTime.help',
                'required' => false,
                'attr' => [
                    'placeholder' => $this->getWorkEndTimePlaceholder($builder),
                ],
            ])
            ->add('coutEtp', NumberType::class, [
                'label' => "cost_moyen_worked_hours",
                'help' => 'user.coutEtp.help',
                'required' => false,
                'attr' => [
                    'placeholder' => $this->getCoutEtpPlaceholder($builder),
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
            ->addEventSubscriber($this->checkPeriodsDatesListener)
            ->addEventListener(FormEvents::SUBMIT, [$this, 'verifyHeuresParJours'])
        ;
    }

    public function verifyHeuresParJours(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $heuresParJours = $data->getHeuresParJours() !== null ? $data->getHeuresParJours() : $data->getSociete()->getHeuresParJours();
        $workStartTime = $data->getWorkStartTime() !== null ? $data->getWorkStartTime() : $data->getSociete()->getWorkStartTime();
        $workEndTime = $data->getWorkEndTime() !== null ? $data->getWorkEndTime() : $data->getSociete()->getWorkEndTime();

        $workStartTime = new \DateTime($workStartTime . ':00');
        $workEndTime = new \DateTime($workEndTime . ':00');
        $diff = $workStartTime->diff($workEndTime)->h;

        if ($heuresParJours > $diff){
            $form->addError(new FormError("Le nombre d'heures de travail n'est pas compatible avec la plage horaire indiquée."));
        }
    }

    private function getHeuresPlaceholder(FormBuilderInterface $builder): string
    {
        $defaultHeuresParJour = $builder->getData()->getSociete()->getHeuresParJours();

        if (null === $defaultHeuresParJour) {
            return '';
        }

        return sprintf('Par défaut : %.2f', $defaultHeuresParJour);
    }

    private function getWorkStartTimePlaceholder(FormBuilderInterface $builder): string
    {
        $defaultWorkStartTime = $builder->getData()->getSociete()->getWorkStartTime();

        if (null === $defaultWorkStartTime) {
            return '';
        }

        return sprintf('Par défaut : %s', $defaultWorkStartTime);
    }

    private function getWorkEndTimePlaceholder(FormBuilderInterface $builder): string
    {
        $defaultWorkEndTime = $builder->getData()->getSociete()->getWorkEndTime();

        if (null === $defaultWorkEndTime) {
            return '';
        }

        return sprintf('Par défaut : %s', $defaultWorkEndTime);
    }

    private function getCoutEtpPlaceholder(FormBuilderInterface $builder): string
    {
        $defaultCoutEtp = $builder->getData()->getSociete()->getCoutEtp();

        if (null === $defaultCoutEtp) {
            return '';
        }

        return sprintf('Par défaut : %.2f', $defaultCoutEtp);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SocieteUser::class,
        ]);
    }
}
