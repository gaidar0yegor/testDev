<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210528142624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des urls courtes pour réduire la taille des SMS';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE short_url (id INT AUTO_INCREMENT NOT NULL, original_url VARCHAR(255) NOT NULL, token VARCHAR(127) NOT NULL, created_at DATETIME NOT NULL, reused_at DATETIME DEFAULT NULL, clicked INT NOT NULL, last_clicked_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_833605315F37A13B (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE short_url');
    }
}
