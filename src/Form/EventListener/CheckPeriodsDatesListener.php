<?php


namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;

class CheckPeriodsDatesListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::SUBMIT => 'checkDates',
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
}