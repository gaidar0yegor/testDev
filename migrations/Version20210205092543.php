<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210205092543 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add help texts';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD help_texts LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\'');

        $this->addSql('
            update user
            set help_texts = "[]"
            where invitation_token is null
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP help_texts');
    }
}
