<?php

namespace App\Form;

use App\Entity\Rappel;
use App\Entity\Societe;
use App\Form\Custom\DatePickerType;
use App\MultiSociete\UserContext;
use App\Repository\SocieteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;

class RappelType extends AbstractType
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'required' => true,
                'constraints'=>[
                    new NotBlank(),
                ]
            ])
            ->add('description', TextareaType:: class, [
                'label' => 'Détails',
                'label_html' => true,
                'required' => false,
                'constraints'=>[
                    new Assert\Length([
                        'max' => 150
                    ]),
                ]
            ])
            ->add('reminderAt', DatePickerType::class, [
                'label' => 'Date',
                'attr' => [
                    'class' => 'text-center datetime-picker',
                ],
                'format' => 'dd MMMM yyyy HH:mm',
                'required' => true,
                'constraints'=>[
                    new NotBlank(),
                ]
            ])
            ->add('societe', EntityType::class, [
                'label' => 'Société',
                'class' => Societe::class,
                'query_builder' => function (SocieteRepository $repository) {
                    return $repository
                        ->createQueryBuilder('societe')
                        ->leftJoin('societe.societeUsers', 'societeUser')
                        ->where('societeUser.user = :user')
                        ->andWhere('societe.enabled = true')
                        ->andWhere('societeUser.enabled = true')
                        ->setParameter('user', $this->userContext->getUser())
                        ;
                },
                'choice_label' => 'raisonSociale',
                'required' 	  => false,
                'placeholder' 	  => 'Lier le rappel à une société',
                'attr' => [
                    'class' => 'select-2 form-control w-100',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-success',
                ],
            ])
          ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Rappel::class
        ]);
    }
}
