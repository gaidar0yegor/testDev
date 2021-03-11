<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210310101014 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add some history on entities';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fait_marquant ADD created_at DATETIME');
        $this->addSql('ALTER TABLE projet ADD created_at DATETIME');
        $this->addSql('ALTER TABLE societe ADD created_by_id INT DEFAULT NULL, ADD created_at DATETIME, ADD created_from VARCHAR(31) DEFAULT NULL');
        $this->addSql('ALTER TABLE societe ADD CONSTRAINT FK_19653DBDB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_19653DBDB03A8386 ON societe (created_by_id)');

        $this->addSql('update fait_marquant set created_at = date');
        $this->addSql('update projet set created_at = date_debut');
        $this->addSql('update projet set created_at = "2020-01-01 00:00:00" where created_at is null');
        $this->addSql('update societe set created_at = "2020-01-01 00:00:00"');

        $this->addSql('ALTER TABLE societe CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE projet CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE fait_marquant CHANGE created_at created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fait_marquant DROP created_at');
        $this->addSql('ALTER TABLE projet DROP created_at');
        $this->addSql('ALTER TABLE societe DROP FOREIGN KEY FK_19653DBDB03A8386');
        $this->addSql('DROP INDEX IDX_19653DBDB03A8386 ON societe');
        $this->addSql('ALTER TABLE societe DROP created_by_id, DROP created_at, DROP created_from');
    }
}
