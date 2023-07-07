<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201204155510 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Lier un fichier projet à un fait marquant';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fichier_projet ADD fait_marquant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fichier_projet ADD CONSTRAINT FK_78207D8356EA9DA1 FOREIGN KEY (fait_marquant_id) REFERENCES fait_marquant (id)');
        $this->addSql('CREATE INDEX IDX_78207D8356EA9DA1 ON fichier_projet (fait_marquant_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fichier_projet DROP FOREIGN KEY FK_78207D8356EA9DA1');
        $this->addSql('DROP INDEX IDX_78207D8356EA9DA1 ON fichier_projet');
        $this->addSql('ALTER TABLE fichier_projet DROP fait_marquant_id');
    }
}
