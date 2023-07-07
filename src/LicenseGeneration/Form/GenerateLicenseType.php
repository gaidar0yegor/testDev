<?php

namespace App\LicenseGeneration\Form;

use App\Form\Custom\DatePickerType;
use App\License\DTO\License;
use App\SocieteProduct\Product\ProductPrivileges;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenerateLicenseType extends AbstractType
{
    private ProductPrivileges $productPrivileges;
    private EntityManagerInterface $em;

    public function __construct(ProductPrivileges $productPrivileges, EntityManagerInterface $em)
    {
        $this->productPrivileges = $productPrivileges;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('societe', SocieteType::class)
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => 168,
                ]
            ])
            ->add('expirationDate', DatePickerType::class)
            ->add('isTryLicense', CheckboxType::class,[
                'label' => "Est-elle une Offre d'essai ?",
                'required' => false
            ])
            ->add('quotas', QuotaType::class);

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'updateSocieteProduct']);
    }

    public function updateSocieteProduct(PostSubmitEvent $event)
    {
        $form = $event->getForm();
        $license = $form->getData();
        $societe = $license->getSociete();
        $societe->setProductKey($license->getProductKey());

        $this->em->persist($societe);
        $this->em->flush();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => License::class,
        ]);
    }
}
