<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220404122257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE projet_planning_task_projet_participant (projet_planning_task_id INT NOT NULL, projet_participant_id INT NOT NULL, INDEX IDX_9AFC85B34195BEED (projet_planning_task_id), INDEX IDX_9AFC85B3AB32B9BA (projet_participant_id), PRIMARY KEY(projet_planning_task_id, projet_participant_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projet_planning_task_projet_participant ADD CONSTRAINT FK_9AFC85B34195BEED FOREIGN KEY (projet_planning_task_id) REFERENCES projet_planning_task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE projet_planning_task_projet_participant ADD CONSTRAINT FK_9AFC85B3AB32B9BA FOREIGN KEY (projet_participant_id) REFERENCES projet_participant (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE projet_planning_task_projet_participant');
    }
}
