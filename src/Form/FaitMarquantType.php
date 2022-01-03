<?php

namespace App\Form;

use App\Entity\FaitMarquant;
use App\Entity\SocieteUser;
use App\Form\Custom\DatePickerType;
use App\Form\Custom\FichierProjetsType;
use App\MultiSociete\UserContext;
use App\Repository\SocieteUserRepository;
use App\Security\Role\RoleSociete;
use App\Service\Invitator;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;

class FaitMarquantType extends AbstractType
{
    private UserContext $userContext;
    private Invitator $invitator;

    public function __construct(UserContext $userContext, Invitator $invitator)
    {
        $this->userContext = $userContext;
        $this->invitator = $invitator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'projet.titre'
                ],
            ])
            ->add('description', CKEditorType:: class, [
                'label' => false,
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
            ->add('sendedToSocieteUsers', EntityType::class, [
                'label' => false,
                'class' => SocieteUser::class,
                'multiple'    => true,
                'expanded' 	  => false,
                'required' 	  => false,
                'attr' => [
                    'class' => 'select-2 select2-with-add form-control',
                    'data-placeholder' => 'Sélectionner des destinataires ...'
                ],
                'query_builder' => function (SocieteUserRepository $repository) {
                    return $repository
                        ->whereSociete($this->userContext->getSocieteUser()->getSociete())
                        ->andWhere('societeUser != :me')
                        ->setParameter('me', $this->userContext->getSocieteUser())
                        ->andWhere('societeUser.enabled = true')
                        ;
                },
                'choice_label' => function (SocieteUser $societeUser) {
                    return "{$societeUser->getUser()->getShortname()} ({$societeUser->getUser()->getEmail()})";
                },
                'choice_value' => function (SocieteUser $societeUser) {
                    return $societeUser->getUser()->getEmail();
                },
                'help' => 'faitMarquant.sendedToSocieteUsers.text.help',
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'createSendedToSocieteUsers'])
            ->addEventListener(FormEvents::SUBMIT, [$this, 'setFichierProjetFaitMarquant'])
        ;
    }

    public function createSendedToSocieteUsers(PreSubmitEvent $event)
    {
        $data = $event->getData();
        if (isset($data['sendedToSocieteUsers'])){
            foreach ($data['sendedToSocieteUsers'] as $key => $email){
                if ($email[0] === '@'){
                    $email = ltrim($email, $email[0]);
                    $invite = $this->invitator->sendAutomaticInvitation($this->userContext->getSocieteUser(),RoleSociete::USER, $email);
                    $data['sendedToSocieteUsers'][$key] = $invite->getInvitationEmail();
                }
            }
            $event->setData($data);
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
