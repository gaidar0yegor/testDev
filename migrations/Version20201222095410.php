<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201222095410 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Permettre à l\'admin d\'activer les SMS ou non pour les utilisateurs de sa société';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE societe ADD sms_enabled TINYINT(1) NOT NULL');

        // Active les SMS pour toutes les sociétés
        $this->addSql('
            update societe
            set sms_enabled = 1
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE societe DROP sms_enabled');
    }
}
