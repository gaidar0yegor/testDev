<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210315111333 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add user notifications (alarm icon)';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, activity_id INT NOT NULL, acknowledged TINYINT(1) NOT NULL, INDEX IDX_3F980AC8A76ED395 (user_id), INDEX IDX_3F980AC881C06096 (activity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_notification ADD CONSTRAINT FK_3F980AC8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_notification ADD CONSTRAINT FK_3F980AC881C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');

        // Fill user notifications with latest fait_marquant of 2 last months
        $this->addSql(<<<SQL
            insert into user_notification(
                user_id,
                activity_id,
                acknowledged
            )
            select
                user.id,
                activity.id,
                datediff(now(), activity.datetime) > 30
            from user
            left join projet_participant on user.id = projet_participant.user_id
            left join projet on projet_participant.projet_id = projet.id
            join activity
                on activity.type = 'fait_marquant_created'
                and activity.parameters like concat('%"projet":', projet.id, ',%')
                and activity.parameters not like concat('%"createdBy":', user.id, ',%')
                and datediff(now(), activity.datetime) < 60
        SQL);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_notification');
    }
}
