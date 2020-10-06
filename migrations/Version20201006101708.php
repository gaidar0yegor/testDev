<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201006101708 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE base_temps_par_contrat (id INT AUTO_INCREMENT NOT NULL, libelle_contrat VARCHAR(255) NOT NULL, cadre_nb_heures_mensuelles DOUBLE PRECISION NOT NULL, non_cadre_nb_heures_mensuelles DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE faits_marquants (id INT AUTO_INCREMENT NOT NULL, projets_id INT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, created_at DATETIME NOT NULL, created_by VARCHAR(45) NOT NULL, INDEX IDX_5F6A2394597A6CB7 (projets_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fichiers_projet (id INT AUTO_INCREMENT NOT NULL, projets_id INT NOT NULL, chemin_fichier VARCHAR(255) NOT NULL, nom_fichier VARCHAR(255) NOT NULL, nom_uploader VARCHAR(45) NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_DC6CF004597A6CB7 (projets_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jours_absence (id INT AUTO_INCREMENT NOT NULL, users_id INT NOT NULL, date_jour DATE NOT NULL, journee_entiere TINYINT(1) NOT NULL, INDEX IDX_B2C9835A67B3B43D (users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jours_feries (id INT AUTO_INCREMENT NOT NULL, date_jour DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE licences (id INT AUTO_INCREMENT NOT NULL, societes_id INT NOT NULL, statut VARBINARY(255) NOT NULL, date_activation DATE NOT NULL, date_desactivation DATE DEFAULT NULL, cle VARCHAR(255) NOT NULL, INDEX IDX_6314AC4F7E841BEA (societes_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participants_projet (id INT AUTO_INCREMENT NOT NULL, users_id INT NOT NULL, projets_id INT NOT NULL, roles_participant_projet_id INT NOT NULL, date_ajout DATE NOT NULL, INDEX IDX_9DF2C33B67B3B43D (users_id), INDEX IDX_9DF2C33B597A6CB7 (projets_id), INDEX IDX_9DF2C33BE07913CF (roles_participant_projet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profils_utilisateur (id INT AUTO_INCREMENT NOT NULL, back_office TINYINT(1) NOT NULL, libelle VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projets (id INT AUTO_INCREMENT NOT NULL, statuts_projet_id INT NOT NULL, titre VARCHAR(255) NOT NULL, resume LONGTEXT DEFAULT NULL, date_debut DATE NOT NULL, date_fin DATE DEFAULT NULL, statut_rdi INT NOT NULL, projet_collaboratif TINYINT(1) NOT NULL, projet_ppp TINYINT(1) NOT NULL, acronyme VARCHAR(255) NOT NULL, INDEX IDX_B454C1DBB4103376 (statuts_projet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referentiel_rdi (id INT AUTO_INCREMENT NOT NULL, mot_cle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE roles_participant_projet (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE societes (id INT AUTO_INCREMENT NOT NULL, statuts_societe_id INT DEFAULT NULL, raison_sociale VARCHAR(45) NOT NULL, siret VARCHAR(45) NOT NULL, nb_licences INT NOT NULL, nb_licences_dispo INT NOT NULL, chemin_logo VARCHAR(255) DEFAULT NULL, nom_logo VARCHAR(255) DEFAULT NULL, INDEX IDX_AEC76507FB2E60AB (statuts_societe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE statuts_projet (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE statuts_rdi_du_projet (id INT AUTO_INCREMENT NOT NULL, libelle_statut_rdi_projet VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE statuts_societe (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(45) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE statuts_utilisateur (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE temps_passe (id INT AUTO_INCREMENT NOT NULL, users_id INT NOT NULL, projets_id INT NOT NULL, pourcent_sur_le_mois INT DEFAULT NULL, date_de_saisie DATE NOT NULL, INDEX IDX_26A3C11C67B3B43D (users_id), INDEX IDX_26A3C11C597A6CB7 (projets_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, licences_id INT DEFAULT NULL, societes_id INT NOT NULL, profils_utilisateur_id INT NOT NULL, statuts_utilisateur_id INT NOT NULL, base_temps_par_contrat_id INT NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, nom VARCHAR(45) NOT NULL, prenom VARCHAR(45) NOT NULL, email VARCHAR(45) NOT NULL, telephone VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, deleted_by VARCHAR(45) DEFAULT NULL, cadre TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_1483A5E95EF2836 (licences_id), INDEX IDX_1483A5E97E841BEA (societes_id), INDEX IDX_1483A5E9D5747720 (profils_utilisateur_id), INDEX IDX_1483A5E9A7245ACB (statuts_utilisateur_id), INDEX IDX_1483A5E9C8A30624 (base_temps_par_contrat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE faits_marquants ADD CONSTRAINT FK_5F6A2394597A6CB7 FOREIGN KEY (projets_id) REFERENCES projets (id)');
        $this->addSql('ALTER TABLE fichiers_projet ADD CONSTRAINT FK_DC6CF004597A6CB7 FOREIGN KEY (projets_id) REFERENCES projets (id)');
        $this->addSql('ALTER TABLE jours_absence ADD CONSTRAINT FK_B2C9835A67B3B43D FOREIGN KEY (users_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE licences ADD CONSTRAINT FK_6314AC4F7E841BEA FOREIGN KEY (societes_id) REFERENCES societes (id)');
        $this->addSql('ALTER TABLE participants_projet ADD CONSTRAINT FK_9DF2C33B67B3B43D FOREIGN KEY (users_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE participants_projet ADD CONSTRAINT FK_9DF2C33B597A6CB7 FOREIGN KEY (projets_id) REFERENCES projets (id)');
        $this->addSql('ALTER TABLE participants_projet ADD CONSTRAINT FK_9DF2C33BE07913CF FOREIGN KEY (roles_participant_projet_id) REFERENCES roles_participant_projet (id)');
        $this->addSql('ALTER TABLE projets ADD CONSTRAINT FK_B454C1DBB4103376 FOREIGN KEY (statuts_projet_id) REFERENCES statuts_projet (id)');
        $this->addSql('ALTER TABLE societes ADD CONSTRAINT FK_AEC76507FB2E60AB FOREIGN KEY (statuts_societe_id) REFERENCES statuts_societe (id)');
        $this->addSql('ALTER TABLE temps_passe ADD CONSTRAINT FK_26A3C11C67B3B43D FOREIGN KEY (users_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE temps_passe ADD CONSTRAINT FK_26A3C11C597A6CB7 FOREIGN KEY (projets_id) REFERENCES projets (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E95EF2836 FOREIGN KEY (licences_id) REFERENCES licences (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E97E841BEA FOREIGN KEY (societes_id) REFERENCES societes (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D5747720 FOREIGN KEY (profils_utilisateur_id) REFERENCES profils_utilisateur (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9A7245ACB FOREIGN KEY (statuts_utilisateur_id) REFERENCES statuts_utilisateur (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9C8A30624 FOREIGN KEY (base_temps_par_contrat_id) REFERENCES base_temps_par_contrat (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9C8A30624');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E95EF2836');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9D5747720');
        $this->addSql('ALTER TABLE faits_marquants DROP FOREIGN KEY FK_5F6A2394597A6CB7');
        $this->addSql('ALTER TABLE fichiers_projet DROP FOREIGN KEY FK_DC6CF004597A6CB7');
        $this->addSql('ALTER TABLE participants_projet DROP FOREIGN KEY FK_9DF2C33B597A6CB7');
        $this->addSql('ALTER TABLE temps_passe DROP FOREIGN KEY FK_26A3C11C597A6CB7');
        $this->addSql('ALTER TABLE participants_projet DROP FOREIGN KEY FK_9DF2C33BE07913CF');
        $this->addSql('ALTER TABLE licences DROP FOREIGN KEY FK_6314AC4F7E841BEA');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E97E841BEA');
        $this->addSql('ALTER TABLE projets DROP FOREIGN KEY FK_B454C1DBB4103376');
        $this->addSql('ALTER TABLE societes DROP FOREIGN KEY FK_AEC76507FB2E60AB');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9A7245ACB');
        $this->addSql('ALTER TABLE jours_absence DROP FOREIGN KEY FK_B2C9835A67B3B43D');
        $this->addSql('ALTER TABLE participants_projet DROP FOREIGN KEY FK_9DF2C33B67B3B43D');
        $this->addSql('ALTER TABLE temps_passe DROP FOREIGN KEY FK_26A3C11C67B3B43D');
        $this->addSql('DROP TABLE base_temps_par_contrat');
        $this->addSql('DROP TABLE faits_marquants');
        $this->addSql('DROP TABLE fichiers_projet');
        $this->addSql('DROP TABLE jours_absence');
        $this->addSql('DROP TABLE jours_feries');
        $this->addSql('DROP TABLE licences');
        $this->addSql('DROP TABLE participants_projet');
        $this->addSql('DROP TABLE profils_utilisateur');
        $this->addSql('DROP TABLE projets');
        $this->addSql('DROP TABLE referentiel_rdi');
        $this->addSql('DROP TABLE roles_participant_projet');
        $this->addSql('DROP TABLE societes');
        $this->addSql('DROP TABLE statuts_projet');
        $this->addSql('DROP TABLE statuts_rdi_du_projet');
        $this->addSql('DROP TABLE statuts_societe');
        $this->addSql('DROP TABLE statuts_utilisateur');
        $this->addSql('DROP TABLE temps_passe');
        $this->addSql('DROP TABLE users');
    }
}
