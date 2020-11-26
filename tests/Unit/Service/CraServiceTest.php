<?php

namespace App\Tests\Unit\Service;

use App\Repository\CraRepository;
use App\Repository\ProjetRepository;
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

    /**
     * @var ProjetRepository
     */
    private $projetRepositoryMock;

    public function __construct()
    {
        parent::__construct();

        $this->craRepositoryMock = $this->createMock(CraRepository::class);
        $this->projetRepositoryMock = $this->createMock(ProjetRepository::class);
    }

    public function testCreateDefaultCraCreatesCraWithDaysOff()
    {
        $craService = new CraService(new DateMonthService(), $this->craRepositoryMock, $this->projetRepositoryMock, new JoursFeriesCalculator());

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
        $craService = new CraService(new DateMonthService(), $this->craRepositoryMock, $this->projetRepositoryMock, new JoursFeriesCalculator());

        $cra = $craService->createDefaultCra(\DateTime::createFromFormat('Y-m-d', '2020-11-09'));

        $this->assertEquals('11', $cra->getMois()->format('m'));
        $this->assertEquals('01', $cra->getMois()->format('d'));
    }
}
