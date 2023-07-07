<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201222085612 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Permettre d\'envoyer une notification par SMS aux utilisateurs';
    }

    public function up(Schema $schema) : void
    {
        // Transforme les "06 06 06 06 06" en "0606060606" pour correspondre au futur format
        $this->addSql("
            update user
            set telephone = replace(telephone, ' ', '')
            where telephone is not null
        ");

        // Supprime les numéros de téléphone invalides pour ne pas causer de bug de parsing
        $this->addSql("
            update user
            set telephone = null
            where telephone not regexp '^(\\\\+33|0)6[0-9]{8}$'
        ");

        $this->addSql('ALTER TABLE user CHANGE telephone telephone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user CHANGE telephone telephone VARCHAR(45) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
