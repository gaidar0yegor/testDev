<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211223105318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dossier_fichier_projet (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, projet_id INT NOT NULL, nom_md5 VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_91599B20B03A8386 (created_by_id), INDEX IDX_91599B20C18272 (projet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dossier_fichier_projet ADD CONSTRAINT FK_91599B20B03A8386 FOREIGN KEY (created_by_id) REFERENCES societe_user (id)');
        $this->addSql('ALTER TABLE dossier_fichier_projet ADD CONSTRAINT FK_91599B20C18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
        $this->addSql('ALTER TABLE fichier_projet ADD dossier_fichier_projet_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fichier_projet ADD CONSTRAINT FK_78207D83DE0E39C0 FOREIGN KEY (dossier_fichier_projet_id) REFERENCES dossier_fichier_projet (id)');
        $this->addSql('CREATE INDEX IDX_78207D83DE0E39C0 ON fichier_projet (dossier_fichier_projet_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fichier_projet DROP FOREIGN KEY FK_78207D83DE0E39C0');
        $this->addSql('DROP TABLE dossier_fichier_projet');
        $this->addSql('DROP INDEX IDX_78207D83DE0E39C0 ON fichier_projet');
        $this->addSql('ALTER TABLE fichier_projet DROP dossier_fichier_projet_id');
    }
}
