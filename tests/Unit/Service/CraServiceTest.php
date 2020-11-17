<?php

namespace App\Tests\Unit\Service;

use App\Repository\CraRepository;
use App\Service\CraService;
use App\Service\DateMonthService;
use App\Service\JoursFeriesCalculator;
use PHPUnit\Framework\TestCase;

class CraServiceTest extends TestCase
{
    /**
     * @var CraRepository
     */
    private $craRepositoryMock;

    public function __construct()
    {
        parent::__construct();

        $this->craRepositoryMock = $this->createMock(CraRepository::class);
    }

    public function testCreateDefaultCraCreatesCraWithDaysOff()
    {
        $craService = new CraService(new DateMonthService(), $this->craRepositoryMock, new JoursFeriesCalculator());

        $cra = $craService->createDefaultCra(\DateTime::createFromFormat('Y-m-d', '2020-11-09'));
        $expected = [
            0,
            1, 1, 1, 1, 1, 0, 0,
            1, 1, 0, 1, 1, 0, 0,
            1, 1, 1, 1, 1, 0, 0,
            1, 1, 1, 1, 1, 0, 0,
            1,
        ];

        $this->assertEquals($expected, $cra->getJours());
    }

    public function testCreateDefaultCraCreatesCraNormalizedMonth()
    {
        $craService = new CraService(new DateMonthService(), $this->craRepositoryMock, new JoursFeriesCalculator());

        $cra = $craService->createDefaultCra(\DateTime::createFromFormat('Y-m-d', '2020-11-09'));

        $this->assertEquals('11', $cra->getMois()->format('m'));
        $this->assertEquals('01', $cra->getMois()->format('d'));
    }
}
