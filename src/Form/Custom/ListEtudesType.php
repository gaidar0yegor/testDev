<?php

namespace App\Form\Custom;

use App\Entity\LabApp\Etude;
use App\Repository\LabApp\EtudeRepository;
use App\MultiSociete\UserContext;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Champ de formulaire qui sert à séléctionner une étude du même labo
 */
class ListEtudesType extends AbstractType
{
    private UserContext $userContext;

    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
            'class' => Etude::class,
            'choice_label' => function (Etude $choice): string {
                return $choice->getTitle() . " ({$choice->getAcronyme()})";
            },
            'query_builder' => function (EtudeRepository $repository) {
                $labo = $this->userContext->getUserBook()->getLabo();

                $qb = $repository->createQueryBuilder('etude');
                return $qb->join('etude.userBook','userBook')
                    ->andWhere('userBook.labo = :labo')
                    ->setParameter('labo', $labo)
                    ;
            },
            'attr' => [
                'class' => 'select-2 form-control'
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'list_etude_equipe';
    }
}
