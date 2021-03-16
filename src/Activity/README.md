# Activity

Permet de gérer globalement les activités, comme l'activité d'un user, ou d'un projet.
L'activité permet de garder un historique des actions réalisées.

## Description

L'activité d'un utilisateur, entité `UserActivity` :
    - est visible par un admin
    - est visible par ce même utilisateur
    - n'est pas visible par un autre utilisateur non admin
        (ce qui permet aussi de ne pas montrer des actions faites par un user sur un de ses projets dont on n'a pas accès)

L'activité d'un projet, entité `ProjetActivity` :
    - est visible par les observateurs

Les push notifications, entité `UserNotification` :
    - est visible seulement par le même user
    - peut être marquée comme lue

## Technique

```
         ,------------------.
         |Activity          |
         |------------------|
         |Datetime $datetime|
         |string $type      |
         |array $parameters |
         `------------------'
           ^              ^
           |              |
,------------.          ,--------------.
|UserActivity|          |ProjetActivity|
|------------|          |--------------|
|User $user  |          |Projet $projet|
`------------'          `--------------'
```

Une instance d'`Activity` est générique, et pourrait être utilisée n'importe où.

Les entités `UserActivity`, `ProjetActivity`, ... permettent de lier une activité à un user ou un projet.

``` php
use App\Activity\ActivityService;
use App\Entity\Activity;

$activity = new Activity();

$activity->setDatetime(new DateTime()); // Date à laquelle s'est passée l'action, qui sera affichée.
$activity->setType('my_type'); // Type de l'activité, lié à l'ActivityInterface qui pourra le gérer.
$activity->setParameters([/* ... */]); // Paramètres personnalisés qui peuvent être utilisé pour l'affichage.

$activityService->render($activity); // Appelle le bon ActivityInterface (selon $type) pour retourner "Il s'est passé ca..."
```

### Ajouter un `ActivityInterface`

Exemple de types d'activités déjà créés :
 - `X a ré-activé le compte de Y`
 - `X a créé le projet Y.`
 - `X a rejoint la société.`

Les types sont des services :
 - qui implémentent `ActivityInterface`
 - taggés `app.activity_type`

Les ActivityInterface peuvent aussi être des listeners
pour ajouter eux-même des activités.

- Voir [ActivityInterface](ActivityInterface.php).
- Voir des exemples déjà créé de types d'activités : [src/Activity/Type](Type).
