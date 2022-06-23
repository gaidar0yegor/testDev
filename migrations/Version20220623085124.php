<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220623085124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE etude (id INT AUTO_INCREMENT NOT NULL, user_book_id INT NOT NULL, banner_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, acronyme VARCHAR(255) NOT NULL, date_debut DATE DEFAULT NULL, date_fin DATE DEFAULT NULL, resume LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_1DDEA9244EAFAD8B (user_book_id), UNIQUE INDEX UNIQ_1DDEA924684EC833 (banner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fichier_etude (id INT AUTO_INCREMENT NOT NULL, fichier_id INT NOT NULL, etude_id INT NOT NULL, note_id INT DEFAULT NULL, uploaded_by_id INT NOT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_2A871D1CF915CFE (fichier_id), INDEX IDX_2A871D1C47ABD362 (etude_id), INDEX IDX_2A871D1C26ED0855 (note_id), INDEX IDX_2A871D1CA2B28FE8 (uploaded_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE labo (id INT AUTO_INCREMENT NOT NULL, logo_id INT DEFAULT NULL, created_by_id INT NOT NULL, rnsr VARCHAR(45) DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, created_from VARCHAR(31) DEFAULT NULL, UNIQUE INDEX UNIQ_9367435CF98F144A (logo_id), INDEX IDX_9367435CB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note (id INT AUTO_INCREMENT NOT NULL, etude_id INT NOT NULL, created_by_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, reading_name VARCHAR(255) DEFAULT NULL, author VARCHAR(255) DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, date DATETIME NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_CFBDFA1447ABD362 (etude_id), INDEX IDX_CFBDFA14B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_book (id INT AUTO_INCREMENT NOT NULL, labo_id INT DEFAULT NULL, user_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, invitation_token VARCHAR(255) DEFAULT NULL, invitation_sent_at DATETIME DEFAULT NULL, invitation_email VARCHAR(255) DEFAULT NULL, invitation_telephone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\', role VARCHAR(31) NOT NULL, INDEX IDX_B164EFF8B65FA4A (labo_id), INDEX IDX_B164EFF8A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE etude ADD CONSTRAINT FK_1DDEA9244EAFAD8B FOREIGN KEY (user_book_id) REFERENCES user_book (id)');
        $this->addSql('ALTER TABLE etude ADD CONSTRAINT FK_1DDEA924684EC833 FOREIGN KEY (banner_id) REFERENCES fichier (id)');
        $this->addSql('ALTER TABLE fichier_etude ADD CONSTRAINT FK_2A871D1CF915CFE FOREIGN KEY (fichier_id) REFERENCES fichier (id)');
        $this->addSql('ALTER TABLE fichier_etude ADD CONSTRAINT FK_2A871D1C47ABD362 FOREIGN KEY (etude_id) REFERENCES etude (id)');
        $this->addSql('ALTER TABLE fichier_etude ADD CONSTRAINT FK_2A871D1C26ED0855 FOREIGN KEY (note_id) REFERENCES note (id)');
        $this->addSql('ALTER TABLE fichier_etude ADD CONSTRAINT FK_2A871D1CA2B28FE8 FOREIGN KEY (uploaded_by_id) REFERENCES user_book (id)');
        $this->addSql('ALTER TABLE labo ADD CONSTRAINT FK_9367435CF98F144A FOREIGN KEY (logo_id) REFERENCES fichier (id)');
        $this->addSql('ALTER TABLE labo ADD CONSTRAINT FK_9367435CB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA1447ABD362 FOREIGN KEY (etude_id) REFERENCES etude (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14B03A8386 FOREIGN KEY (created_by_id) REFERENCES user_book (id)');
        $this->addSql('ALTER TABLE user_book ADD CONSTRAINT FK_B164EFF8B65FA4A FOREIGN KEY (labo_id) REFERENCES labo (id)');
        $this->addSql('ALTER TABLE user_book ADD CONSTRAINT FK_B164EFF8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD current_user_book_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B11E1350 FOREIGN KEY (current_user_book_id) REFERENCES user_book (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649B11E1350 ON user (current_user_book_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fichier_etude DROP FOREIGN KEY FK_2A871D1C47ABD362');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA1447ABD362');
        $this->addSql('ALTER TABLE user_book DROP FOREIGN KEY FK_B164EFF8B65FA4A');
        $this->addSql('ALTER TABLE fichier_etude DROP FOREIGN KEY FK_2A871D1C26ED0855');
        $this->addSql('ALTER TABLE etude DROP FOREIGN KEY FK_1DDEA9244EAFAD8B');
        $this->addSql('ALTER TABLE fichier_etude DROP FOREIGN KEY FK_2A871D1CA2B28FE8');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14B03A8386');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B11E1350');
        $this->addSql('DROP TABLE etude');
        $this->addSql('DROP TABLE fichier_etude');
        $this->addSql('DROP TABLE labo');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP TABLE user_book');
        $this->addSql('DROP INDEX IDX_8D93D649B11E1350 ON user');
        $this->addSql('ALTER TABLE user DROP current_user_book_id');
    }
}
