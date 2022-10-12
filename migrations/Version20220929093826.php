<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220929093826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE societe ADD work_start_time VARCHAR(5) DEFAULT \'09:00\', ADD work_end_time VARCHAR(5) DEFAULT \'17:00\'');
        $this->addSql('ALTER TABLE societe_user ADD work_start_time VARCHAR(5) DEFAULT NULL, ADD work_end_time VARCHAR(5) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE societe DROP work_start_time, DROP work_end_time');
        $this->addSql('ALTER TABLE societe_user DROP work_start_time, DROP work_end_time');
    }
}
