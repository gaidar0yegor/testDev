<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210701100303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Pouvoir ajouter un lien vers Trello, ou d\'autres liens externes en général sur la page projet';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE projet_url (id INT AUTO_INCREMENT NOT NULL, projet_id INT NOT NULL, url VARCHAR(255) NOT NULL, text VARCHAR(127) DEFAULT NULL, INDEX IDX_21A9FCAFC18272 (projet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projet_url ADD CONSTRAINT FK_21A9FCAFC18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE projet_url');
    }
}
