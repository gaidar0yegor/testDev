<?php

namespace App\Form;

use App\DTO\ProjetExportParameters;
use App\Entity\ProjetParticipant;
use App\Form\Custom\DatePickerType;
use App\MultiSociete\UserContext;
use App\Security\Role\RoleProjet;
use App\Security\Role\RoleSociete;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProjetExportType extends AbstractType
{
    private AuthorizationCheckerInterface $authChecker;

    private UserContext $userContext;

    private EntityManagerInterface $em;

    public function __construct(AuthorizationCheckerInterface $authChecker, UserContext $userContext, EntityManagerInterface $em)
    {
        $this->authChecker = $authChecker;
        $this->userContext = $userContext;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $projetExportParameters = $options['data'];
        $exportOptions = array_combine($projetExportParameters->getExportOptions(), $projetExportParameters->getExportOptions());

        $projetParticipant = $this->em->getRepository(ProjetParticipant::class)->findOneBy([
            'societeUser' => $this->userContext->getSocieteUser(),
            'projet' => $projetExportParameters->getProjet(),
        ]);

        if (!$this->authChecker->isGranted(RoleSociete::ADMIN)) {
            if (!$projetParticipant instanceof ProjetParticipant){
                throw new AccessDeniedException('Un problème est survenu !!');
            }

            if ($projetParticipant->getRole() === RoleProjet::OBSERVATEUR){
                unset($exportOptions[ProjetExportParameters::STATISTIQUES]);
                unset($exportOptions[ProjetExportParameters::PARTICIPANTS]);
            }
        }

        $builder
        ->add('dateDebut', DatePickerType::class, [
            'required' => false,
            'label' => 'Date de début de l\'export',
        ])
        ->add('dateFin', DatePickerType::class, [
            'required' => false,
            'label' => 'Date de fin de l\'export',
        ])
        ->add('exportOptions', ChoiceType::class, [
            'required' => false,
            'label' => 'Que souhaitez-vous exporter ?',
            'expanded' => true,
            'multiple' => true,
            'choices' => $exportOptions
        ])
        ->add('format', ChoiceType::class, [
            'choices' => [
                '.pdf' => 'pdf',
                'Page html' => 'html',
            ],
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => ProjetExportParameters::class
        ]);
    }
}
