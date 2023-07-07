<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220217123540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE projet_rdi_domain (projet_id INT NOT NULL, rdi_domain_id INT NOT NULL, INDEX IDX_9D769837C18272 (projet_id), INDEX IDX_9D7698376A58B709 (rdi_domain_id), PRIMARY KEY(projet_id, rdi_domain_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rdi_domain (id INT AUTO_INCREMENT NOT NULL, level INT NOT NULL, cle VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, keywords LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projet_rdi_domain ADD CONSTRAINT FK_9D769837C18272 FOREIGN KEY (projet_id) REFERENCES projet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE projet_rdi_domain ADD CONSTRAINT FK_9D7698376A58B709 FOREIGN KEY (rdi_domain_id) REFERENCES rdi_domain (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE projet ADD annual_rdi_scores LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', DROP rdi_score, DROP rdi_score_reliability');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE projet_rdi_domain DROP FOREIGN KEY FK_9D7698376A58B709');
        $this->addSql('DROP TABLE projet_rdi_domain');
        $this->addSql('DROP TABLE rdi_domain');
        $this->addSql('ALTER TABLE projet ADD rdi_score DOUBLE PRECISION DEFAULT NULL, ADD rdi_score_reliability DOUBLE PRECISION DEFAULT NULL, DROP annual_rdi_scores');
    }
}
