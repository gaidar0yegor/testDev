<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\File\FileHandler\ProjectFileHandler;
use App\MultiSociete\UserContext;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211221133920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
            INSERT INTO fichier_projet_societe_user (fichier_projet_id, societe_user_id) 
            SELECT fichier_projet.id as fichier_projet_id, projet_participant.societe_user_id as societe_user_id FROM fichier_projet 
            JOIN projet ON projet.id = fichier_projet.projet_id
            JOIN projet_participant ON projet_participant.projet_id = projet.id;
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('TRUNCATE TABLE fichier_projet_societe_user');
    }
}
