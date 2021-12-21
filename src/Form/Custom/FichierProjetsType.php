<?php

namespace App\Form\Custom;

use App\Entity\Projet;
use App\Form\FichierProjetType;
use App\ProjetResourceInterface;
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

class FichierProjetsType extends AbstractType
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
        $view->vars['projet'] = $options['projet'];
    }

    public function checkGrantedToAdd(SubmitEvent $event): void
    {
        $projet = $event->getForm()->getConfig()->getOption('projet');

        if ($this->authChecker->isGranted(ProjetResourceInterface::CREATE, $projet)) {
            return;
        }

        foreach ($event->getData() as $fichierProjet) {
            if (null !== $fichierProjet->getId()) {
                continue;
            }

            throw new AccessDeniedException('Vous ne pouvez pas ajouter de fichiers sur ce projet.');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type' => FichierProjetType::class,
            'prototype' => true,
            'allow_add' => true,
            'allow_delete' => false,
            'by_reference'  => false,
            'required' => false,
            'projet' => null,
        ]);
        $resolver->setDefined([
            'projet',
        ]);
        $resolver->setRequired([
            'projet',
        ]);
        $resolver->setAllowedTypes('projet', [
            Projet::class,
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
        return 'fichier_projets';
    }
}
