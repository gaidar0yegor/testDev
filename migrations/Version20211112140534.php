<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211112140534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fait_marquant ADD trashed_by_id INT DEFAULT NULL, ADD trashed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE fait_marquant ADD CONSTRAINT FK_36D0F5A5A038B036 FOREIGN KEY (trashed_by_id) REFERENCES societe_user (id)');
        $this->addSql('CREATE INDEX IDX_36D0F5A5A038B036 ON fait_marquant (trashed_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fait_marquant DROP FOREIGN KEY FK_36D0F5A5A038B036');
        $this->addSql('DROP INDEX IDX_36D0F5A5A038B036 ON fait_marquant');
        $this->addSql('ALTER TABLE fait_marquant DROP trashed_by_id, DROP trashed_at');
    }
}
