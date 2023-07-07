<?php

namespace App\Service\EntityLink;

use App\Exception\RdiException;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EntityLinkService
{
    /**
     * @var EntityLinkGeneratorInterface[]
     */
    private array $linkGenerators;

    private EntityManagerInterface $em;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        iterable $linkGenerators,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->linkGenerators = iterator_to_array($linkGenerators);
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
    }

    public function generateLink($entityOrClassname, int $id = null): EntityLink
    {
        if (is_string($entityOrClassname)) {
            $repository = $this->em->getRepository($entityOrClassname);
            $entity = $repository->find($id);
        } else {
            $entity = $entityOrClassname;
        }

        if (null === $entity) {
            return new NullEntityLink();
        }

        $entityClass = ClassUtils::getRealClass(get_class($entity));

        if (!array_key_exists($entityClass, $this->linkGenerators)) {
            throw new RdiException(sprintf(
                'Unable to generate a link for entity "%s".',
                $entityClass
            ));
        }

        return $this
            ->linkGenerators[$entityClass]
            ->generateLink($entity, $this->urlGenerator)
        ;
    }
}
