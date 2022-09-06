<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220905150246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE projet_revenue (id INT AUTO_INCREMENT NOT NULL, projet_id INT NOT NULL, titre VARCHAR(255) NOT NULL, amount NUMERIC(10, 2) NOT NULL, date DATE DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_6B3B31F4C18272 (projet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projet_revenue ADD CONSTRAINT FK_6B3B31F4C18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
        $this->addSql('ALTER TABLE projet ADD roi_enabled TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE projet_revenue');
        $this->addSql('ALTER TABLE projet DROP roi_enabled');
    }
}
