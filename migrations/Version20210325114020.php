<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210325114020 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Multi-societe';
    }

    public function up(Schema $schema) : void
    {
        // Fix created_at dates set to 0
        $this->addSql('update user set created_at = "2020-01-01 00:00:00" where not created_at');

        // Split User to User and SocieteUser
        $this->addSql(<<<SQL
            create table societe_user (
                id int auto_increment not null,
                societe_id int default null,
                user_id int default null,
                role varchar(31) not null,
                heures_par_jours numeric(5, 3) default null,
                date_entree date default null,
                date_sortie date default null,
                created_at datetime not null,
                enabled tinyint(1) not null,
                invitation_token varchar(255) default null,
                invitation_sent_at datetime default null,
                invitation_email varchar(255) default null,
                index IDX_EFBCEA58FCF77503 (societe_id),
                index IDX_EFBCEA58A76ED395 (user_id),
                primary key(id)
            ) default character set utf8mb4 collate `utf8mb4_unicode_ci` engine = innodb
        SQL);

        $this->addSql(<<<SQL
            insert into societe_user (
                id,
                societe_id,
                user_id,
                role,
                heures_par_jours,
                date_entree,
                date_sortie,
                created_at,
                enabled,
                invitation_token,
                invitation_sent_at,
                invitation_email
            )
            select
                id,
                societe_id,
                if (password is null, null, id),
                case
                    when roles like '%ROLE_FO_ADMIN%' then 'SOCIETE_ADMIN'
                    when roles like '%ROLE_FO_CDP%' then 'SOCIETE_CDP'
                    when roles like '%ROLE_FO_USER%' then 'SOCIETE_USER'
                end,
                heures_par_jours,
                date_entree,
                date_sortie,
                if (created_at, created_at, '2020-01-01 00:00:00'),
                enabled,
                invitation_token,
                invitation_sent_at,
                if (invitation_token is null, null, email)
            from user
            where
                roles like '%ROLE_FO_%'
        SQL);

        $this->addSql('update user set roles = replace(roles, "ROLE_FO_ADMIN", "ROLE_FO_USER")');
        $this->addSql('update user set roles = replace(roles, "ROLE_FO_CDP", "ROLE_FO_USER")');

        // Make all user accounts enabled, even the ones that have been disabled on their societe
        $this->addSql('update user set enabled = true');

        $this->addSql('alter table projet_participant change role role varchar(31)');
        $this->addSql('update projet_participant set role = concat("PROJET_", role)');

        $this->addSql('ALTER TABLE societe_user ADD CONSTRAINT FK_EFBCEA58FCF77503 FOREIGN KEY (societe_id) REFERENCES societe (id)');
        $this->addSql('ALTER TABLE societe_user ADD CONSTRAINT FK_EFBCEA58A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');

        $this->addSql('ALTER TABLE user DROP invitation_token, DROP heures_par_jours, DROP invitation_sent_at, DROP date_entree, DROP date_sortie, CHANGE societe_id current_societe_user_id INT DEFAULT NULL');
        $this->addSql('update user set current_societe_user_id = null');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64925548414 FOREIGN KEY (current_societe_user_id) REFERENCES societe_user (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64925548414 ON user (current_societe_user_id)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649FCF77503');
        $this->addSql('DROP INDEX IDX_8D93D649FCF77503 ON user');

        $this->addSql('alter table user_notification rename to societe_user_notification');
        $this->addSql('ALTER TABLE societe_user_notification DROP FOREIGN KEY FK_3F980AC8A76ED395');
        $this->addSql('DROP INDEX IDX_3F980AC8A76ED395 ON societe_user_notification');
        $this->addSql('ALTER TABLE societe_user_notification DROP FOREIGN KEY FK_3F980AC881C06096');
        $this->addSql('ALTER TABLE societe_user_notification CHANGE user_id societe_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE societe_user_notification ADD CONSTRAINT FK_875CA14E62A85E16 FOREIGN KEY (societe_user_id) REFERENCES societe_user (id)');
        $this->addSql('CREATE INDEX IDX_875CA14E62A85E16 ON societe_user_notification (societe_user_id)');
        $this->addSql('DROP INDEX idx_3f980ac881c06096 ON societe_user_notification');
        $this->addSql('CREATE INDEX IDX_875CA14E81C06096 ON societe_user_notification (activity_id)');
        $this->addSql('ALTER TABLE societe_user_notification ADD CONSTRAINT FK_3F980AC881C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON UPDATE NO ACTION ON DELETE NO ACTION');

        $this->addSql('alter table user_activity rename to societe_user_activity');
        $this->addSql('ALTER TABLE societe_user_activity DROP FOREIGN KEY FK_4CF9ED5AA76ED395');
        $this->addSql('DROP INDEX IDX_4CF9ED5AA76ED395 ON societe_user_activity');
        $this->addSql('ALTER TABLE societe_user_activity DROP FOREIGN KEY FK_4CF9ED5A81C06096');
        $this->addSql('ALTER TABLE societe_user_activity CHANGE user_id societe_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE societe_user_activity ADD CONSTRAINT FK_59E91A7E62A85E16 FOREIGN KEY (societe_user_id) REFERENCES societe_user (id)');
        $this->addSql('CREATE INDEX IDX_59E91A7E62A85E16 ON societe_user_activity (societe_user_id)');
        $this->addSql('DROP INDEX idx_4cf9ed5a81c06096 ON societe_user_activity');
        $this->addSql('CREATE INDEX IDX_59E91A7E81C06096 ON societe_user_activity (activity_id)');
        $this->addSql('ALTER TABLE societe_user_activity ADD CONSTRAINT FK_4CF9ED5A81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON UPDATE NO ACTION ON DELETE NO ACTION');

        $this->addSql('ALTER TABLE fait_marquant DROP FOREIGN KEY FK_36D0F5A5B03A8386');
        $this->addSql('ALTER TABLE fait_marquant ADD CONSTRAINT FK_36D0F5A5B03A8386 FOREIGN KEY (created_by_id) REFERENCES societe_user (id)');
        $this->addSql('ALTER TABLE projet_participant DROP FOREIGN KEY FK_CA53F2FA76ED395');
        $this->addSql('DROP INDEX IDX_CA53F2FA76ED395 ON projet_participant');
        $this->addSql('ALTER TABLE projet_participant CHANGE role role VARCHAR(31) NOT NULL, CHANGE user_id societe_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE projet_participant ADD CONSTRAINT FK_CA53F2F62A85E16 FOREIGN KEY (societe_user_id) REFERENCES societe_user (id)');
        $this->addSql('CREATE INDEX IDX_CA53F2F62A85E16 ON projet_participant (societe_user_id)');
        $this->addSql('ALTER TABLE cra DROP FOREIGN KEY FK_926CE6D1A76ED395');
        $this->addSql('DROP INDEX IDX_926CE6D1A76ED395 ON cra');
        $this->addSql('ALTER TABLE cra CHANGE user_id societe_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE cra ADD CONSTRAINT FK_926CE6D162A85E16 FOREIGN KEY (societe_user_id) REFERENCES societe_user (id)');
        $this->addSql('CREATE INDEX IDX_926CE6D162A85E16 ON cra (societe_user_id)');
        $this->addSql('ALTER TABLE fichier_projet DROP FOREIGN KEY FK_78207D83A2B28FE8');
        $this->addSql('ALTER TABLE fichier_projet ADD CONSTRAINT FK_78207D83A2B28FE8 FOREIGN KEY (uploaded_by_id) REFERENCES societe_user (id)');
    }

    public function down(Schema $schema) : void
    {
        // Reverting Multi-societe is not supported.
    }
}
