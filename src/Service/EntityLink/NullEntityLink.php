<?php

namespace App\Service\EntityLink;

class NullEntityLink extends EntityLink
{
    public function __construct()
    {
        parent::__construct('', '');
    }

    public function __toString()
    {
        return '<i>supprimé</i>';
    }
}
