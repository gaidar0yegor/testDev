# License

Limiter l'utilisation de RDI-Manager dans le temps et le nombre de contributeurs et de projets,
afin de rappeller de renouveller son abonnement.

## Description

Ce composant permet de :
- lire les fichiers de licenses
- calculer les quotas maximum à partir des licenses
- calculer les quotas utilisé par une société
- laisser l'accès en lecture seule si les licenses sont expirées

Objectifs des licenses :
- pourrait fonctionner sur une instance auto-hebergée
- liée à une société (app.rdimanager.com ou auto-hebergée)
- à une date limite, peut être au mois ou à l'année
- devrait pouvoir être renouvellé automatiquement (tous les mois)
- doit pouvoir être achetée, ajoutée à une société
- marge d'utilisation d'un mois après expiration
- quand la license expire, l'accès rétrogradé en lecture seule

Les données brute d'une license sont :
- societe uuid (unique/fixe) et raisonSociale (peut changer)
- date expiration
- contributeurs max
- projets max

Un fichier de license contient une partie texte clair,
non utilisée par le système, mais juste pour connaître le contenu de la license,
et une partie chiffrée, qui déchiffrée, contient un json qui sera lu par le système.

Partie chiffrée :
Les licenses sont signées avec une clés privée possédée par RDI-Manager.
Il faut donc la clé publique pour déchiffrer la partie chiffrée.
Ce qui veut dire que la partie "chiffrée" n'a rien de secret, et est faîte
pour être déchiffrée publiquement.
Cela sert à être sûr qu'une license est officiellement créée par RDI-Manager,
grâce à cette signature numérique, car seule la clé publique de RDI-Manager
pourra la déchiffrer.

Les quotas actuellement définis :
- Projets actif : un projet toujours actif si on se referre à ses dates de début/fin.
- Contributeur : un utilisateur actif, et contributeur sur au moins un projet de la société.

## Configuration

Pour déchiffrer un fichier de license, il faut indiquer l'url de la clé publique,
et un chemin où la stocker :

```
LICENSE_PUBLIC_KEY_URL=https://app.rdimanager.com/license/public-key.pem
LICENSE_PUBLIC_KEY_FILENAME=%kernel.project_dir%/var/license-public-key.pem
```

Les licenses d'une société seront stockées dans le dossier :
`var/storage/licenses/{societe_uuid}/`.

Commandes :

``` bash
app:license:download-public-key
# Pour récupérer la clé publique : télécharge la clé publique à partir du serveur officiel

app:license:read
# Vérifie si une license est officielle, et décode son contenu
```

## Technique

Les quotas sont des classes qui implémentent `App\License\LicenseQuotaInterface`.

Ils sont tagués `app.license_quota` dans le container pour être pris en compte par le service `App\License\QuotaService`.

Une classe quota fourni sont nom unique,
et une méthode de calcul du quota actuellement utilisé par une société.
