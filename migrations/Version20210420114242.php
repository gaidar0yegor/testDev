<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210420114242 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Observateur externe';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE projet_observateur_externe (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, projet_id INT NOT NULL, date_ajout DATE NOT NULL, last_action_at DATETIME DEFAULT NULL, invitation_token VARCHAR(255) DEFAULT NULL, invitation_sent_at DATETIME DEFAULT NULL, invitation_email VARCHAR(255) DEFAULT NULL, invitation_telephone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\', watching TINYINT(1) NOT NULL, INDEX IDX_FC43D351A76ED395 (user_id), INDEX IDX_FC43D351C18272 (projet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projet_observateur_externe ADD CONSTRAINT FK_FC43D351A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE projet_observateur_externe ADD CONSTRAINT FK_FC43D351C18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE projet_observateur_externe');
    }
}
