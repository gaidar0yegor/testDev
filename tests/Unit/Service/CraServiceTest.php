<?php

namespace App\Tests\Unit\Service;

use App\Entity\SocieteUser;
use App\Entity\SocieteUserPeriod;
use App\Entity\User;
use App\Repository\CraRepository;
use App\Repository\ProjetRepository;
use App\Service\CraService;
use App\Service\DateMonthService;
use App\Service\JoursFeriesCalculator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
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

    /**
     * @var EntityManagerInterface
     */
    private $em;

    protected function setUp(): void
    {
        // test skipped
        $this->markTestSkipped('must be revisited.');


        $this->craRepositoryMock = $this->createMock(CraRepository::class);
        $this->projetRepositoryMock = $this->createMock(ProjetRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
    }

    public function testCreateDefaultCraCreatesCraWithDaysOff()
    {
        $craService = new CraService(new DateMonthService(), $this->craRepositoryMock, $this->projetRepositoryMock, new JoursFeriesCalculator(), $this->em);

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
        $craService = new CraService(new DateMonthService(), $this->craRepositoryMock, $this->projetRepositoryMock, new JoursFeriesCalculator(), $this->em);

        $cra = $craService->createDefaultCra(\DateTime::createFromFormat('Y-m-d', '2020-11-09'));

        $this->assertEquals('11', $cra->getMois()->format('m'));
        $this->assertEquals('01', $cra->getMois()->format('d'));
    }

    public function testCreateDefaultCraTakesAccountOfUserDateEntreeSameMonth()
    {
        $craService = new CraService(new DateMonthService(), $this->craRepositoryMock, $this->projetRepositoryMock, new JoursFeriesCalculator(), $this->em);

        $user = new SocieteUser();
        $userPeriod = new SocieteUserPeriod();
        $userPeriod->setDateEntry(DateTime::createFromFormat('Y-m-d', '2020-11-10'));
        $user->addSocieteUserPeriod($userPeriod);

        $cra = $craService->loadCraForUser($user, DateTime::createFromFormat('Y-m-d', '2020-11-01'));

        $expected = [
            0,
            0, 0, 0, 0, 0, 0, 0,
            0, 1, 0, 1, 1, 0, 0,
            1, 1, 1, 1, 1, 0, 0,
            1, 1, 1, 1, 1, 0, 0,
            1,
        ];

        $this->assertEquals($expected, $cra->getJours());
    }

    public function testCreateDefaultCraTakesAccountOfUserDateEntreeLongBefore()
    {
        $craService = new CraService(new DateMonthService(), $this->craRepositoryMock, $this->projetRepositoryMock, new JoursFeriesCalculator(), $this->em);

        $user = new SocieteUser();
        $userPeriod = new SocieteUserPeriod();
        $userPeriod->setDateEntry(DateTime::createFromFormat('Y-m-d', '2020-11-09'));
        $user->addSocieteUserPeriod($userPeriod);

        $cra = $craService->loadCraForUser($user, DateTime::createFromFormat('Y-m-d', '2020-04-01'));

        $expected = [
            0,
            0, 0, 0, 0, 0, 0, 0,
            0, 0, 0, 0, 0, 0, 0,
            0, 0, 0, 0, 0, 0, 0,
            0, 0, 0, 0, 0, 0, 0,
            0,
        ];

        $this->assertEquals($expected, $cra->getJours());
    }

    public function testCreateDefaultCraTakesAccountOfUserDateSortieSameMonth()
    {
        $craService = new CraService(new DateMonthService(), $this->craRepositoryMock, $this->projetRepositoryMock, new JoursFeriesCalculator(), $this->em);

        $user = new SocieteUser();
        $userPeriod = new SocieteUserPeriod();
        $userPeriod->setDateEntry(DateTime::createFromFormat('Y-m-d', '2020-10-01'));
        $userPeriod->setDateLeave(DateTime::createFromFormat('Y-m-d', '2020-11-10'));
        $user->addSocieteUserPeriod($userPeriod);

        $cra = $craService->loadCraForUser($user, DateTime::createFromFormat('Y-m-d', '2020-11-01'));

        $expected = [
            0,
            1, 1, 1, 1, 1, 0, 0,
            1, 1, 0, 0, 0, 0, 0,
            0, 0, 0, 0, 0, 0, 0,
            0, 0, 0, 0, 0, 0, 0,
            0,
        ];

        $this->assertEquals($expected, $cra->getJours());
    }

    public function testCreateDefaultCraTakesAccountOfUserDateSortieLongAfter()
    {
        $craService = new CraService(new DateMonthService(), $this->craRepositoryMock, $this->projetRepositoryMock, new JoursFeriesCalculator(), $this->em);

        $user = new SocieteUser();
        $userPeriod = new SocieteUserPeriod();
        $userPeriod->setDateEntry(DateTime::createFromFormat('Y-m-d', '2020-01-01'));
        $userPeriod->setDateLeave(DateTime::createFromFormat('Y-m-d', '2020-05-10'));
        $user->addSocieteUserPeriod($userPeriod);

        $cra = $craService->loadCraForUser($user, DateTime::createFromFormat('Y-m-d', '2021-11-01'));

        $expected = [
            0,
            0, 0, 0, 0, 0, 0, 0,
            0, 0, 0, 0, 0, 0, 0,
            0, 0, 0, 0, 0, 0, 0,
            0, 0, 0, 0, 0, 0, 0,
            0,
        ];

        $this->assertEquals($expected, $cra->getJours());
    }
}
