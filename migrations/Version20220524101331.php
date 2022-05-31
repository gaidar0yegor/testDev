<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220524101331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, projet_id INT DEFAULT NULL, text VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, type VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, location VARCHAR(255) DEFAULT NULL, auto_update_cra TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_B26681EB03A8386 (created_by_id), INDEX IDX_B26681EC18272 (projet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement_participant (id INT AUTO_INCREMENT NOT NULL, evenement_id INT NOT NULL, societe_user_id INT NOT NULL, required TINYINT(1) NOT NULL, INDEX IDX_460A7D3AFD02F13 (evenement_id), INDEX IDX_460A7D3A62A85E16 (societe_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE societe_user_evenement_notification (id INT AUTO_INCREMENT NOT NULL, societe_user_id INT NOT NULL, activity_id INT NOT NULL, acknowledged TINYINT(1) NOT NULL, INDEX IDX_C4986F9762A85E16 (societe_user_id), INDEX IDX_C4986F9781C06096 (activity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EB03A8386 FOREIGN KEY (created_by_id) REFERENCES societe_user (id)');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EC18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
        $this->addSql('ALTER TABLE evenement_participant ADD CONSTRAINT FK_460A7D3AFD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE evenement_participant ADD CONSTRAINT FK_460A7D3A62A85E16 FOREIGN KEY (societe_user_id) REFERENCES societe_user (id)');
        $this->addSql('ALTER TABLE societe_user_evenement_notification ADD CONSTRAINT FK_C4986F9762A85E16 FOREIGN KEY (societe_user_id) REFERENCES societe_user (id)');
        $this->addSql('ALTER TABLE societe_user_evenement_notification ADD CONSTRAINT FK_C4986F9781C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement_participant DROP FOREIGN KEY FK_460A7D3AFD02F13');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE evenement_participant');
        $this->addSql('DROP TABLE societe_user_evenement_notification');
    }
}
