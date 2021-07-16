<?php

namespace App\File;

use App\Entity\Fichier;

interface FileHandlerInterface
{
    public function upload(Fichier $fichier): void;
}
