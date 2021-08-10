<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210810085744 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Mails de rappels automatique pour finaliser son inscription';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE societe_user ADD notification_onboarding_enabled TINYINT(1) NOT NULL, ADD notification_onboarding_last_sent_at DATETIME DEFAULT NULL');

        $this->addSql('update societe_user set notification_onboarding_enabled = 1');

        $this->addSql('CREATE TABLE parameter (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(63) NOT NULL, value VARCHAR(255) DEFAULT NULL, help_text VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql(<<<SQL
            insert into parameter
                (name, value, help_text)
            values
                ('bo.onboarding.notification_every', '2 weeks', 'parameter.bo.onboarding.notification_every.help')
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE parameter');
        $this->addSql('ALTER TABLE societe_user DROP notification_onboarding_enabled, DROP notification_onboarding_last_sent_at');
    }
}
