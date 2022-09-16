<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220915141919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rappel (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, societe_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, rappel_date DATETIME NOT NULL, minutes_to_reminde INT NOT NULL, reminder_at DATETIME NOT NULL, is_reminded TINYINT(1) DEFAULT \'0\' NOT NULL, acknowledged TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_303A29C9A76ED395 (user_id), INDEX IDX_303A29C9FCF77503 (societe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rappel ADD CONSTRAINT FK_303A29C9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE rappel ADD CONSTRAINT FK_303A29C9FCF77503 FOREIGN KEY (societe_id) REFERENCES societe (id)');
        $this->addSql('INSERT INTO cron_job (name, command, schedule, description, enabled) VALUES ("rappel:send-notifications", "app:rappel:send-notifications", "* * * * *", "Envoi les notifications des rappels créés par les utilisateurs.", "1")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE rappel');
        $this->addSql('DELETE FROM cron_job WHERE cron_job.command LIKE "app:rappel:send-notifications" LIMIT 1');
    }
}
