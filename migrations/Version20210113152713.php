<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210113152713 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity (id INT AUTO_INCREMENT NOT NULL, datetime DATETIME NOT NULL, type VARCHAR(31) NOT NULL, parameters LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projet_activity (id INT AUTO_INCREMENT NOT NULL, projet_id INT NOT NULL, activity_id INT NOT NULL, INDEX IDX_8BF462ADC18272 (projet_id), INDEX IDX_8BF462AD81C06096 (activity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_activity (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, activity_id INT NOT NULL, INDEX IDX_4CF9ED5AA76ED395 (user_id), INDEX IDX_4CF9ED5A81C06096 (activity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE projet_activity ADD CONSTRAINT FK_8BF462ADC18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
        $this->addSql('ALTER TABLE projet_activity ADD CONSTRAINT FK_8BF462AD81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE user_activity ADD CONSTRAINT FK_4CF9ED5AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_activity ADD CONSTRAINT FK_4CF9ED5A81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');

        // Prefill 'fait_marquant_created' activities
        $this->addSql("
            insert into activity (datetime, type, parameters)
            select
                date,
                'fait_marquant_created',
                concat('{\"projet\":', projet_id, ',\"createdBy\":', created_by_id, ',\"faitMarquant\":', id, '}')
            from fait_marquant
        ");
        $this->addSql("
            insert into projet_activity(projet_id, activity_id)
            select json_value(parameters, '$.projet'), id
            from activity
            where type = 'fait_marquant_created'
        ");
        $this->addSql("
            insert into user_activity(user_id, activity_id)
            select json_value(parameters, '$.createdBy'), id
            from activity
            where type = 'fait_marquant_created'
        ");

        // Prefill 'projet_created' activities
        $this->addSql("
            insert into activity (datetime, type, parameters)
            select
                date_debut,
                'projet_created',
                concat('{\"projet\":', projet.id, ',\"createdBy\":', user.id, '}')
            from projet
            left join projet_participant on projet_participant.projet_id = projet.id
            left join user on projet_participant.user_id = user.id
            where
                projet_participant.role = 'CDP'
                and date_debut is not null
        ");
        $this->addSql("
            insert into projet_activity(projet_id, activity_id)
            select json_value(parameters, '$.projet'), id
            from activity
            where type = 'projet_created'
        ");
        $this->addSql("
            insert into user_activity(user_id, activity_id)
            select json_value(parameters, '$.createdBy'), id
            from activity
            where type = 'projet_created'
        ");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE projet_activity DROP FOREIGN KEY FK_8BF462AD81C06096');
        $this->addSql('ALTER TABLE user_activity DROP FOREIGN KEY FK_4CF9ED5A81C06096');
        $this->addSql('DROP TABLE activity');
        $this->addSql('DROP TABLE projet_activity');
        $this->addSql('DROP TABLE user_activity');
    }
}
