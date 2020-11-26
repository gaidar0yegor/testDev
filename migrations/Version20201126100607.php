<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201126100607 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Refactorize Cra and TempsPasse: relation TempsPasse->Cra';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('
            alter table temps_passe
            add cra_id int null
        ');
        $this->addSql('
            update temps_passe
            left join cra
                on temps_passe.mois = cra.mois
                and temps_passe.user_id = cra.user_id
            set temps_passe.cra_id = cra.id
        ');
        $this->addSql('
            delete from temps_passe
            where cra_id is null
        ');
        $this->addSql('
            alter table temps_passe
            change cra_id cra_id int not null
        ');

        $this->addSql('ALTER TABLE temps_passe DROP FOREIGN KEY FK_26A3C11CA76ED395');
        $this->addSql('DROP INDEX IDX_26A3C11CA76ED395 ON temps_passe');
        $this->addSql('ALTER TABLE temps_passe DROP mois, drop user_id');

        $this->addSql('ALTER TABLE temps_passe ADD CONSTRAINT FK_26A3C11CA62AE3BC FOREIGN KEY (cra_id) REFERENCES cra (id)');
        $this->addSql('CREATE INDEX IDX_26A3C11CA62AE3BC ON temps_passe (cra_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE temps_passe DROP FOREIGN KEY FK_26A3C11CA62AE3BC');
        $this->addSql('DROP INDEX IDX_26A3C11CA62AE3BC ON temps_passe');

        $this->addSql('
            alter table temps_passe
            add mois date null,
            add user_id int null
        ');
        $this->addSql('
            update temps_passe
            left join cra
                on temps_passe.cra_id = cra.id
            set
                temps_passe.mois = cra.mois,
                temps_passe.user_id = cra.user_id
        ');
        $this->addSql('
            alter table temps_passe
            change mois mois date not null,
            change user_id user_id int not null,
            drop cra_id
        ');

        $this->addSql('ALTER TABLE temps_passe ADD CONSTRAINT FK_26A3C11CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_26A3C11CA76ED395 ON temps_passe (user_id)');
    }
}
