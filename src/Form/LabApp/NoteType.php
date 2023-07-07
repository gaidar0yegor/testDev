<?php

namespace App\Form\LabApp;

use App\Entity\LabApp\Note;
use App\Form\Custom\DatePickerType;
use App\Form\Custom\FichierEtudesType;
use App\MultiSociete\UserContext;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class NoteType extends AbstractType
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
        $builder
            ->add('title', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'note.title'
                ],
            ])
            ->add('description', CKEditorType:: class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'ckeditor-instance',
                ],
                'constraints'=>[
                    new NotBlank(),
                ]
            ])
            ->add('date', DatePickerType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'text-center date-picker',
                    'placeholder' => 'note.date'
                ]
            ])
            ->add('readingName', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'note.readingName'
                ],
            ])
            ->add('author', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'note.author'
                ],
            ])
            ->add('reference', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'note.reference'
                ],
            ])
            ->add('fichierEtudes', FichierEtudesType::class, [
                'etude' => $builder->getData()->getEtude(),
                'entry_options' => array('etude' => $builder->getData()->getEtude()),
                'label' => false,
                'attr' => [
                    'class' => 'no-searchBar no-exportBtn'
                ],
            ])
            ->addEventListener(FormEvents::SUBMIT, [$this, 'setFichierEtudeNote'])
        ;
    }

    public function setFichierEtudeNote(SubmitEvent $event)
    {
        $note = $event->getData();

        foreach ($note->getFichierEtudes() as $fichierEtude) {
            if (null !== $fichierEtude->getId()) {
                continue;
            }

            $fichierEtude->getFichier()->setDateUpload($note->getDate());

            $fichierEtude
                ->setEtude($note->getEtude())
                ->setNote($note)
                ->setUploadedBy($this->userContext->getUserBook())
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Note::class
        ]);
    }
}
