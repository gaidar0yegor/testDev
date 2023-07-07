<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210211092944 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Remove SocieteStatut, remove User.cadre';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE societe DROP FOREIGN KEY FK_19653DBDF6203804');
        $this->addSql('DROP TABLE societe_statut');
        $this->addSql('DROP INDEX IDX_19653DBDF6203804 ON societe');
        $this->addSql('ALTER TABLE societe DROP statut_id');
        $this->addSql('ALTER TABLE user DROP cadre');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE societe_statut (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(45) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE societe ADD statut_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE societe ADD CONSTRAINT FK_19653DBDF6203804 FOREIGN KEY (statut_id) REFERENCES societe_statut (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_19653DBDF6203804 ON societe (statut_id)');
        $this->addSql('ALTER TABLE user ADD cadre TINYINT(1) DEFAULT NULL');

        $this->addSql('
            insert into societe_statut (libelle)
            values ("Activée")
        ');
        $this->addSql('
            update societe
            set statut_id = (
                select id
                from societe_statut
                limit 1
            )
        ');
    }
}
