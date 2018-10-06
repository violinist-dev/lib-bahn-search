<?php

namespace Dpeuscher\BahnSearchTest\Service;

use Doctrine\ORM\ORMException;
use Dpeuscher\BahnSearch\Entity\Connection;
use Dpeuscher\BahnSearch\Service\CheapConnectionService;
use Dpeuscher\BahnSearch\Service\CheapRoundTripConnectionService;
use PHPUnit\Framework\TestCase;

/**
 * Class CheapRoundTripConnectionServiceTest
 * @covers \Dpeuscher\BahnSearch\Service\CheapRoundTripConnectionService
 * @covers \Dpeuscher\BahnSearch\Entity\RoundTrip
 * @covers \Dpeuscher\BahnSearch\Entity\Connection
 */
class CheapRoundTripConnectionServiceTest extends TestCase
{
    public function testGetRoundTrip(): void
    {
        try {
            /**
             * @var Connection[] $cheapestFirstLeg
             * @var Connection[] $cheapestSecondLeg
             * @var CheapRoundTripConnectionService $sut
             */
            [$cheapestFirstLeg, $cheapestSecondLeg, $sut] = $this->getSutMock();
            $dateTime = new \DateTime();
            $roundTrip = $sut->getRoundTrip($this->getFirstLegMockData($dateTime),
                $this->getSecondLegMockData($dateTime));
            $this->assertSame($roundTrip->getCheapestFirstLeg(), $cheapestFirstLeg[0]);
            $this->assertSame($roundTrip->getCheapestFirstLegFirstClass(), $cheapestFirstLeg[1]);
            $this->assertSame($roundTrip->getCheapestLastLeg(), $cheapestSecondLeg[0]);
            $this->assertSame($roundTrip->getCheapestLastLegFirstClass(), $cheapestSecondLeg[1]);
        } catch (ORMException $e) {
            $this->fail($e);
        }
    }

    public function testGetRoundTripPriceSecondClass(): void
    {
        try {
            /**
             * @var Connection[] $cheapestFirstLeg
             * @var Connection[] $cheapestSecondLeg
             * @var CheapRoundTripConnectionService $sut
             */
            [, , $sut] = $this->getSutMock();
            $dateTime = new \DateTime();
            $roundTrip = $sut->getRoundTrip($this->getFirstLegMockData($dateTime),
                $this->getSecondLegMockData($dateTime));
            $this->assertSame($roundTrip->getFullPrice(), 5.0);
        } catch (ORMException $e) {
            $this->fail($e);
        }
    }

    public function testGetRoundTripPriceFirstClass(): void
    {
        try {
            /**
             * @var CheapRoundTripConnectionService $sut
             */
            [, , $sut] = $this->getSutMock();
            $dateTime = new \DateTime();
            $roundTrip = $sut->getRoundTrip($this->getFirstLegMockData($dateTime),
                $this->getSecondLegMockData($dateTime));
            $this->assertSame($roundTrip->getFullPriceFirstClass(), 10.0);
        } catch (ORMException $e) {
            $this->fail($e);
        }
    }

    public function testGetRoundTripWithoutSecondClassFirstLeg(): void
    {
        try {
            /**
             * @var CheapRoundTripConnectionService $sut
             */
            [, , $sut] = $this->getSutMock(false);
            $dateTime = new \DateTime();
            $roundTrip = $sut->getRoundTrip($this->getFirstLegMockData($dateTime),
                $this->getSecondLegMockData($dateTime));
            $this->assertSame($roundTrip->getFullPrice(), 6.0);
        } catch (ORMException $e) {
            $this->fail($e);
        }
    }

    public function testGetRoundTripWithoutSecondClassSecondLeg(): void
    {
        try {
            /**
             * @var CheapRoundTripConnectionService $sut
             */
            [, , $sut] = $this->getSutMock(true,false);
            $dateTime = new \DateTime();
            $roundTrip = $sut->getRoundTrip($this->getFirstLegMockData($dateTime),
                $this->getSecondLegMockData($dateTime));
            $this->assertSame($roundTrip->getFullPrice(), 9.0);
        } catch (ORMException $e) {
            $this->fail($e);
        }
    }

