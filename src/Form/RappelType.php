<?php

namespace App\Form;

use App\Entity\Rappel;
use App\Entity\Societe;
use App\Form\Custom\DatePickerType;
use App\MultiSociete\UserContext;
use App\Repository\SocieteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
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
        $rappel = $builder->getData();

        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('description', TextareaType:: class, [
                'label' => 'Détails',
                'label_html' => true,
                'required' => false,
                'attr' => [
                    'class' => 'count-with-max',
                    'maxlength' => '200',
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 200
                    ]),
                ],
                'help' => '<i class="fa fa-question-circle"></i> Le nombre maximum de caractères autorisé est 200',
                'help_html' => true,
            ])
            ->add('rappelDate', DatePickerType::class, [
                'label' => 'Date',
                'attr' => [
                    'class' => 'text-center date-picker',
                    'autocomplete' => 'off'
                ],
                'format' => 'dd MMMM yyyy',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('reminderTimeAt', TimeType::class, [
                'label' => 'Heure',
                'mapped' => false,
                'input' => 'datetime',
                'widget' => 'choice',
                'required' => true,
                'data' => $rappel->getRappelDate() ?? $rappel->getRappelDate(),
                'attr' => [
                    'class' => 'form-time'
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('minutesToReminde', ChoiceType::class, [
                'label' => 'Alerte',
                'choices' => [
                    'AT_TIME' => 0,
                    '10_MINUTES_BEFORE' => 10,
                    '30_MINUTES_BEFORE' => 30,
                    '1_HOUR_BEFORE' => 60,
                    '6_HOURS_BEFORE' => 60 * 6,
                    '12_HOURS_BEFORE' => 60 * 12,
                    '1_DAY_BEFORE' => 60 * 24,
                ],
                'required' => true,
                'constraints' => [
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
                        ->setParameter('user', $this->userContext->getUser());
                },
                'choice_label' => 'raisonSociale',
                'required' => false,
                'placeholder' => 'Lier le rappel à une société',
                'attr' => [
                    'class' => 'select-2 form-control w-100',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'submit',
                'attr' => [
                    'class' => 'btn-success',
                ],
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'setReminderAtValue']);;
    }

    public function setReminderAtValue(PostSubmitEvent $event)
    {
        $form = $event->getForm();

        $rappel = $form->getData();
        $rappelDate = $form->get('rappelDate')->getData();
        $reminderTime = $form->get('reminderTimeAt')->getData();

        $rappelDate->setTime((int)$reminderTime->format('H'), (int)$reminderTime->format('i'));
        $reminderAt = (clone $rappelDate)->modify("-" . $form->get('minutesToReminde')->getData() . " minutes");

        $rappel->setRappelDate($rappelDate);
        $rappel->setReminderAt($reminderAt);
        $rappel->setIsReminded($reminderAt <= (new \DateTime()));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Rappel::class
        ]);
    }
}
