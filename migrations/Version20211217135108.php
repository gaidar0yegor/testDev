<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211217135108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Supprimer les projet_participant des utilisateurs désactiver';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM `projet_participant` WHERE role <> "PROJET_CDP" AND id IN (SELECT pp.id FROM projet_participant pp
            INNER JOIN societe_user su ON pp.societe_user_id = su.id
            WHERE su.enabled = 0);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('');
    }
}
