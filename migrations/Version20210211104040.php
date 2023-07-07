<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210211104040 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add relation projet->societe';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE projet ADD societe_id INT NOT NULL');

        $this->addSql('
            update projet
            left join projet_participant
                on projet_participant.projet_id = projet.id
                and projet_participant.role = "CDP"
            left join user
                on projet_participant.user_id = user.id
            set
                projet.societe_id = user.societe_id
        ');

        $this->addSql('ALTER TABLE projet ADD CONSTRAINT FK_50159CA9FCF77503 FOREIGN KEY (societe_id) REFERENCES societe (id)');
        $this->addSql('CREATE INDEX IDX_50159CA9FCF77503 ON projet (societe_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE projet DROP FOREIGN KEY FK_50159CA9FCF77503');
        $this->addSql('DROP INDEX IDX_50159CA9FCF77503 ON projet');
        $this->addSql('ALTER TABLE projet DROP societe_id');
    }
}
