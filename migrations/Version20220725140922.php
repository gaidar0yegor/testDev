<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220725140922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_book_invite (id INT AUTO_INCREMENT NOT NULL, labo_id INT DEFAULT NULL, invitation_token VARCHAR(255) NOT NULL, invitation_sent_at DATETIME NOT NULL, invitation_email VARCHAR(255) DEFAULT NULL, invitation_telephone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\', notes LONGTEXT DEFAULT NULL, role VARCHAR(31) NOT NULL, INDEX IDX_472ED2CFB65FA4A (labo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_book_invite ADD CONSTRAINT FK_472ED2CFB65FA4A FOREIGN KEY (labo_id) REFERENCES labo (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_book_invite');
    }
}
