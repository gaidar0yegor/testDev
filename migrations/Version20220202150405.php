<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220202150405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossier_fichier_projet DROP FOREIGN KEY FK_91599B20B03A8386');
        $this->addSql('DROP INDEX IDX_91599B20B03A8386 ON dossier_fichier_projet');
        $this->addSql('ALTER TABLE dossier_fichier_projet DROP created_by_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossier_fichier_projet ADD created_by_id INT NOT NULL');
        $this->addSql('ALTER TABLE dossier_fichier_projet ADD CONSTRAINT FK_91599B20B03A8386 FOREIGN KEY (created_by_id) REFERENCES societe_user (id)');
        $this->addSql('CREATE INDEX IDX_91599B20B03A8386 ON dossier_fichier_projet (created_by_id)');
    }
}
