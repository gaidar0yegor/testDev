<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220727134042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipe (id INT AUTO_INCREMENT NOT NULL, labo_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_2449BA15B65FA4A (labo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE equipe ADD CONSTRAINT FK_2449BA15B65FA4A FOREIGN KEY (labo_id) REFERENCES labo (id)');
        $this->addSql('ALTER TABLE etude ADD equipe_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE etude ADD CONSTRAINT FK_1DDEA9246D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id)');
        $this->addSql('CREATE INDEX IDX_1DDEA9246D861B89 ON etude (equipe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etude DROP FOREIGN KEY FK_1DDEA9246D861B89');
        $this->addSql('DROP TABLE equipe');
        $this->addSql('DROP INDEX IDX_1DDEA9246D861B89 ON etude');
        $this->addSql('ALTER TABLE etude DROP equipe_id');
    }
}
