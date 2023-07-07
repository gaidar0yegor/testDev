<?php

namespace App\File;

use OpenStack\OpenStack;

class RackspaceAdapterFactory
{
    public static function create(
        string $region,
        string $userName,
        string $userPassword,
        string $containerName
    ) {
        $openstack = new OpenStack([
            'authUrl' => 'https://auth.cloud.ovh.net/v3/',
            'region' => $region,
            'user' => [
                'name' => $userName,
                'password' => $userPassword,
                'domain'   => [
                    'id' => 'default',
                ],
            ],
        ]);

        return $openstack
            ->objectStoreV1()
            ->getContainer($containerName)
        ;
    }
}
