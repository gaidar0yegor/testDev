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
        $this->addSql('
            DELETE FROM projet_participant
            WHERE role <> "PROJET_CDP" AND id IN (
                SELECT * FROM (
                    SELECT projet_participant.id FROM projet_participant 
                    INNER JOIN societe_user ON projet_participant.societe_user_id = societe_user.id 
                    WHERE societe_user.enabled = 0
                ) as ppid
            );
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('');
    }
}
