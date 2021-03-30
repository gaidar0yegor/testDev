<?php

namespace App;

use App\Entity\Projet;
use App\Entity\SocieteUser;

/**
 * Représente une ressource d'un projet.
 * Exemple : fait marquant, fichier...
 *
 * Une ressource a un possesseur, et est liée à un projet.
 */
interface ProjetResourceInterface
{
    public const CREATE = 'projet_resource_create';
    public const VIEW = 'projet_resource_view';
    public const EDIT = 'projet_resource_edit';
    public const DELETE = 'projet_resource_delete';

    public function getOwner(): SocieteUser;

    public function getProjet(): Projet;
}
