<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201211113913 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Permet aux utilisateurs de filtrer les notificiations qu\'ils recoivent';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD notification_enabled TINYINT(1) NOT NULL, ADD notification_create_fait_marquant_enabled TINYINT(1) NOT NULL, ADD notification_latest_fait_marquant_enabled TINYINT(1) NOT NULL, ADD notification_saisie_temps_enabled TINYINT(1) NOT NULL');

        $this->addSql('
            update user
            set
                notification_enabled = 1,
                notification_create_fait_marquant_enabled = 1,
                notification_latest_fait_marquant_enabled = 1,
                notification_saisie_temps_enabled = 1
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP notification_enabled, DROP notification_create_fait_marquant_enabled, DROP notification_latest_fait_marquant_enabled, DROP notification_saisie_temps_enabled');
    }
}
