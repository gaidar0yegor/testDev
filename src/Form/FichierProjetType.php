<?php

namespace App\Form;

use App\Entity\FichierProjet;
use App\File\FileHandler\ProjectFileHandler;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;

class FichierProjetType extends AbstractType
{
    private ProjectFileHandler $projectFileHandler;

    public function __construct(ProjectFileHandler $projectFileHandler)
    {
        $this->projectFileHandler = $projectFileHandler;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fichier', FichierType::class, [
                'label' => false,
                'fileHandler' => $this->projectFileHandler,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FichierProjet::class,
        ]);
    }
}
