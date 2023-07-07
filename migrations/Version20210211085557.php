<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210211085557 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add Slack integration';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE slack_access_token (id INT AUTO_INCREMENT NOT NULL, societe_id INT NOT NULL, authed_user_id VARCHAR(15) DEFAULT NULL, scope LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', access_token VARCHAR(255) NOT NULL, bot_user_id VARCHAR(15) DEFAULT NULL, team_id VARCHAR(15) DEFAULT NULL, team_name VARCHAR(255) DEFAULT NULL, enterprise TINYINT(1) DEFAULT NULL, is_enterprise_install TINYINT(1) DEFAULT NULL, incoming_webhook_channel VARCHAR(255) NOT NULL, incoming_webhook_channel_id VARCHAR(15) DEFAULT NULL, incoming_webhook_configuration_url VARCHAR(255) DEFAULT NULL, incoming_webhook_url VARCHAR(255) DEFAULT NULL, last_request_success TINYINT(1) DEFAULT NULL, last_request_response VARCHAR(255) DEFAULT NULL, last_request_sent_at DATETIME DEFAULT NULL, INDEX IDX_460AC733FCF77503 (societe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE slack_access_token ADD CONSTRAINT FK_460AC733FCF77503 FOREIGN KEY (societe_id) REFERENCES societe (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE slack_access_token');
    }
}
