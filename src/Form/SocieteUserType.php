<?php

namespace App\Form;

use App\Entity\SocieteUser;
use App\Form\Custom\FoRoleCardChoiceType;
use App\Form\EventListener\CheckPeriodsDatesListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
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
            ->addEventSubscriber($this->checkPeriodsDatesListener);
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

    private function getCoutEtpPlaceholder(FormBuilderInterface $builder): string
    {
        $defaultCoutEtp = $builder->getData()->getSociete()->getCoutEtp();

        if (null === $defaultCoutEtp) {
            return '';
        }

        return sprintf('Par défaut : %.2f', $defaultCoutEtp);
    }
}
