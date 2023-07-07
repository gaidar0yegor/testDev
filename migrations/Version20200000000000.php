<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200000000000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Initial migration.';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cra (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, mois DATE NOT NULL, jours LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', INDEX IDX_926CE6D1A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fait_marquant (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, projet_id INT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date DATETIME NOT NULL, INDEX IDX_36D0F5A5B03A8386 (created_by_id), INDEX IDX_36D0F5A5C18272 (projet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fichier_projet (id INT AUTO_INCREMENT NOT NULL, uploaded_by_id INT DEFAULT NULL, projet_id INT NOT NULL, nom_md5 VARCHAR(255) NOT NULL, nom_fichier VARCHAR(255) NOT NULL, date_upload DATE NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_78207D83A2B28FE8 (uploaded_by_id), INDEX IDX_78207D83C18272 (projet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projet (id INT AUTO_INCREMENT NOT NULL, statut_projet_id INT NOT NULL, titre VARCHAR(255) NOT NULL, resume LONGTEXT DEFAULT NULL, date_debut DATE NOT NULL, date_fin DATE DEFAULT NULL, statut_rdi INT DEFAULT NULL, projet_collaboratif TINYINT(1) NOT NULL, projet_ppp TINYINT(1) NOT NULL, acronyme VARCHAR(255) NOT NULL, projet_interne TINYINT(1) DEFAULT NULL, INDEX IDX_50159CA9A16F17B0 (statut_projet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projet_participant (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, projet_id INT NOT NULL, date_ajout DATE NOT NULL, role VARCHAR(15) NOT NULL, INDEX IDX_CA53F2FA76ED395 (user_id), INDEX IDX_CA53F2FC18272 (projet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE societe (id INT AUTO_INCREMENT NOT NULL, statut_id INT DEFAULT NULL, raison_sociale VARCHAR(45) NOT NULL, siret VARCHAR(45) DEFAULT NULL, heures_par_jours NUMERIC(5, 3) DEFAULT NULL, INDEX IDX_19653DBDF6203804 (statut_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE societe_statut (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(45) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE statut_projet (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE temps_passe (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, projet_id INT NOT NULL, mois DATE NOT NULL, pourcentage INT NOT NULL, INDEX IDX_26A3C11CA76ED395 (user_id), INDEX IDX_26A3C11CC18272 (projet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, societe_id INT DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) DEFAULT NULL, nom VARCHAR(63) DEFAULT NULL, prenom VARCHAR(63) DEFAULT NULL, email VARCHAR(255) NOT NULL, invitation_token VARCHAR(255) DEFAULT NULL, reset_password_token VARCHAR(255) DEFAULT NULL, reset_password_token_expires_at DATETIME DEFAULT NULL, telephone VARCHAR(45) DEFAULT NULL, heures_par_jours NUMERIC(5, 3) DEFAULT NULL, created_at DATETIME NOT NULL, enabled TINYINT(1) NOT NULL, cadre TINYINT(1) DEFAULT NULL, INDEX IDX_8D93D649FCF77503 (societe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cra ADD CONSTRAINT FK_926CE6D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE fait_marquant ADD CONSTRAINT FK_36D0F5A5B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE fait_marquant ADD CONSTRAINT FK_36D0F5A5C18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
        $this->addSql('ALTER TABLE fichier_projet ADD CONSTRAINT FK_78207D83A2B28FE8 FOREIGN KEY (uploaded_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE fichier_projet ADD CONSTRAINT FK_78207D83C18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
        $this->addSql('ALTER TABLE projet ADD CONSTRAINT FK_50159CA9A16F17B0 FOREIGN KEY (statut_projet_id) REFERENCES statut_projet (id)');
        $this->addSql('ALTER TABLE projet_participant ADD CONSTRAINT FK_CA53F2FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE projet_participant ADD CONSTRAINT FK_CA53F2FC18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
        $this->addSql('ALTER TABLE societe ADD CONSTRAINT FK_19653DBDF6203804 FOREIGN KEY (statut_id) REFERENCES societe_statut (id)');
        $this->addSql('ALTER TABLE temps_passe ADD CONSTRAINT FK_26A3C11CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE temps_passe ADD CONSTRAINT FK_26A3C11CC18272 FOREIGN KEY (projet_id) REFERENCES projet (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649FCF77503 FOREIGN KEY (societe_id) REFERENCES societe (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fait_marquant DROP FOREIGN KEY FK_36D0F5A5C18272');
        $this->addSql('ALTER TABLE fichier_projet DROP FOREIGN KEY FK_78207D83C18272');
        $this->addSql('ALTER TABLE projet_participant DROP FOREIGN KEY FK_CA53F2FC18272');
        $this->addSql('ALTER TABLE temps_passe DROP FOREIGN KEY FK_26A3C11CC18272');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649FCF77503');
        $this->addSql('ALTER TABLE societe DROP FOREIGN KEY FK_19653DBDF6203804');
        $this->addSql('ALTER TABLE projet DROP FOREIGN KEY FK_50159CA9A16F17B0');
        $this->addSql('ALTER TABLE cra DROP FOREIGN KEY FK_926CE6D1A76ED395');
        $this->addSql('ALTER TABLE fait_marquant DROP FOREIGN KEY FK_36D0F5A5B03A8386');
        $this->addSql('ALTER TABLE fichier_projet DROP FOREIGN KEY FK_78207D83A2B28FE8');
        $this->addSql('ALTER TABLE projet_participant DROP FOREIGN KEY FK_CA53F2FA76ED395');
        $this->addSql('ALTER TABLE temps_passe DROP FOREIGN KEY FK_26A3C11CA76ED395');
        $this->addSql('DROP TABLE cra');
        $this->addSql('DROP TABLE fait_marquant');
        $this->addSql('DROP TABLE fichier_projet');
        $this->addSql('DROP TABLE projet');
        $this->addSql('DROP TABLE projet_participant');
        $this->addSql('DROP TABLE societe');
        $this->addSql('DROP TABLE societe_statut');
        $this->addSql('DROP TABLE statut_projet');
        $this->addSql('DROP TABLE temps_passe');
        $this->addSql('DROP TABLE user');
    }
}
