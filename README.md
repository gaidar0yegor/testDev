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

Then go to <http://127.0.0.1:8000/projets>

## Development

Run integration tests:

``` bash
vendor/bin/behat
```
