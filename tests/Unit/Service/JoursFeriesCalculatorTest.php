<?php

namespace App\Tests\Unit\Service;

use App\Service\JoursFeriesCalculator;
use PHPUnit\Framework\TestCase;

class JoursFeriesCalculatorTest extends TestCase
{
    public function testCalcJoursFeries2020()
    {
        $joursFeriesCalculator = new JoursFeriesCalculator();

        $joursFeries = $joursFeriesCalculator->calcJoursFeries(2020);

        $this->assertEquals(11, count($joursFeries));

        $this->assertEquals('2020-01-01', $joursFeries[0]->format('Y-m-d'));
        $this->assertEquals('2020-04-13', $joursFeries[1]->format('Y-m-d'));
        $this->assertEquals('2020-05-01', $joursFeries[2]->format('Y-m-d'));
        $this->assertEquals('2020-05-08', $joursFeries[3]->format('Y-m-d'));
        $this->assertEquals('2020-05-21', $joursFeries[4]->format('Y-m-d'));
        $this->assertEquals('2020-06-01', $joursFeries[5]->format('Y-m-d'));
        $this->assertEquals('2020-07-14', $joursFeries[6]->format('Y-m-d'));
        $this->assertEquals('2020-08-15', $joursFeries[7]->format('Y-m-d'));
        $this->assertEquals('2020-11-01', $joursFeries[8]->format('Y-m-d'));
        $this->assertEquals('2020-11-11', $joursFeries[9]->format('Y-m-d'));
        $this->assertEquals('2020-12-25', $joursFeries[10]->format('Y-m-d'));
    }

    public function testCalcJoursFeriesFiltersByMonth()
    {
        $joursFeriesCalculator = new JoursFeriesCalculator();

        $joursFeries = $joursFeriesCalculator->calcJoursFeries(2020, 5);

        $this->assertEquals(3, count($joursFeries));

        $this->assertEquals('2020-05-01', $joursFeries[0]->format('Y-m-d'));
        $this->assertEquals('2020-05-08', $joursFeries[1]->format('Y-m-d'));
        $this->assertEquals('2020-05-21', $joursFeries[2]->format('Y-m-d'));
    }

    public function testCalcJoursFeries2021()
    {
        $joursFeriesCalculator = new JoursFeriesCalculator();

        $joursFeries = $joursFeriesCalculator->calcJoursFeries(2021);

        $this->assertEquals(11, count($joursFeries));

        $this->assertEquals('2021-01-01', $joursFeries[0]->format('Y-m-d'));
        $this->assertEquals('2021-04-05', $joursFeries[1]->format('Y-m-d'));
        $this->assertEquals('2021-05-01', $joursFeries[2]->format('Y-m-d'));
        $this->assertEquals('2021-05-08', $joursFeries[3]->format('Y-m-d'));
        $this->assertEquals('2021-05-13', $joursFeries[4]->format('Y-m-d'));
        $this->assertEquals('2021-05-24', $joursFeries[5]->format('Y-m-d'));
        $this->assertEquals('2021-07-14', $joursFeries[6]->format('Y-m-d'));
        $this->assertEquals('2021-08-15', $joursFeries[7]->format('Y-m-d'));
        $this->assertEquals('2021-11-01', $joursFeries[8]->format('Y-m-d'));
        $this->assertEquals('2021-11-11', $joursFeries[9]->format('Y-m-d'));
        $this->assertEquals('2021-12-25', $joursFeries[10]->format('Y-m-d'));
    }

    public function testCalcJoursFeries2022()
    {
        $joursFeriesCalculator = new JoursFeriesCalculator();

        $joursFeries = $joursFeriesCalculator->calcJoursFeries(2022);

        $this->assertEquals(11, count($joursFeries));

        $this->assertEquals('2022-01-01', $joursFeries[0]->format('Y-m-d'));
        $this->assertEquals('2022-04-18', $joursFeries[1]->format('Y-m-d'));
        $this->assertEquals('2022-05-01', $joursFeries[2]->format('Y-m-d'));
        $this->assertEquals('2022-05-08', $joursFeries[3]->format('Y-m-d'));
        $this->assertEquals('2022-05-26', $joursFeries[4]->format('Y-m-d'));
        $this->assertEquals('2022-06-06', $joursFeries[5]->format('Y-m-d'));
        $this->assertEquals('2022-07-14', $joursFeries[6]->format('Y-m-d'));
        $this->assertEquals('2022-08-15', $joursFeries[7]->format('Y-m-d'));
        $this->assertEquals('2022-11-01', $joursFeries[8]->format('Y-m-d'));
        $this->assertEquals('2022-11-11', $joursFeries[9]->format('Y-m-d'));
        $this->assertEquals('2022-12-25', $joursFeries[10]->format('Y-m-d'));
    }

    public function testCalcJoursFeries2026()
    {
        $joursFeriesCalculator = new JoursFeriesCalculator();

        $joursFeries = $joursFeriesCalculator->calcJoursFeries(2026);

        $this->assertEquals(11, count($joursFeries));

        $this->assertEquals('2026-01-01', $joursFeries[0]->format('Y-m-d'));
        $this->assertEquals('2026-04-06', $joursFeries[1]->format('Y-m-d'));
        $this->assertEquals('2026-05-01', $joursFeries[2]->format('Y-m-d'));
        $this->assertEquals('2026-05-08', $joursFeries[3]->format('Y-m-d'));
        $this->assertEquals('2026-05-14', $joursFeries[4]->format('Y-m-d'));
        $this->assertEquals('2026-05-25', $joursFeries[5]->format('Y-m-d'));
        $this->assertEquals('2026-07-14', $joursFeries[6]->format('Y-m-d'));
        $this->assertEquals('2026-08-15', $joursFeries[7]->format('Y-m-d'));
        $this->assertEquals('2026-11-01', $joursFeries[8]->format('Y-m-d'));
        $this->assertEquals('2026-11-11', $joursFeries[9]->format('Y-m-d'));
        $this->assertEquals('2026-12-25', $joursFeries[10]->format('Y-m-d'));
    }
}
