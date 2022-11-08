# RDI Manager 01

Projet RDI Manager.

## Installation

Requires:

- php 7.4+
- PHP extensions: zip, gd, xsl, intl, mysql, openssl (`sudo apt install php7.4-zip php7.4-gd php7.4-xsl php7.4-intl php7.4-mysql php7.4-mbstring`)
- PHP extension for development: sqlite3 (`sudo apt install php7.4-sqlite3`)
- MySQL 8.0+
- composer
- nodejs
- yarn
- a database
- [Symfony CLI tool](https://symfony.com/doc/current/cloud/getting-started)
- Mailer DSN pour envoyer les emails
- wkhtmltopdf pour la génération de feuilles de temps en PDF

``` bash
# Clone the project
git clone git@github.com:RDI-Manager/rdi_manager_01.git
cd rdi_manager_01/

# Install php dependencies
composer install

# Install CKEditor
php bin/console ckeditor:install --clear=skip

# Install Assests
php bin/console assets:install

# - Editez votre .env.local (base de données, ... voir plus bas)

# Si la base de données existe déjà
php bin/console doctrine:database:drop --force

# Initialize database
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force --complete

# Install js dependencies
yarn

# Run application
symfony serve

# Watch assets
yarn watch
```

You can now open RDI-Manager: <http://127.0.0.1:8000>

And login with:

- Admin: `admin@societe.dev` / `admin`
- Chef de projet: `cdp@societe.dev` / `cdp`
- User: `user@societe.dev` / `user`

### Licenses

Sur windows : Ajouter la variable d’environnement système global

```
Nom de la variable : OPENSSL_CONF
Valeur de la variable : C:\xampp\apache\conf\openssl.cnf
```

Pour générer des licenses illimitées pour le dev, ajouter dans votre `.env.local`:

``` yaml
LICENSE_GENERATION_PRIVATE_KEY=%kernel.project_dir%/var/license-generation/private.pem
LICENSE_GENERATION_PUBLIC_KEY=%kernel.project_dir%/public/license/public-key.pem

LICENSE_PUBLIC_KEY_FILENAME=%kernel.project_dir%/public/license/public-key.pem
```

Puis lancer les commandes :

``` bash
# Créer votre propre clé privée pour générer des licenses
php bin/console app:license-generation:generate-private

# Créer des licenses illimitées pour chacune de vos sociétés en local
php bin/console app:license-generation:generate:premium-license
```

Pour en savoir plus sur les licenses, voir:
- [License, côté client](src/License/README.md)
- [LicenseGeneration côté serveur de licenses](src/LicenseGeneration/README.md)

### Activation des extensions sous windows

PHP Activation des extensions modifier les fichiers suivants :

- php.ini
- php.ini-development
- php.ini-production

et décommenter :

```
extension=xsl
extension=sodium
extension=intl
extension=zip
```

Puis relancer le serveur

### `.env.local`

Create `.env.local` file at the root directory, and:

- Configure **database**:

``` yaml
DATABASE_URL=mysql://USER:PASS@HOST:PORT/DBNAME
```

- Configure **application url**:

Used to know application url while using command line.
If not provided, absolute links will be resolved to `localhost` when sending email from command line.

``` yaml
# Nom de domaine. Si le port n'est pas 80, mettre par exemple "rdimanager.com:8000".
REQUEST_BASE_HOST=rdimanager.com

# Vide, sauf si l'application est dans un dossier, mettre par exemple "/dossier/application"
REQUEST_BASE_PATH=
```

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

**Developpement** : Pour recevoir les emails dans une boîte fictive et locale,
utiliser [mailhog](https://github.com/mailhog/MailHog).

Avec Docker : `docker run -d -p 1025:1025 -p 8025:8025 mailhog/mailhog`,
puis ajouter dans `.env.local` : `MAILER_DSN=smtp://0.0.0.0:1025`.
Ouvrir <http://0.0.0.0:8025/> dans le navigateur pour voir les mails recus.


- Configure **PDF generation**:

Chemin vers votre wkhtmltopdf,
utile pour la génération des feuilles de temps en PDF.
wkhtmltoimage est en général dans le même dossier.

```
WKHTMLTOPDF_PATH=/usr/local/bin/wkhtmltopdf
WKHTMLTOIMAGE_PATH=/usr/local/bin/wkhtmltoimage
```

- Configure Openstack files

By default, project files are uploaded on local hard drive.
To use OVH cloud object storage, use something like:

```
# Use openstack adapter
STORAGE_DEFAULT=default.storage.openstack

# Object storage localization
OS_REGION=GRA

# OVH Horizon user credentials, same as the one required to login at https://horizon.cloud.ovh.net/
OS_USER_NAME=user-nhvXXXXXXXX
OS_USER_PASSWORD=FXyaXXXXXXXXXXXXXXXXXXXXjt6

# The object storage name
OS_CONTAINER_NAME=projets-uploads
```

### Initialiser une société et un accès référent

Lancer la commande :

``` bash
php bin/console app:init-societe-referent
```

La commande vous demandera le nom de la société et l'email du référent.

Un accès sera initialisé et un lien d'invitation sera créé
pour finaliser la création de compte du référent.

### Elastic search pour calculer le score RDI

Le score RDI d'un projet est un coefficient entre 0 et 1.
En théorie il tend vers 1 quand un projet tend à être elligible RDI,
et donc potentiellement pourra recevoir des aides style CIR/CII...

[Installer Elastic search](https://www.elastic.co/guide/en/elasticsearch/reference/7.10/install-elasticsearch.html), ou lancer une instance directement avec Docker avec:

``` bash
docker run \
    -p 9200:9200 \
    -p 9300:9300 \
    -e "http.cors.enabled=true" \
    -e "http.cors.allow-origin=https://app.elasticvue.com" \
    -e "discovery.type=single-node" \
    docker.elastic.co/elasticsearch/elasticsearch:7.10.2
```

Ajouter le host dans votre `.env.local`:

```
ELASTIC_SEARCH_HOST=127.0.0.1:9200
```

Puis lancer la commander pour mettre à jour les scores de tous les projets :

``` bash
php bin/console app:update-projets-rdi-score
```

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
# Supprimer le schéma et les migrations déjà jouées
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

## Translation

Dump translations from templates, php files... with:

``` bash
php bin/console translation:update --dump-messages --force --output-format=yml --prefix="" fr
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

# Build assets
yarn
yarn build
```

### Log errors on a Slack channel

Create a Slack application and issue a webhook url. Then:

``` bash
cp config/override/monolog.yaml.dist config/override/monolog.yaml
```

And configure:

``` yaml
webhook_url: https://hooks.slack.com/services/T0XXXM/B0XXXL/kdeXXXiWxg
channel: '#rdi-manager-logs'
```

### Cron

Programmer cron pour qu'il lance la commande cron de Symfony chaque minute,
avec `crontab -e` par exemple :

```
* * * * * /usr/local/bin/php /absolute/path/to/rdi_manager_01/bin/console cron:run 1>> /dev/null 2>&1
```

#### Check timezone

To be sure that notification are sent at the same time as defined in admin, run:

``` bash
date
```

If it is not the local time, change it with:

``` bash
sudo dpkg-reconfigure tzdata
```

Then select Europe > Paris for french time.
