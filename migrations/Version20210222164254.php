<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210222164254 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add unique id to societe to bind licenses';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE societe ADD uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');

        $this->addSql("
            update societe
            set uuid = (
                select lower(concat(
                    lpad(hex(floor(rand() * 0xffffffff)), 8, '0'), '-',
                    lpad(hex(floor(rand() * 0x0ffff)), 4, '0'), '-',
                    lpad(hex(floor(rand() * 0x0fff)), 4, '0'), '-',
                    lpad(hex(floor(rand() * 0xffff)), 4, '0'), '-',
                    lpad(hex(floor(rand() * 0xffffffffffff)), 12, '0')
                ))
            )
        ");

        $this->addSql('CREATE UNIQUE INDEX UNIQ_19653DBDD17F50A6 ON societe (uuid)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_19653DBDD17F50A6 ON societe');
        $this->addSql('ALTER TABLE societe DROP uuid');
    }
}
