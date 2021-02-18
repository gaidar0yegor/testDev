<?php

namespace App\Service;


use App\Repository\UserRepository;


class UserHasActivate
{
    public function getIfThereIsToken(UserRepository $userRepo) {

        $userToken = $userRepo->findBy(['invitationToken' => 'null']);

        return $userToken;
    }
}