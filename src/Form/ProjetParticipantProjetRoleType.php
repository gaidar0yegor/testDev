<?php

namespace App\Form;

use App\Entity\Projet;
use App\Entity\ProjetParticipant;
use App\Form\Custom\RoleProjetCardChoiceType;
use App\MultiSociete\UserContext;
use App\Repository\ProjetRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Affiche un formulaire pour modifier le rôle d'un participant sur un projet donné.
 */
class ProjetParticipantProjetRoleType extends AbstractType
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('projet', EntityType::class, [
                'class' => Projet::class,
                'query_builder' => function (ProjetRepository $repository) {
                    $societe = $this->userContext->getSocieteUser()->getSociete();

                    return $repository
                        ->createQueryBuilder('projet')
                        ->where('projet.societe = :societe')
                        ->setParameter('societe', $societe)
                    ;
                },
                'choice_label' => function (?Projet $projet) {
                    return null === $projet ? '' : $projet->getAcronyme();
                },
            ])
            ->add('role', RoleProjetCardChoiceType::class, [
                'card_choice_size' => 'small',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjetParticipant::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'projet_participant_projet_role';
    }
}
