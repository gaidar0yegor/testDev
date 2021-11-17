<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211116150933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE societe ADD logo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE societe ADD CONSTRAINT FK_19653DBDF98F144A FOREIGN KEY (logo_id) REFERENCES fichier (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_19653DBDF98F144A ON societe (logo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE societe DROP FOREIGN KEY FK_19653DBDF98F144A');
        $this->addSql('DROP INDEX UNIQ_19653DBDF98F144A ON societe');
        $this->addSql('ALTER TABLE societe DROP logo_id');
    }
}
