<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220308144105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE projet_planning (id INT AUTO_INCREMENT NOT NULL, projet_id INT NOT NULL, created_by_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_F319D401C18272 (projet_id), INDEX IDX_F319D401B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projet_planning_task (id INT AUTO_INCREMENT NOT NULL, projet_planning_id INT NOT NULL, parent_task_id INT DEFAULT NULL, text VARCHAR(255) NOT NULL, start_date DATE NOT NULL, duration INT NOT NULL, progress DOUBLE PRECISION NOT NULL, INDEX IDX_B76B86C1E6BC072F (projet_planning_id), INDEX IDX_B76B86C1FFFE75C0 (parent_task_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projet_planning ADD CONSTRAINT FK_F319D401C18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
        $this->addSql('ALTER TABLE projet_planning ADD CONSTRAINT FK_F319D401B03A8386 FOREIGN KEY (created_by_id) REFERENCES societe_user (id)');
        $this->addSql('ALTER TABLE projet_planning_task ADD CONSTRAINT FK_B76B86C1E6BC072F FOREIGN KEY (projet_planning_id) REFERENCES projet_planning (id)');
        $this->addSql('ALTER TABLE projet_planning_task ADD CONSTRAINT FK_B76B86C1FFFE75C0 FOREIGN KEY (parent_task_id) REFERENCES projet_planning_task (id)');
        $this->addSql('ALTER TABLE fait_marquant ADD projet_planning_task_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fait_marquant ADD CONSTRAINT FK_36D0F5A54195BEED FOREIGN KEY (projet_planning_task_id) REFERENCES projet_planning_task (id)');
        $this->addSql('CREATE INDEX IDX_36D0F5A54195BEED ON fait_marquant (projet_planning_task_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE projet_planning_task DROP FOREIGN KEY FK_B76B86C1E6BC072F');
        $this->addSql('ALTER TABLE fait_marquant DROP FOREIGN KEY FK_36D0F5A54195BEED');
        $this->addSql('ALTER TABLE projet_planning_task DROP FOREIGN KEY FK_B76B86C1FFFE75C0');
        $this->addSql('DROP TABLE projet_planning');
        $this->addSql('DROP TABLE projet_planning_task');
        $this->addSql('DROP INDEX IDX_36D0F5A54195BEED ON fait_marquant');
        $this->addSql('ALTER TABLE fait_marquant DROP projet_planning_task_id');
    }
}
