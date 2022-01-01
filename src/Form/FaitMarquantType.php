<?php

namespace App\Form;

use App\Entity\FaitMarquant;
use App\Form\Custom\DatePickerType;
use App\Form\Custom\FichierProjetsType;
use App\MultiSociete\UserContext;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\Length;

class FaitMarquantType extends AbstractType
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
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
            ->add('date', DateType::class, [
                'attr' => ['format' => 'yyyy-MM-dd'],
            ])
            ->add('date', DatePickerType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'text-center date-picker',
                    'placeholder' => 'projet.date',
                ],
            ])
            ->addEventListener(FormEvents::SUBMIT, [$this, 'setFichierProjetFaitMarquant'])
        ;
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
