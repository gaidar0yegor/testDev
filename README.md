# RDI Manager 01

Projet RDI Manager.

## Installation

Requires:

- php
- composer
- a database
- [Symfony CLI tool](https://symfony.com/doc/current/cloud/getting-started)

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
php bin/console doctrine:schema:update --force
php bin/console hautelook:fixtures:load --no-interaction

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

- Configure **database**:

``` yaml
DATABASE_URL=mysql://USER:PASS@HOST:PORT/DBNAME
```

Then go to <http://127.0.0.1:8000/projets>

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
