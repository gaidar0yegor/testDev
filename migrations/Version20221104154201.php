<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221104154201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement ADD minutes_to_reminde INT, ADD reminder_at DATETIME, ADD is_reminded TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('UPDATE evenement SET minutes_to_reminde = 0');
        $this->addSql('UPDATE evenement SET reminder_at = start_date');
        $this->addSql('UPDATE evenement SET is_reminded = reminder_at <= NOW()');
        $this->addSql('ALTER TABLE evenement CHANGE reminder_at reminder_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE evenement CHANGE minutes_to_reminde minutes_to_reminde INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement DROP minutes_to_reminde, DROP reminder_at, DROP is_reminded');
    }
}
