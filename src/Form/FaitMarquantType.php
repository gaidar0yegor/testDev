<?php

namespace App\Form;

use App\Entity\FaitMarquant;
use App\Form\Custom\DatePickerType;
use App\Form\Custom\FichierProjetsType;
use App\MultiSociete\UserContext;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
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

        if ($faitMarquant->getSendedToEmails() && count($faitMarquant->getSendedToEmails())){
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
                    'class' => 'select-2 form-control',
                    'data-placeholder' => 'Sélectionner des adresses e-mail ...'
                ],
                'choices' => $sendedToEmailsChoices,
            ])
            ->add('extraSendedToEmails', TextType::class, [
                'label' => false,
                'required' 	  => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ajouter des nouvelles adresses e-mail séparées par un point-virgule " ; "'
                ],
            ])
            ->addEventListener(FormEvents::SUBMIT, [$this, 'setFichierProjetFaitMarquant'])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'addExtraSendedToEmails'])
        ;
        $builder->get('extraSendedToEmails')
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'verifExtraSendedToEmails']);
    }

    public function verifExtraSendedToEmails(PreSubmitEvent $event)
    {
        $form = $event->getForm();
        $extraSendedToEmails = $event->getData();
        if ($extraSendedToEmails){
            $extraSendedToEmails = explode(';', $extraSendedToEmails);
            foreach ($extraSendedToEmails as $email){
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $form->addError(new FormError('Des adresses e-mail invalides.'));
                    break;
                }
            }
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

    public function addExtraSendedToEmails(PostSubmitEvent $event)
    {
        $extraSendedToEmails = $event->getForm()->get('extraSendedToEmails')->getData();
        $faitMarquant = $event->getData();
        if ($extraSendedToEmails){
            $extraSendedToEmails = explode(';',$extraSendedToEmails);
            $sendedToEmails = $faitMarquant->getSendedToEmails();
            $sendedToEmails = array_unique(array_merge($sendedToEmails, $extraSendedToEmails));
            $faitMarquant->setSendedToEmails($sendedToEmails);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FaitMarquant::class
        ]);
    }
}
