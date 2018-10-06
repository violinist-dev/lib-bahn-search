<?php

namespace Dpeuscher\BahnSearchTest\Service;

use Doctrine\ORM\EntityManager;
use Dpeuscher\BahnSearch\Bahn\FareSearchService;
use Dpeuscher\BahnSearch\Entity\Connection;
use Dpeuscher\BahnSearch\Service\CheapConnectionService;
use Dpeuscher\BahnSearchTest\Bahn\FareSearchServiceTest;
use PHPUnit\Framework\TestCase;

/**
 * Class CheapConnectionServiceTest
 *
 * @covers \Dpeuscher\BahnSearch\Service\CheapConnectionService
 * @covers \Dpeuscher\BahnSearch\Bahn\FareSearchService
 * @covers \Dpeuscher\BahnSearch\Bahn\FindLocationService
 * @covers \Dpeuscher\BahnSearch\Entity\Connection
 */
class CheapConnectionServiceTest extends TestCase
{

    public function testGetCheapestConnections(): void
    {
        /** @var FareSearchService $fareSearchMock */
        $fareSearchServiceTest = new FareSearchServiceTest();
        $fareSearchMock = $fareSearchServiceTest->getMockSuccessfulResultService();
        $entityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->setMethods([
            'persist',
            'flush',
        ])->getMock();
        $entityManager->expects($this->atLeastOnce())->method('persist');
        $entityManager->expects($this->atLeastOnce())->method('flush');
        /** @var EntityManager $entityManager */
        $service = new CheapConnectionService($fareSearchMock, $entityManager);
        try {
            /** @var \DateTime $dateTime */
            $dateTime = unserialize(file_get_contents(FareSearchServiceTest::FIXTURE_SUCCESSFUL_RESPONSE_MUNICH_HAMBURG_SEARCHTIME));
            $dateTime->add(new \DateInterval('P1D'));
            $leg = [
                'from'              => 'München Hbf',
                'to'                => 'Hamburg Hbf',
                'programId'         => '',
                'fromDateTime'      => (clone $dateTime)->setTime(22, 0),
                'cabinClasses'      => ['1', '2'],
                'earliestDeparture' => (clone $dateTime)->setTime(22, 0)->add(new \DateInterval('PT2H')),
                'latestArrival'     => (clone $dateTime)->setTime(22, 0)->add(new \DateInterval('PT10H')),
            ];
            [$cheapest, $cheapestFirstClass] = $service->getCheapestConnections($leg);
            $this->assertSame([
                'München Hbf (05.10.2018 03:46) -> Hamburg Hbf (05.10.2018 10:29) [403 Minuten] 0 Umstiege - Preis: 75,90 € (2. Kl.) [ICE]  ',
                'München Hbf (05.10.2018 03:46) -> Hamburg Hbf (05.10.2018 10:29) [403 Minuten] 0 Umstiege - Preis: 89,90 € (1. Kl.) [ICE]  ',
            ], [
                $cheapest->__toString(),
                $cheapestFirstClass->__toString(),
            ]);
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }

    public function testSortEmpty(): void
    {
        $connections = [];
        $expectedConnections = [];
        $this->assertSortResult($connections, $expectedConnections);
    }

    public function testSortCheaperIsFirstPriority(): void
    {
        $dateTime = new \DateTime();
        $earliestDeparture = (clone $dateTime)->setTime(16, 0);
        $latestArrival = (clone $dateTime)->setTime(23, 0);

        $connectionGood = new Connection();
        $connectionGood->setMinimumFare(100.00);
        $connectionGood->setFromTime((clone $dateTime)->setTime(19, 0));
        $connectionGood->setChanges(2);
        $connectionBad = new Connection();
        $connectionBad->setMinimumFare(110.00);
        $connectionBad->setFromTime((clone $dateTime)->setTime(18, 0));
        $connectionBad->setChanges(1);

        $connections = [$connectionBad, $connectionGood];
        $expectedConnections = [$connectionGood, $connectionBad];
        $this->assertSortResult($connections, $expectedConnections, $earliestDeparture, $latestArrival);
    }

    public function testSortSamePriceFewerChangesHasHigherPriority(): void
    {
        $dateTime = new \DateTime();
        $earliestDeparture = (clone $dateTime)->setTime(16, 0);
        $latestArrival = (clone $dateTime)->setTime(23, 0);

        $connectionGood = new Connection();
        $connectionGood->setMinimumFare(100.00);
        $connectionGood->setFromTime((clone $dateTime)->setTime(19, 0));
        $connectionGood->setChanges(1);
        $connectionBad = new Connection();
        $connectionBad->setMinimumFare(100.00);
        $connectionBad->setFromTime((clone $dateTime)->setTime(18, 0));
        $connectionBad->setChanges(2);

        $connections = [$connectionBad, $connectionGood];
        $expectedConnections = [$connectionGood, $connectionBad];
        $this->assertSortResult($connections, $expectedConnections, $earliestDeparture, $latestArrival);
    }

    public function testSortSamePriceSameChangesMoreThan60MinutesShorterHasHigherPriority(): void
    {
        $dateTime = new \DateTime();
        $earliestDeparture = (clone $dateTime)->setTime(16, 0);
        $latestArrival = (clone $dateTime)->setTime(23, 0);

        $connectionGood = new Connection();
        $connectionGood->setMinimumFare(100.00);
        $connectionGood->setFromTime((clone $dateTime)->setTime(19, 0));
        $connectionGood->setChanges(1);
        $connectionGood->setDuration(100);
        $connectionBad = new Connection();
        $connectionBad->setMinimumFare(100.00);
        $connectionBad->setFromTime((clone $dateTime)->setTime(18, 0));
        $connectionBad->setChanges(1);
        $connectionBad->setDuration(170);

        $connections = [$connectionBad, $connectionGood];
        $expectedConnections = [$connectionGood, $connectionBad];
        $this->assertSortResult($connections, $expectedConnections, $earliestDeparture, $latestArrival);
    }

    public function testSortSamePriceSameChangesLessThan60MinutesShorterButEarlierDepartureHasHigherPriority(): void
    {
        $dateTime = new \DateTime();
        $earliestDeparture = (clone $dateTime)->setTime(16, 0);
        $latestArrival = (clone $dateTime)->setTime(23, 0);

        $connectionGood = new Connection();
        $connectionGood->setMinimumFare(100.00);
        $connectionGood->setFromTime((clone $dateTime)->setTime(18, 0));
        $connectionGood->setToTime((clone $dateTime)->setTime(20, 0));
        $connectionGood->setChanges(1);
        $connectionGood->setDuration(150);
        $connectionBad = new Connection();
        $connectionBad->setMinimumFare(100.00);
        $connectionBad->setFromTime((clone $dateTime)->setTime(19, 0));
        $connectionBad->setToTime((clone $dateTime)->setTime(21, 0));
        $connectionBad->setChanges(1);
        $connectionBad->setDuration(100);

        $connections = [$connectionBad, $connectionGood];
        $expectedConnections = [$connectionGood, $connectionBad];
        $this->assertSortResult($connections, $expectedConnections, $earliestDeparture, $latestArrival);
    }

    public function testSortSamePriceSameChangesSameDurationButEarlierDepartureHasHigherPriority(): void
    {
        $dateTime = new \DateTime();
        $earliestDeparture = (clone $dateTime)->setTime(16, 0);
        $latestArrival = (clone $dateTime)->setTime(23, 0);

        $connectionGood = new Connection();
        $connectionGood->setMinimumFare(100.00);
        $connectionGood->setFromTime((clone $dateTime)->setTime(18, 0));
        $connectionGood->setToTime((clone $dateTime)->setTime(20, 0));
        $connectionGood->setChanges(1);
        $connectionGood->setDuration(100);
        $connectionBad = new Connection();
        $connectionBad->setMinimumFare(100.00);
        $connectionBad->setFromTime((clone $dateTime)->setTime(19, 0));
        $connectionBad->setToTime((clone $dateTime)->setTime(21, 0));
        $connectionBad->setChanges(1);
        $connectionBad->setDuration(100);

        $connections = [$connectionBad, $connectionGood];
        $expectedConnections = [$connectionGood, $connectionBad];
        $this->assertSortResult($connections, $expectedConnections, $earliestDeparture, $latestArrival);
    }

    public function testSortSamePriceSameChangesSameDurationButEarlierDepartureExactDepartureLimitHasHigherPriority(): void
    {
        $dateTime = new \DateTime();
        $earliestDeparture = (clone $dateTime)->setTime(16, 0);
        $latestArrival = (clone $dateTime)->setTime(23, 0);

        $connectionGood = new Connection();
        $connectionGood->setMinimumFare(100.00);
        $connectionGood->setFromTime((clone $dateTime)->setTime(16, 0));
        $connectionGood->setToTime((clone $dateTime)->setTime(20, 0));
        $connectionGood->setChanges(1);
        $connectionGood->setDuration(100);
        $connectionBad = new Connection();
        $connectionBad->setMinimumFare(100.00);
        $connectionBad->setFromTime((clone $dateTime)->setTime(19, 0));
        $connectionBad->setToTime((clone $dateTime)->setTime(21, 0));
        $connectionBad->setChanges(1);
        $connectionBad->setDuration(100);

        $connections = [$connectionBad, $connectionGood];
        $expectedConnections = [$connectionGood, $connectionBad];
        $this->assertSortResult($connections, $expectedConnections, $earliestDeparture, $latestArrival);
    }

    public function testSortSamePriceSameChangesSameDurationButEarlierDepartureExactArrivalLimitHasHigherPriority(): void
    {
        $dateTime = new \DateTime();
        $earliestDeparture = (clone $dateTime)->setTime(16, 0);
        $latestArrival = (clone $dateTime)->setTime(23, 0);

        $connectionGood = new Connection();
        $connectionGood->setMinimumFare(100.00);
        $connectionGood->setFromTime((clone $dateTime)->setTime(19, 0));
        $connectionGood->setToTime((clone $dateTime)->setTime(23, 0));
        $connectionGood->setChanges(1);
        $connectionGood->setDuration(100);
        $connectionBad = new Connection();
        $connectionBad->setMinimumFare(100.00);
        $connectionBad->setFromTime((clone $dateTime)->setTime(18, 0));
        $connectionBad->setToTime((clone $dateTime)->setTime(23, 0));
        $connectionBad->setChanges(1);
        $connectionBad->setDuration(100);

        $connections = [$connectionBad, $connectionGood];
        $expectedConnections = [$connectionGood, $connectionBad];
        $this->assertSortResult($connections, $expectedConnections, $earliestDeparture, $latestArrival);
    }

    public function testSortSamePriceSameChangesSameDurationSameDepartureButBetterCabinClassHasHigherPriority(): void
    {
        $dateTime = new \DateTime();
        $earliestDeparture = (clone $dateTime)->setTime(16, 0);
        $latestArrival = (clone $dateTime)->setTime(23, 0);

        $connectionGood = new Connection();
        $connectionGood->setMinimumFare(100.00);
        $connectionGood->setFromTime((clone $dateTime)->setTime(18, 0));
        $connectionGood->setToTime((clone $dateTime)->setTime(22, 0));
        $connectionGood->setChanges(1);
        $connectionGood->setDuration(100);
        $connectionGood->setMinimumFareCabinClass('1');
        $connectionBad = new Connection();
        $connectionBad->setMinimumFare(100.00);
        $connectionBad->setFromTime((clone $dateTime)->setTime(18, 0));
        $connectionBad->setToTime((clone $dateTime)->setTime(22, 0));
        $connectionBad->setChanges(1);
        $connectionBad->setDuration(100);
        $connectionBad->setMinimumFareCabinClass('2');

        $connections = [$connectionBad, $connectionGood];
        $expectedConnections = [$connectionGood, $connectionBad];
        $this->assertSortResult($connections, $expectedConnections, $earliestDeparture, $latestArrival);
    }

    public function testSortSamePriceSameChangesSameDurationSameDepartureSameCabinClassKeepOrder(): void
    {
        $dateTime = new \DateTime();
        $earliestDeparture = (clone $dateTime)->setTime(16, 0);
        $latestArrival = (clone $dateTime)->setTime(23, 0);

        $connectionGood = new Connection();
        $connectionGood->setMinimumFare(100.00);
        $connectionGood->setFromTime((clone $dateTime)->setTime(18, 0));
        $connectionGood->setToTime((clone $dateTime)->setTime(22, 0));
        $connectionGood->setChanges(1);
        $connectionGood->setDuration(100);
        $connectionGood->setMinimumFareCabinClass('2');
        $connectionBad = new Connection();
        $connectionBad->setMinimumFare(100.00);
        $connectionBad->setFromTime((clone $dateTime)->setTime(18, 0));
        $connectionBad->setToTime((clone $dateTime)->setTime(22, 0));
        $connectionBad->setChanges(1);
        $connectionBad->setDuration(100);
        $connectionBad->setMinimumFareCabinClass('2');

        $connections = [$connectionGood, $connectionBad];
        $expectedConnections = [$connectionGood, $connectionBad];
        $this->assertSortResult($connections, $expectedConnections, $earliestDeparture, $latestArrival, true);
    }

    public function testIgnoreBetterPriceChangesDurationAndDepartureTimeIfEarlierThanEarliestDeparture(): void
    {
        $dateTime = new \DateTime();
        $earliestDeparture = (clone $dateTime)->setTime(16, 0);
        $latestArrival = (clone $dateTime)->setTime(23, 0);

        $connectionGood = new Connection();
        $connectionGood->setMinimumFare(100.00);
        $connectionGood->setFromTime((clone $dateTime)->setTime(18, 0));
        $connectionGood->setToTime((clone $dateTime)->setTime(22, 0));
        $connectionGood->setChanges(1);
        $connectionGood->setDuration(100);
        $connectionGood->setMinimumFareCabinClass('2');
        $connectionBad = new Connection();
        $connectionBad->setMinimumFare(50.00);
        $connectionBad->setFromTime((clone $dateTime)->setTime(15, 30));
        $connectionBad->setToTime((clone $dateTime)->setTime(22, 0));
        $connectionBad->setChanges(0);
        $connectionBad->setDuration(10);
        $connectionBad->setMinimumFareCabinClass('1');

        $connections = [$connectionGood, $connectionBad];
        $expectedConnections = [$connectionGood, $connectionBad];
        $this->assertSortResult($connections, $expectedConnections, $earliestDeparture, $latestArrival);
    }

    public function testIgnoreBetterPriceChangesDurationAndDepartureTimeIfLaterThanLatestArrival(): void
    {
        $dateTime = new \DateTime();
        $earliestDeparture = (clone $dateTime)->setTime(16, 0);
        $latestArrival = (clone $dateTime)->setTime(23, 0);

        $connectionGood = new Connection();
        $connectionGood->setMinimumFare(100.00);
        $connectionGood->setFromTime((clone $dateTime)->setTime(18, 0));
        $connectionGood->setToTime((clone $dateTime)->setTime(22, 0));
        $connectionGood->setChanges(1);
        $connectionGood->setDuration(100);
        $connectionGood->setMinimumFareCabinClass('2');
        $connectionBad = new Connection();
        $connectionBad->setMinimumFare(50.00);
        $connectionBad->setFromTime((clone $dateTime)->setTime(17, 0));
        $connectionBad->setToTime((clone $dateTime)->setTime(23, 30));
        $connectionBad->setChanges(0);
        $connectionBad->setDuration(10);
        $connectionBad->setMinimumFareCabinClass('1');

        $connections = [$connectionGood, $connectionBad];
        $expectedConnections = [$connectionGood, $connectionBad];
        $this->assertSortResult($connections, $expectedConnections, $earliestDeparture, $latestArrival);
    }

    /**
     * @param $connections
     * @param $expectedConnections
     * @param \DateTime|null $earliestDeparture
     * @param \DateTime|null $latestArrival
     * @param bool $runReverseOrder
     */
    private function assertSortResult(
        $connections,
        $expectedConnections,
        ?\DateTime $earliestDeparture = null,
        ?\DateTime $latestArrival = null,
        bool $runReverseOrder = false
    ): void {
        $reverseConnections = array_reverse($connections);
        try {
            /** @var FareSearchService $fareSearchMock */
            $fareSearchServiceTest = new FareSearchServiceTest();
            $fareSearchMock = $fareSearchServiceTest->getMockSuccessfulResultService();

            $cheapConnectionService = new CheapConnectionService($fareSearchMock);
            $cheapConnectionServiceObject = new \ReflectionObject($cheapConnectionService);
            $sortConnectionsMethod = $cheapConnectionServiceObject->getMethod('sortConnections');
            $sortConnectionsMethod->setAccessible(true);

            /** @var \DateTime $dateTime */
            $dateTime = unserialize(file_get_contents(FareSearchServiceTest::FIXTURE_SUCCESSFUL_RESPONSE_MUNICH_HAMBURG_SEARCHTIME));
            $dateTime->add(new \DateInterval('P1D'));

            $sortConnectionsMethod->invokeArgs($cheapConnectionService, [
                &$connections,
                $earliestDeparture ?? (clone $dateTime)->setTime(22, 0)->add(new \DateInterval('PT2H')),
                $latestArrival ?? (clone $dateTime)->setTime(22, 0)->add(new \DateInterval('PT10H')),
            ]);

            $this->assertSame($expectedConnections, $connections);
        } catch (\Exception $e) {
            $this->fail($e);
        }
        if (!$runReverseOrder) {
            $this->assertSortResult($reverseConnections, $expectedConnections, $earliestDeparture, $latestArrival,
                true);
        }
    }
}
