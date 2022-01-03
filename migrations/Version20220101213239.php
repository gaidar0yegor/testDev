<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220101213239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fait_marquant_societe_user (fait_marquant_id INT NOT NULL, societe_user_id INT NOT NULL, INDEX IDX_D40D6C056EA9DA1 (fait_marquant_id), INDEX IDX_D40D6C062A85E16 (societe_user_id), PRIMARY KEY(fait_marquant_id, societe_user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fait_marquant_societe_user ADD CONSTRAINT FK_D40D6C056EA9DA1 FOREIGN KEY (fait_marquant_id) REFERENCES fait_marquant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fait_marquant_societe_user ADD CONSTRAINT FK_D40D6C062A85E16 FOREIGN KEY (societe_user_id) REFERENCES societe_user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE fait_marquant_societe_user');
    }
}
