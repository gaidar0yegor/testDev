# RDI Manager 01

Projet RDI Manager.

## Installation

Requires:

- php
- composer
- a database
- [Symfony CLI tool](https://symfony.com/doc/current/cloud/getting-started)
- Mailer DSN pour envoyer les emails
- wkhtmltopdf pour la génération de feuilles de temps en PDF

``` bash
# Clone the project
git clone git@github.com:Sylvain78310/rdi_manager_01.git
cd rdi_manager_01/

# Install php dependencies
composer install

# Si la base de données existe déjà
php bin/console doctrine:database:drop --force

# Initialize database and fixtures
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force --complete
FIXTURES=fixtures/demo php bin/console hautelook:fixtures:load --no-interaction

# Charger les fixtures sur environnement de dev sous windows
#remplacer la ligne suivante : 
FIXTURES=fixtures/demo php bin/console hautelook:fixtures:load --no-interaction
# par ces 2 lignes (commande sous windows à lancer avant pour pouvoir installer les fixtures de test)
set FIXTURES=fixtures/demo 
php bin/console hautelook:fixtures:load --no-interaction
 
### PHP Activation des extensions : les fichiers suivants :  php.ini / php.ini-development /php.ini-production
# pour l'envoi des invitations décommenter la ligne 
;extension=xsl
# pour la gestion des dates décommenter la ligne 
;extension=sodium
#Puis relancer le serveur

# Run application
symfony serve
```

Then create `.env.local` file at the root directory, and:

- Configure **email smtp server**:

``` yaml
MAILER_DSN=smtp://USER:PASS@mail.eurekaci.com:465
# USER : Email de la boite encodé avec urlencode (le "@" doit être encodé en "%40")
# PASS : Mot de passe de la boite email

MAILER_FROM=rdi-manager@eurekaci.com
# Pour changer l'email d'envoi. Par défaut celui ci dessus.
```

Pour tester la configuration, utilisez la commande pour envoyer un email de test :

``` bash
php bin/console app:test-mail votre-email@eurekaci.com
```

Vérifiez si vous avez reçu l'email de test dans votre boîte.

- Configure **database**:

``` yaml
DATABASE_URL=mysql://USER:PASS@HOST:PORT/DBNAME
```

- Configure **PDF generation**:

Chemin vers votre wkhtmltopdf,
utile pour la génération des feuilles de temps en PDF.
wkhtmltoimage est en général dans le même dossier.

```
WKHTMLTOPDF_PATH=/usr/local/bin/wkhtmltopdf
WKHTMLTOIMAGE_PATH=/usr/local/bin/wkhtmltoimage
```

Then go to <http://127.0.0.1:8000/projets>

### Initialiser une société et un accès référent

Lancer la commande :

``` bash
php bin/console app:init-societe-referent
```

La commande vous demandera le nom de la société et l'email du référent.

Un accès sera initialisé et un lien d'invitation sera créé
pour finaliser la création de compte du référent.

## Development

### Run unit tests

Pour lancer les tests unitaires :

```
php bin/phpunit
```

Les tests unitaires sont dans `tests/Unit/`,
et les dossiers reproduisent la même structure que dans `src/`.

### Run integration tests

Pour ne pas écraser votre base de données lors des tests,
il est possible d'en créer une autre,
puis de l'utiliser dans les tests
en ajoutant dans le fichier à la racine du projet `.env.test.local`:

```
DATABASE_URL=mysql://...

# Ou d'utiliser une base de données sqlite avec:
DATABASE_URL=sqlite:///%kernel.project_dir%/var/test.db3
```

Lancer tous les tests avec:

``` bash
vendor/bin/behat
```

Ou lancer les tests d'un seul fichier avec:

``` bash
vendor/bin/behat features/projet/page_projet.feature
```

### Migrations

Lorsque le schéma des entités est modifié,
il faut créer la migration qui sera jouée sur la base de données de production.

Pour créer la migration, il faut mettre sa base locale à l'état des migrations,
puis auto-générer la nouvelle migration par rapport aux différences du schéma :

``` bash
# Supprimer le schéma et le migrations déjà jouées
php bin/console doctrine:schema:drop --force --full-database

# Lancer les anciennes migrations
php bin/console doctrine:migrations:migrate

# Générer la nouvelle migration automatiquement
php bin/console doctrine:migrations:diff
```

La nouvelle migration est créée dans le dossier `migrations/` :

- Vérifier que celle ci ne fait potentiellement pas perdre de données,
celle ci sera executée en production.
- Lui donner une description dans `getDescription()`

Ensuite lancer cette nouvelle migration avec :

``` bash
php bin/console doctrine:migrations:migrate
```

Pour vérifier une dèrnière fois si la migration a généré
un schéma de base de données à jour, lancer :

``` bash
php bin/console doctrine:schema:up --dump-sql
```

## Production

Déploiement :

``` bash
# Récupérer la version X.Y.Z du projet
git clone -b X.Y.Z git@github.com:Sylvain78310/rdi_manager_01.git
cd rdi_manager_01/

# Créer un fichier .env.local avec la configuration nécessaire (voir readme ci dessus)
# Ajouter dans le .env.local :
APP_ENV=prod

# Mettre à jour les dépendences
composer install

# Mettre à jour la base de données
php bin/console doctrine:migrations:migrate

# Vider le cache
php bin/console cache:clear
```
