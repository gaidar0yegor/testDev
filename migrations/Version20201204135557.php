<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201204135557 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Refactor FichierProjet entity to create independant Fichier entity';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE fichier (id INT AUTO_INCREMENT NOT NULL, nom_md5 VARCHAR(255) NOT NULL, nom_fichier VARCHAR(255) NOT NULL, date_upload DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('
            alter table fichier_projet
                add fichier_id int not null,
                change uploaded_by_id uploaded_by_id int not null
        ');
        $this->addSql('
            insert into fichier(nom_md5, nom_fichier, date_upload)
                select nom_md5, nom_fichier, date_upload
                from fichier_projet
        ');
        $this->addSql('
            update fichier_projet
                left join fichier on fichier.nom_md5 = fichier_projet.nom_md5
                set fichier_projet.fichier_id = fichier.id
        ');
        $this->addSql('
            alter table fichier_projet
                drop nom_md5,
                drop nom_fichier,
                drop date_upload
        ');

        $this->addSql('ALTER TABLE fichier_projet ADD CONSTRAINT FK_78207D83F915CFE FOREIGN KEY (fichier_id) REFERENCES fichier (id)');
        $this->addSql('CREATE INDEX IDX_78207D83F915CFE ON fichier_projet (fichier_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE fichier_projet DROP FOREIGN KEY FK_78207D83F915CFE');

        $this->addSql('
            alter table fichier_projet
                add nom_md5 varchar(255) character set utf8mb4 not null collate `utf8mb4_unicode_ci`,
                add nom_fichier varchar(255) character set utf8mb4 not null collate `utf8mb4_unicode_ci`,
                add date_upload date null,
                change uploaded_by_id uploaded_by_id int default null
        ');
        $this->addSql('
            update fichier_projet
                left join fichier on fichier.id = fichier_projet.fichier_id
            set
                fichier_projet.nom_md5 = fichier.nom_md5,
                fichier_projet.nom_fichier = fichier.nom_fichier,
                fichier_projet.date_upload = fichier.date_upload
        ');
        $this->addSql('
            alter table fichier_projet
                change date_upload date_upload date not null
        ');

        $this->addSql('
            alter table fichier_projet
                drop fichier_id
        ');

        $this->addSql('DROP TABLE fichier');
    }
}
