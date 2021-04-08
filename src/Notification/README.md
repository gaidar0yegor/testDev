# Notifications

Toutes les notifications qui peuvent être envoyées par RDI-Manager.

## Description

Une notification est un message emit à un moment précis
(rappel de saisie de temps un jour précis, invitation d'un utilisateur),
qui pourra être écouté par l'application pour ensuite envoyer un mail, sms ou message sur slack.

Gère aussi les notifications par mail et sms dans les dossier `Mail` et `Sms`.

## Technique

Les classes d'évenements sont de simple classes, toutes dans `src/Notification/Event`.
Elles seront dispatchées par l'event dispatcher.