    public function testGetRoundTripWithoutSecondClassFirstLegAndSecondClassSecondLeg(): void
    {
        try {
            /**
             * @var CheapRoundTripConnectionService $sut
             */
            [, , $sut] = $this->getSutMock(false,false);
            $dateTime = new \DateTime();
            $roundTrip = $sut->getRoundTrip($this->getFirstLegMockData($dateTime),
                $this->getSecondLegMockData($dateTime));
            $this->assertSame($roundTrip->getFullPrice(), 10.0);
        } catch (ORMException $e) {
            $this->fail($e);
        }
    }

    /**
     * @param bool $cheapestSecondClassAvailableFirstLeg
     * @param bool $cheapestSecondClassAvailableSecondLeg
     * @param bool $cheapestFirstClassAvailableFirstLeg
     * @param bool $cheapestFirstClassAvailableSecondLeg
     * @return array
     */
    private function getSutMock(
        bool $cheapestSecondClassAvailableFirstLeg = true,
        bool $cheapestSecondClassAvailableSecondLeg = true,
        bool $cheapestFirstClassAvailableFirstLeg = true,
        bool $cheapestFirstClassAvailableSecondLeg = true
    ): array {
        $cheapestSecondClassConnectionFirstLeg = new Connection();
        $cheapestSecondClassConnectionFirstLeg->setMinimumFare(1.00);
        $cheapestFirstClassConnectionFirstLeg = new Connection();
        $cheapestFirstClassConnectionFirstLeg->setMinimumFare(2.00);
        $cheapestSecondClassConnectionSecondLeg = new Connection();
        $cheapestSecondClassConnectionSecondLeg->setMinimumFare(4.00);
        $cheapestFirstClassConnectionSecondLeg = new Connection();
        $cheapestFirstClassConnectionSecondLeg->setMinimumFare(8.00);
        /** @var Connection[] $cheapestFirstLeg */
        $cheapestFirstLeg = [
            $cheapestSecondClassConnectionFirstLeg,
            $cheapestFirstClassConnectionFirstLeg,
        ];
        if (!$cheapestSecondClassAvailableFirstLeg) {
            $cheapestFirstLeg[0] = null;
        }
        if (!$cheapestFirstClassAvailableFirstLeg) {
            $cheapestFirstLeg[1] = null;
        }
        /** @var Connection[] $cheapestSecondLeg */
        $cheapestSecondLeg = [
            $cheapestSecondClassConnectionSecondLeg,
            $cheapestFirstClassConnectionSecondLeg,
        ];
        if (!$cheapestSecondClassAvailableSecondLeg) {
            $cheapestSecondLeg[0] = null;
        }
        if (!$cheapestFirstClassAvailableSecondLeg) {
            $cheapestSecondLeg[1] = null;
        }
        $cheapConnectionService = $this->getMockBuilder(CheapConnectionService::class)->disableOriginalConstructor()->setMethods(['getCheapestConnections'])->getMock();
        $cheapConnectionService->expects($this->atLeastOnce())->method('getCheapestConnections')->willReturn(
            $cheapestFirstLeg,
            $cheapestSecondLeg
        );
        /** @var CheapConnectionService $cheapConnectionService */
        $sut = new CheapRoundTripConnectionService($cheapConnectionService);
        return [$cheapestFirstLeg, $cheapestSecondLeg, $sut];
    }

    /**
     * @param \DateTime $dateTime
     * @return array
     */
    private function getFirstLegMockData(\DateTime $dateTime): array
    {
        return [
            'from'         => 'From',
            'to'           => 'To',
            'programId'    => 'ProgramId',
            'fromDateTime' => (clone $dateTime)->setTime(8, 0),
        ];
    }

    /**
     * @param \DateTime $dateTime
     * @return array
     */
    private function getSecondLegMockData(\DateTime $dateTime): array
    {
        return [
            'from'         => 'To',
            'to'           => 'From',
            'programId'    => 'ProgramId',
            'fromDateTime' => (clone $dateTime)->setTime(14, 0),
        ];
    }

}
