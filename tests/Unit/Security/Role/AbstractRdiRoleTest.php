<?php

namespace App\Tests\Unit\Security\Role;

use App\Security\Exception\UnknownRoleException;
use App\Security\Role\AbstractRdiRole;
use PHPUnit\Framework\TestCase;

class AbstractRdiRoleTest extends TestCase
{
    public function testHasRole(): void
    {
        $testRoles = new class extends AbstractRdiRole
        {
            public static array $allRoles = [
                'USER',
                'ADMIN',
                'SUPERADMIN',
            ];
        };

        $this->assertTrue($testRoles::hasRole('USER', 'USER'));
        $this->assertTrue($testRoles::hasRole('ADMIN', 'USER'));
        $this->assertTrue($testRoles::hasRole('SUPERADMIN', 'USER'));

        $this->assertFalse($testRoles::hasRole('USER', 'ADMIN'));
        $this->assertTrue($testRoles::hasRole('ADMIN', 'ADMIN'));
        $this->assertTrue($testRoles::hasRole('SUPERADMIN', 'ADMIN'));

        $this->assertFalse($testRoles::hasRole('USER', 'SUPERADMIN'));
        $this->assertFalse($testRoles::hasRole('ADMIN', 'SUPERADMIN'));
        $this->assertTrue($testRoles::hasRole('SUPERADMIN', 'SUPERADMIN'));
    }

    public function testCheckRole(): void
    {
        $testRoles = new class extends AbstractRdiRole
        {
            public static array $allRoles = [
                'USER',
                'ADMIN',
                'SUPERADMIN',
            ];
        };

        $testRoles::checkRole('ADMIN');

        $this->expectException(UnknownRoleException::class);

        $testRoles::checkRole('WTF');
    }
}
