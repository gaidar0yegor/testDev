<?php

namespace App\Form\Custom;

use App\Entity\LabApp\Etude;
use App\EtudeResourceInterface;
use App\Form\LabApp\FichierEtudeType;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FichierEtudesType extends AbstractType
{
    private AuthorizationCheckerInterface $authChecker;

    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::SUBMIT, [$this, 'checkGrantedToAdd'])
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['etude'] = $options['etude'];
    }

    public function checkGrantedToAdd(SubmitEvent $event): void
    {
        $etude = $event->getForm()->getConfig()->getOption('etude');

        if ($this->authChecker->isGranted(EtudeResourceInterface::CREATE, $etude)) {
            return;
        }

        foreach ($event->getData() as $fichierEtude) {
            if (null !== $fichierEtude->getId()) {
                continue;
            }

            throw new AccessDeniedException('Vous ne pouvez pas ajouter de fichiers sur cette étude.');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type' => FichierEtudeType::class,
            'prototype' => true,
            'allow_add' => true,
            'allow_delete' => false,
            'by_reference'  => false,
            'required' => false,
            'etude' => null,
        ]);
        $resolver->setDefined([
            'etude',
        ]);
        $resolver->setRequired([
            'etude',
        ]);
        $resolver->setAllowedTypes('etude', [
            Etude::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'fichier_etudes';
    }
}
