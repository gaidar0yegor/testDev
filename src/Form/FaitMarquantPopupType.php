<?php

namespace App\Form;

use App\Entity\FaitMarquant;
use App\Entity\ProjetPlanningTask;
use App\Form\Custom\DatePickerType;
use App\Form\Custom\FichierProjetsType;
use App\MultiSociete\UserContext;
use App\Repository\ProjetPlanningTaskRepository;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\ProductPrivilegeCheker;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class FaitMarquantPopupType extends AbstractType
{
    private UserContext $userContext;
    private TranslatorInterface $translator;

    public function __construct(UserContext $userContext, TranslatorInterface $translator)
    {
        $this->userContext = $userContext;
        $this->translator = $translator;
    }

    private function getSendedToEmailsChoices(FaitMarquant $faitMarquant)
    {
        $sendedToEmailsChoices = [];

        foreach ($faitMarquant->getProjet()->getSociete()->getSocieteUsers() as $societeUser){
            $sendedToEmailsChoices[$societeUser->getUser()->getFullnameOrEmail()] = $societeUser->getUser()->getEmail();
        }
        foreach ($faitMarquant->getProjet()->getProjetObservateurExternes() as $observateurExterne){
            $email = $observateurExterne->getUser() ? $observateurExterne->getUser()->getEmail() : $observateurExterne->getInvitationEmail();
            $sendedToEmailsChoices[$observateurExterne->getUser() ? $observateurExterne->getUser()->getFullnameOrEmail() : $email] = $email;
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

        $hasPrivilegeFmDate = ProductPrivilegeCheker::checkProductPrivilege(
            $builder->getData()->getProjet()->getSociete(),
            ProductPrivileges::FAIT_MARQUANT_DATE
        );

        $hasPrivilegeFmSendMail = ProductPrivilegeCheker::checkProductPrivilege(
            $builder->getData()->getProjet()->getSociete(),
            ProductPrivileges::FAIT_MARQUANT_SEND_MAIL
        );

        $builder
            ->add('titre', null, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'input-sm',
                    'placeholder' => 'projet.titre',
                    'autocomplete' => 'off'
                ],
                'constraints'=>[
                    new NotBlank(),
                ],
            ])
            ->add('description', CKEditorType:: class, [
                'label' => false,
                'required' => true,
                'config_name' => 'without_options',
                'config' => [
                    'placeholder' => $this->translator->trans('projet.description')
                ],
                'attr' => [
                    'class' => 'ckeditor-instance',
                ],
                'constraints'=>[
                    new NotBlank(),
                ]
            ])
            ->add('date', DatePickerType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'input-sm text-center date-picker',
                    'placeholder' => 'projet.date',
                    'title' => !$hasPrivilegeFmDate ? $this->translator->trans('product_privilege_no_dispo') : false,
                ],
                'disabled' => !$hasPrivilegeFmDate,
                'constraints'=>[
                    new NotBlank(),
                ],
            ])
            ->add('fichierProjets', FichierProjetsType::class, [
                'projet' => $builder->getData()->getProjet(),
                'entry_options' => array('projet' => $builder->getData()->getProjet()),
                'label' => false,
                'attr' => [
                    'class' => 'no-searchBar no-exportBtn'
                ],
            ])
            ->add('geolocalisation', null, [
                'label' => 'projet.geolocalisation',
            ])
            ->add('projetPlanningTask', EntityType::class, [
                'label' => 'Lots du planning',
                'class' => ProjetPlanningTask::class,
                'query_builder' => function (ProjetPlanningTaskRepository $repository) use ($builder) {
                    $projet = $builder->getData()->getProjet();

                    return $repository
                        ->createQueryBuilder('ppt')
                        ->join('ppt.projetPlanning', 'pp', 'WITH', 'ppt.projetPlanning = pp')
                        ->where('pp.projet = :projet')
                        ->andWhere('ppt.parentTask IS NULL')
                        ->setParameter('projet', $projet)
                        ;
                },
                'choice_label' => 'text',
                'required' 	  => false,
                'placeholder' => 'Lier ce fait marquant à un lot du planning ...',
                'attr' => [
                    'class' => 'select-2 form-control w-100',
                ],
            ])
            ->add('sendedToEmails', ChoiceType::class, [
                'label' => 'Envoyer immédiatement par e-mail en interne',
                'multiple'    => true,
                'expanded' 	  => false,
                'required' 	  => false,
                'attr' => [
                    'class' => 'select-2 form-control w-100',
                    'title' => !$hasPrivilegeFmSendMail ? $this->translator->trans('product_privilege_no_dispo') : false,
                ],
                'disabled' => !$hasPrivilegeFmSendMail,
                'choices' => $sendedToEmailsChoices,
            ])
            ->add('extraSendedToEmails', TextType::class, [
                'label' => 'Partager par e-mail en externe',
                'required' 	  => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control w-100',
                    'title' => !$hasPrivilegeFmSendMail ? $this->translator->trans('product_privilege_no_dispo') : false,
                ],
                'disabled' => !$hasPrivilegeFmSendMail,
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

            $fichierProjet->getFichier()->setDateUpload($faitMarquant->getDate());

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
            'data_class' => FaitMarquant::class,
            'attr' => [
                'id' => 'fait_marquant_popup_form'
            ]
        ]);
    }
}
