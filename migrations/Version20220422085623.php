<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220422085623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE projet_budget_expense (id INT AUTO_INCREMENT NOT NULL, projet_id INT NOT NULL, titre VARCHAR(255) NOT NULL, amount NUMERIC(10, 2) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_D9974EE4C18272 (projet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projet_budget_expense ADD CONSTRAINT FK_D9974EE4C18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
        $this->addSql('ALTER TABLE projet ADD etp NUMERIC(5, 3) DEFAULT NULL, ADD budget_euro NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE societe ADD cout_etp NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE societe_user ADD cout_etp NUMERIC(10, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE projet_budget_expense');
        $this->addSql('ALTER TABLE projet DROP etp, DROP budget_euro');
        $this->addSql('ALTER TABLE societe DROP cout_etp');
        $this->addSql('ALTER TABLE societe_user DROP cout_etp');
    }
}
