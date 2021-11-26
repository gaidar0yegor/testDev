<?php

namespace App\Form;

use App\Entity\FaitMarquant;
use App\Form\Custom\FichierProjetsType;
use App\Form\Custom\MarkdownWysiwygType;
use App\MultiSociete\UserContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
        $societe = $this->userContext->getSocieteUser()->getSociete();

        $descriptionOptions = [ 'attr' => [
            'class' => 'text-justify',
            'rows' => 9,
        ]];

        if ($societe->getFaitMarquantMaxDescIsblocking() && $societe->getFaitMarquantMaxDesc() !== -1){
            $descriptionOptions['attr']['maxlength'] = $societe->getFaitMarquantMaxDesc();
            $descriptionOptions['constraints'] = [
                new Length(['max' => $societe->getFaitMarquantMaxDescIsblocking() ? $societe->getFaitMarquantMaxDesc() - 1 : false])
            ];
        }

        $builder
            ->add('titre', null, [
                'attr' => ['class' => 'form-control-lg'],
            ])
            ->add('description', MarkdownWysiwygType:: class, $descriptionOptions)
            ->add('fichierProjets', FichierProjetsType::class, [
                'projet' => $builder->getData()->getProjet(),
                'label' => 'Fichiers joints',
            ])
            ->add('date', DateType::class, [
                'attr' => ['format' => 'yyyy-MM-dd'],
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
