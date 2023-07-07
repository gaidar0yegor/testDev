<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220125084129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dashboard_consolide (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_85396800A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dashboard_consolide_societe_user (dashboard_consolide_id INT NOT NULL, societe_user_id INT NOT NULL, INDEX IDX_C8B225DB9460AC12 (dashboard_consolide_id), INDEX IDX_C8B225DB62A85E16 (societe_user_id), PRIMARY KEY(dashboard_consolide_id, societe_user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dashboard_consolide ADD CONSTRAINT FK_85396800A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dashboard_consolide_societe_user ADD CONSTRAINT FK_C8B225DB9460AC12 FOREIGN KEY (dashboard_consolide_id) REFERENCES dashboard_consolide (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dashboard_consolide_societe_user ADD CONSTRAINT FK_C8B225DB62A85E16 FOREIGN KEY (societe_user_id) REFERENCES societe_user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dashboard_consolide_societe_user DROP FOREIGN KEY FK_C8B225DB9460AC12');
        $this->addSql('DROP TABLE dashboard_consolide');
        $this->addSql('DROP TABLE dashboard_consolide_societe_user');
    }
}
