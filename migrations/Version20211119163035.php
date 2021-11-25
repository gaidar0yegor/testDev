<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211119163035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Every SocieteUser can have one or many SocieteUserPeriod';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
            CREATE TABLE societe_user_period (
            id INT AUTO_INCREMENT NOT NULL, 
            societe_user_id INT NOT NULL, 
            date_entry DATE DEFAULT NULL, 
            date_leave DATE DEFAULT NULL, 
            INDEX IDX_C045D20262A85E16 (societe_user_id), 
            PRIMARY KEY(id)) 
            DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('
            ALTER TABLE societe_user_period 
            ADD CONSTRAINT FK_C045D20262A85E16 FOREIGN KEY (societe_user_id) REFERENCES societe_user (id)');

        $this->addSql('
            INSERT INTO societe_user_period(societe_user_id, date_entry, date_leave)
                SELECT id, date_entree, date_sortie
                from societe_user');

        $this->addSql('
            ALTER TABLE societe_user
                drop date_entree,
                drop date_sortie');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE societe_user_period DROP FOREIGN KEY FK_C045D20262A85E16');

        $this->addSql('
            alter table societe_user
                add date_entree date default null,
                add date_sortie date default null');

        $this->addSql('
            update societe_user
            left join societe_user_period on societe_user.id = societe_user_period.societe_user_id
            set 
                societe_user.date_entree = societe_user_period.date_entry,
                societe_user.date_sortie = societe_user_period.date_leave
            ');

        $this->addSql('DROP TABLE societe_user_period');
    }
}
