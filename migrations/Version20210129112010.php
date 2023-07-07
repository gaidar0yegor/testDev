<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210129112010 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Ajout de l\'onboarding';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD onboarding_enabled TINYINT(1) NOT NULL, ADD onboarding_timesheet_completed TINYINT(1) NOT NULL');

        $this->addSql('
            update user
            set onboarding_enabled = 1
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP onboarding_enabled, DROP onboarding_timesheet_completed');
    }
}
