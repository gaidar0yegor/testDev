<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210511112049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Pouvoir saisir nos temps passés à la semaine ou au jour près';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE temps_passe CHANGE pourcentage pourcentages LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE temps_passe CHANGE pourcentages pourcentage int NOT NULL AFTER projet_id');
    }
}
