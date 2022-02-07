<?php

namespace App\Form;

use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use App\Repository\SocieteUserRepository;
use App\SocieteProduct\Product\ProductPrivileges;
use App\SocieteProduct\ProductPrivilegeCheker;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class SocieteUserSuperiorType extends AbstractType
{
    private UserContext $userContext;
    private TranslatorInterface $translator;

    public function __construct(UserContext $userContext, TranslatorInterface $translator)
    {
        $this->userContext = $userContext;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $hasPrivilegeHierarchicalSuperior = ProductPrivilegeCheker::checkProductPrivilege(
            $builder->getData()->getSociete(),
            ProductPrivileges::SOCIETE_HIERARCHICAL_SUPERIOR
        );

        $builder
            ->add('mySuperior', EntityType::class, [
                'label' => false,
                'class' => SocieteUser::class,
                'required' => false,
                'query_builder' => function (SocieteUserRepository $repository) {
                    $societeUser = $this->userContext->getSocieteUser();

                    return $repository
                        ->whereSociete($societeUser->getSociete())
                        ->andWhere('societeUser.invitationToken is null')
                        ->andWhere('societeUser != :me')
                        ->setParameter('me', $this->userContext->getSocieteUser())
                        ->andWhere('societeUser.enabled = true');
                },
                'choice_label' => function (SocieteUser $societeUser) {
                    return $societeUser->getUser()->getFullname();
                },
                'placeholder' => 'Sélectionnez votre supérieur (N+1)',
                'attr' => [
                    'class' => 'select-2 form-control',
                    'title' => !$hasPrivilegeHierarchicalSuperior ? $this->translator->trans('product_privilege_no_dispo') : false,
                ],
                'disabled' => !$hasPrivilegeHierarchicalSuperior
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'update',
                'attr' => [
                    'class' => 'btn btn-primary',
                    'title' => !$hasPrivilegeHierarchicalSuperior ? $this->translator->trans('product_privilege_no_dispo') : false,
                ],
                'disabled' => !$hasPrivilegeHierarchicalSuperior
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SocieteUser::class,
        ]);
    }
}
