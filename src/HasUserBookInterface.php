<?php

namespace App;

use App\Entity\LabApp\UserBook;

interface HasUserBookInterface
{
    public function getUserBook(): ?UserBook;
}
