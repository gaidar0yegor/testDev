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

    public function testgenerateUrlTokenWithGivenSize()
    {
        $tokenGenerator = new TokenGenerator();

        $token = $tokenGenerator->generateUrlToken(16);

        $this->assertIsString($token);
        $this->assertEquals(16, strlen($token), 'Token has not the needed size');
    }
}
