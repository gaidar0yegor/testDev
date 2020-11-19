<?php

namespace App\Form\Custom;

use App\Entity\User;
use App\Exception\RdiException;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SameSocieteUserType extends AbstractType
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => User::class,
            'choice_label' => 'email',
            'query_builder' => function (UserRepository $repository) {
                $user = $this->tokenStorage->getToken()->getUser();

                if (!$user instanceof User) {
                    throw new RdiException(
                        'Cannot use SameSocieteUserType when no user is logged in,'
                        .' because needs to filter by society.'
                    );
                }

                return $repository->whereSociete($user->getSociete());
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'same_societe_user';
    }
}
