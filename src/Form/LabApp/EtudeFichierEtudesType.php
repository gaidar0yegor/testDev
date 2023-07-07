<?php

namespace App\Form\LabApp;

use App\Entity\LabApp\Etude;
use App\Form\Custom\FichierEtudesType;
use App\MultiSociete\UserContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EtudeFichierEtudesType extends AbstractType
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fichierEtudes', FichierEtudesType::class, [
                'etude' => $builder->getData(),
                'entry_options' => array('etude' => $builder->getData()),
                'label' => false,
            ])
            ->addEventListener(FormEvents::SUBMIT, [$this, 'setFichierEtude'])
        ;
    }

    public function setFichierEtude(SubmitEvent $event)
    {
        $etude = $event->getData();

        foreach ($etude->getFichierEtudes() as $fichierEtude) {
            if (null !== $fichierEtude->getId()) {
                continue;
            }

            $fichierEtude
                ->setUploadedBy($this->userContext->getUserBook())
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Etude::class,
        ]);
    }
}
