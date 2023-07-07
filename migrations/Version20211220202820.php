<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\File\FileHandler\ProjectFileHandler;
use App\MultiSociete\UserContext;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211220202820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fichier_projet_societe_user (fichier_projet_id INT NOT NULL, societe_user_id INT NOT NULL, INDEX IDX_71A00B8924CD47E2 (fichier_projet_id), INDEX IDX_71A00B8962A85E16 (societe_user_id), PRIMARY KEY(fichier_projet_id, societe_user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fichier_projet_societe_user ADD CONSTRAINT FK_71A00B8924CD47E2 FOREIGN KEY (fichier_projet_id) REFERENCES fichier_projet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fichier_projet_societe_user ADD CONSTRAINT FK_71A00B8962A85E16 FOREIGN KEY (societe_user_id) REFERENCES societe_user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE fichier_projet_societe_user');
    }
}
