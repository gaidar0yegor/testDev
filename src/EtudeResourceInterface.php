<?php

namespace App;

use App\Entity\LabApp\Etude;
use App\Entity\LabApp\UserBook;

/**
 * Représente une ressource d'une étude.
 * Exemple : notes, fichier...
 *
 * Une ressource a un possesseur, et est liée à une étude.
 */
interface EtudeResourceInterface
{
    public const CREATE = 'etude_resource_create';
    public const VIEW = 'etude_resource_view';
    public const EDIT = 'etude_resource_edit';
    public const DELETE = 'etude_resource_delete';

    public function getOwner(): UserBook;

    public function getEtude(): Etude;
}
