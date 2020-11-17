<?php

namespace App\Tests\Unit\Service;

use App\Service\TokenGenerator;
use PHPUnit\Framework\TestCase;

class TokenGeneratorTest extends TestCase
{
    public function testgenerateUrlToken()
    {
        $tokenGenerator = new TokenGenerator();

        $token = $tokenGenerator->generateUrlToken();

        $this->assertIsString($token);
        $this->assertTrue(strlen($token) > 20, 'Token is not long enough');
    }
}
