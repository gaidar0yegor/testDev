<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220426094527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bo_user_notification (id INT AUTO_INCREMENT NOT NULL, bo_user_id INT NOT NULL, activity_id INT NOT NULL, INDEX IDX_562A628B3461FCF (bo_user_id), INDEX IDX_562A628B81C06096 (activity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bo_user_notification ADD CONSTRAINT FK_562A628B3461FCF FOREIGN KEY (bo_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE bo_user_notification ADD CONSTRAINT FK_562A628B81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE bo_user_notification');
    }
}
