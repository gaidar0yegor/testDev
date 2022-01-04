<?php

namespace App\Form;

use App\Entity\FaitMarquant;
use App\Form\Custom\DatePickerType;
use App\Form\Custom\FichierProjetsType;
use App\MultiSociete\UserContext;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;

class FaitMarquantType extends AbstractType
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    private function getSendedToEmailsChoices(FaitMarquant $faitMarquant)
    {
        $sendedToEmailsChoices = [];

        foreach ($faitMarquant->getProjet()->getSociete()->getSocieteUsers() as $societeUser){
            $sendedToEmailsChoices["{$societeUser->getUser()->getShortname()} ({$societeUser->getUser()->getEmail()})"] = $societeUser->getUser()->getEmail();
        }
        foreach ($faitMarquant->getProjet()->getProjetObservateurExternes() as $observateurExterne){
            $email = $observateurExterne->getUser() ? $observateurExterne->getUser()->getEmail() : $observateurExterne->getInvitationEmail();
            $sendedToEmailsChoices[($observateurExterne->getUser() ? "{$observateurExterne->getUser()->getShortname()} ({$email})" : $email)] = $email;
        }

        if (count($faitMarquant->getSendedToEmails())){
            foreach ($faitMarquant->getSendedToEmails() as $sendedToEmail){
                if (!in_array($sendedToEmail,$sendedToEmailsChoices)){
                    $sendedToEmailsChoices[$sendedToEmail] = $sendedToEmail;
                }
            }
        }

        return array_unique($sendedToEmailsChoices);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sendedToEmailsChoices = $this->getSendedToEmailsChoices($builder->getData());

        $builder
            ->add('titre', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'projet.titre'
                ],
            ])
            ->add('description', CKEditorType:: class, [
                'label' => false,
                'required' => true
            ])
            ->add('fichierProjets', FichierProjetsType::class, [
                'projet' => $builder->getData()->getProjet(),
                'entry_options' => array('projet' => $builder->getData()->getProjet()),
                'label' => false,
            ])
            ->add('date', DatePickerType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'text-center date-picker',
                    'placeholder' => 'projet.date',
                ],
            ])
            ->add('sendedToEmails', ChoiceType::class, [
                'label' => false,
                'multiple'    => true,
                'expanded' 	  => false,
                'required' 	  => false,
                'attr' => [
                    'class' => 'select-2 select2-with-add form-control',
                    'data-placeholder' => 'Sélectionner des destinataires ...'
                ],
                'choices' => $sendedToEmailsChoices,
                'help' => 'faitMarquant.sendedToSocieteUsers.text.help',
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'updateChoices'])
            ->addEventListener(FormEvents::SUBMIT, [$this, 'setFichierProjetFaitMarquant'])
        ;
        $builder->get('sendedToEmails')->resetViewTransformers();
        $builder->get('sendedToEmails')->resetModelTransformers();
    }

    public function updateChoices(PreSubmitEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (isset($data['sendedToEmails'])){
            $form->add('sendedToEmails', ChoiceType::class, [
                'multiple' => true,
                'expanded' => false,
                'choices' => array_unique(array_merge($this->getSendedToEmailsChoices($form->getData()), array_combine($data['sendedToEmails'], $data['sendedToEmails'])))
            ]);
        }
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
                ->setUploadedBy($this->userContext->getSocieteUser())
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FaitMarquant::class
        ]);
    }
}
