<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220510103555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE projet_event (id INT AUTO_INCREMENT NOT NULL, projet_id INT NOT NULL, text VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, type VARCHAR(100) NOT NULL, INDEX IDX_9008E511C18272 (projet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projet_event_participant (id INT AUTO_INCREMENT NOT NULL, projet_event_id INT NOT NULL, participant_id INT NOT NULL, accepted TINYINT(1) DEFAULT NULL, INDEX IDX_91BF75535677837C (projet_event_id), INDEX IDX_91BF75539D1C3019 (participant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projet_event ADD CONSTRAINT FK_9008E511C18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
        $this->addSql('ALTER TABLE projet_event_participant ADD CONSTRAINT FK_91BF75535677837C FOREIGN KEY (projet_event_id) REFERENCES projet_event (id)');
        $this->addSql('ALTER TABLE projet_event_participant ADD CONSTRAINT FK_91BF75539D1C3019 FOREIGN KEY (participant_id) REFERENCES projet_participant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE projet_event_participant DROP FOREIGN KEY FK_91BF75535677837C');
        $this->addSql('DROP TABLE projet_event');
        $this->addSql('DROP TABLE projet_event_participant');
    }
}
