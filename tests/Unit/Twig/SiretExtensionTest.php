<?php

namespace App\Tests;

use App\Twig\SiretExtension;
use PHPUnit\Framework\TestCase;

class SiretExtensionTest extends TestCase
{
    public function testFormatSiret()
    {
        $siretExtension = new SiretExtension();

        $this->assertEquals('123 456 789', $siretExtension->formatSiret('123456789'));
        $this->assertEquals('123 456 789', $siretExtension->formatSiret('123 456 789'));
        $this->assertEquals('123 456 789', $siretExtension->formatSiret(' 123 456789 '));
        $this->assertEquals('123 456 789 00019', $siretExtension->formatSiret(' 123 45678900019'));
        $this->assertEquals('123 456 789 00019', $siretExtension->formatSiret(' 123 45678900019 '));
        $this->assertEquals('123 456 789 00019', $siretExtension->formatSiret(' 123 456 789 000 19 '));
        $this->assertEquals('12', $siretExtension->formatSiret('12'));
        $this->assertEquals('123 4', $siretExtension->formatSiret('1234'));
        $this->assertEquals('123 456 789 00019 123123', $siretExtension->formatSiret('12345678900019123123'));
    }
}
