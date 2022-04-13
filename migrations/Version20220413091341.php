<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220413091341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fait_marquant_comment (id INT AUTO_INCREMENT NOT NULL, observateur_externe_id INT DEFAULT NULL, societe_user_id INT DEFAULT NULL, fait_marquant_id INT NOT NULL, text LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_8881E8E64746193 (observateur_externe_id), INDEX IDX_8881E8E62A85E16 (societe_user_id), INDEX IDX_8881E8E56EA9DA1 (fait_marquant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fait_marquant_comment ADD CONSTRAINT FK_8881E8E64746193 FOREIGN KEY (observateur_externe_id) REFERENCES projet_observateur_externe (id)');
        $this->addSql('ALTER TABLE fait_marquant_comment ADD CONSTRAINT FK_8881E8E62A85E16 FOREIGN KEY (societe_user_id) REFERENCES societe_user (id)');
        $this->addSql('ALTER TABLE fait_marquant_comment ADD CONSTRAINT FK_8881E8E56EA9DA1 FOREIGN KEY (fait_marquant_id) REFERENCES fait_marquant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE fait_marquant_comment');
    }
}
