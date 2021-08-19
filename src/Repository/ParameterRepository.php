<?php

namespace App\Repository;

use App\Entity\Parameter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;

/**
 * @method Parameter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parameter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parameter[]    findAll()
 * @method Parameter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParameterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parameter::class);
    }

    public function getParameter(string $name, string $default = null): Parameter
    {
        $parameter = $this->findOneByName($name);

        if (null === $parameter) {
            $parameter = new Parameter();
            $parameter
                ->setName($name)
                ->setValue($default)
            ;

            $this->_em->persist($parameter);
        }

        return $parameter;
    }
}
