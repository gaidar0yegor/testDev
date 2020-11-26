<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201126110135 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Differencie les dates de dernieres mise a jour pour absences et temps passes';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cra ADD temps_passes_modified_at DATE DEFAULT NULL, CHANGE modified_at cra_modified_at DATE DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cra ADD modified_at DATE DEFAULT NULL, DROP cra_modified_at, DROP temps_passes_modified_at');
    }
}
