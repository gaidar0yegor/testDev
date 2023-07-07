<?php


namespace App\Form\EventListener;

use App\Entity\SocieteUserPeriod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CheckPeriodsDatesListener implements EventSubscriberInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::SUBMIT => 'checkDates',
            FormEvents::PRE_SET_DATA => 'addEmptyPeriod',
        ];
    }

    public function checkDates(SubmitEvent $event): void
    {
        $forms = $event->getForm()->get('societeUserPeriods');

        $dateLeaveLast = null;
        foreach ($forms as $societeUserPeriodForm) {
            $dateEntry = $societeUserPeriodForm->getData()->getDateEntry();
            $dateLeave = $societeUserPeriodForm->getData()->getDateLeave();
            if (
                (!$dateEntry && $dateLeave) ||
                ($dateEntry && $dateLeave && $dateEntry > $dateLeave) ||
                ($dateEntry && $dateLeaveLast && $dateEntry < $dateLeaveLast)
            ) {
                $societeUserPeriodForm->addError(new FormError("Les dates d'entrée / sortie ne sont pas cohérentes !"));
            }
            $dateLeaveLast = $dateLeave;
        }
    }

    public function addEmptyPeriod(FormEvent $event): void
    {
        $societeUser = $event->getData();
        $lastPeriod = $societeUser->getSocieteUserPeriods()->last();
        $emptyPeriod = $this->em->getRepository(SocieteUserPeriod::class)->findBy([
            'societeUser' => $societeUser->getId(),
            'dateEntry' => null,
            'dateLeave' => null,
        ]);

        if (count($emptyPeriod) === 0 && $lastPeriod->getDateEntry() && $lastPeriod->getDateLeave()){
            $societeUser->addSocieteUserPeriod(new SocieteUserPeriod());
            $this->em->persist($societeUser);
            $this->em->flush();
        }
    }
}