<?php

namespace App\RegisterLabo\Form;

use App\Entity\LabApp\UserBook;
use App\Entity\User;
use App\Repository\LabApp\UserBookRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserBookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label' => 'Titre',
                'required' => $options['user_to_join_labo'] === null,
                'attr' => [
                    'class' => 'form-control-lg',
                ],
                'help' => 'Titre de votre cahier de laboratoire.',
            ])
        ;

        if ($options['user_to_join_labo'] instanceof User){
            $user = $options['user_to_join_labo'];
            $builder
                ->add('existedUserBook', EntityType::class, [
                    'label' => false,
                    'placeholder' => 'Sélectionner un cahier de vos cahiers de laboratoire ...',
                    'class' => UserBook::class,
                    'mapped' => false,
                    'required' => false,
                    'query_builder' => function (UserBookRepository $repository) use ($user) {
                        return $repository
                            ->createQueryBuilder('userBook')
                            ->where('userBook.user = :user')
                            ->andWhere('userBook.labo IS NULL')
                            ->setParameter('user', $user)
                            ;
                    },
                    'choice_label' => function (UserBook $userBook) {
                        return $userBook->getTitle();
                    },
                    'attr' => [
                        'class' => 'select-2 form-control',
                    ],
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserBook::class,
            'validation_groups' => ['registration'],
            'user_to_join_labo' => null
        ]);
    }
}
