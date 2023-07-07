<?php

namespace App\Form;

use App\Entity\DashboardConsolide;
use App\Entity\SocieteUser;
use App\MultiSociete\UserContext;
use App\Repository\SocieteUserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DashboardConsolideType extends AbstractType
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'dashboard_consolide.titre',
                'required' 	=> true,
            ])
            ->add('societeUsers', EntityType::class, [
                'label' => 'dashboard_consolide.societes',
                'class' => SocieteUser::class,
                'query_builder' => function (SocieteUserRepository $repository) {
                    return $repository
                        ->createQueryBuilder('societeUser')
                        ->where('societeUser.user = :me')
                        ->andWhere('societeUser.enabled = true')
                        ->setParameter('me', $this->userContext->getUser());
                },
                'choice_label' => function (SocieteUser $societeUser) {
                    return $societeUser->getSociete()->getRaisonSociale();
                },
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DashboardConsolide::class,
        ]);
    }
}
