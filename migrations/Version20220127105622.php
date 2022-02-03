<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220127105622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE societe_user ADD my_superior_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE societe_user ADD CONSTRAINT FK_EFBCEA58914229B9 FOREIGN KEY (my_superior_id) REFERENCES societe_user (id)');
        $this->addSql('CREATE INDEX IDX_EFBCEA58914229B9 ON societe_user (my_superior_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE societe_user DROP FOREIGN KEY FK_EFBCEA58914229B9');
        $this->addSql('DROP INDEX IDX_EFBCEA58914229B9 ON societe_user');
        $this->addSql('ALTER TABLE societe_user DROP my_superior_id');
    }
}
