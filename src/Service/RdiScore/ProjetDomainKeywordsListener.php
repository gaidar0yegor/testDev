<?php

namespace App\Service\RdiScore;

use App\Entity\Projet;
use App\Entity\RdiDomain;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * En choisissant un domain, on récupére sa liste des keywords s'ils n'existent pas dans la base de données
 */
class ProjetDomainKeywordsListener
{
    private RdiKeywordsGeneratorFromAPI $rdiKeywordsgenerator;

    public function __construct(RdiKeywordsGeneratorFromAPI $rdiKeywordsgenerator)
    {
        $this->rdiKeywordsgenerator = $rdiKeywordsgenerator;
    }

    public function postPersist(Projet $projet, LifecycleEventArgs $args): void
    {
        $rdiDomains = $projet->getRdiDomains();

        foreach ($rdiDomains as $rdiDomain){
            if (count($rdiDomain->getKeywords()) === 0){
                $keywords = $this->rdiKeywordsgenerator->getKeywords($rdiDomain);
                $rdiDomain->setKeywords($keywords);
                $args->getEntityManager()->persist($rdiDomain);
            }
        }

        $args->getEntityManager()->flush();
    }

    public function postUpdate(Projet $projet, LifecycleEventArgs $args): void
    {
        $collectionUpdates = $args->getEntityManager()->getUnitOfWork()->getScheduledCollectionUpdates();

        foreach ($collectionUpdates as $collectionUpdate){
            if ($collectionUpdate->first() instanceof RdiDomain && count($collectionUpdate->getInsertDiff())){
                $rdiDomains = $collectionUpdate->getInsertDiff();
                foreach ($rdiDomains as $rdiDomain){
                    if (count($rdiDomain->getKeywords()) === 0){
                        $keywords = $this->rdiKeywordsgenerator->getKeywords($rdiDomain);
                        $rdiDomain->setKeywords($keywords);
                        $args->getEntityManager()->persist($rdiDomain);
                    }
                }
                $args->getEntityManager()->flush();
                break;
            }
        }
    }
}
