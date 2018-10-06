<?php

namespace Dpeuscher\BahnSearchTest\Service;

use Doctrine\ORM\EntityManager;
use Dpeuscher\BahnSearch\Entity\Connection;
use Dpeuscher\BahnSearch\Entity\RoundTrip;
use Dpeuscher\BahnSearch\Service\CheapRoundTripConnectionService;
use Dpeuscher\BahnSearch\Service\CheapWeekEndRoundTripService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class CheapWeekEndRoundTripServiceTest
 *
 * @covers \Dpeuscher\BahnSearch\Service\CheapWeekEndRoundTripService
 * @covers \Dpeuscher\BahnSearch\Entity\Connection
 */
class CheapWeekEndRoundTripServiceTest extends TestCase
{
    public function testGetRoundTrips()
    {
        $programId = 'ProgramId';
        $startTime = 15;
        $startTimeFrame = 12;
        $returnTime = 14;
        $returnTimeFrame = 11;
        $from = 'From';
        $to = 'To';

        $now = \DateTime::createFromFormat('Y-m-d H:i:s', '2018-10-06 13:45:00');

        $returnArray = [
            [
                'firstLegData' => [
                    'earliestDeparture' => '2018-10-12 15:00:00',
                    'latestArrival'     => '2018-10-13 03:00:00',
                    'fromDateTime'      => '2018-10-12 15:00:00',
                ],
                'lastLegData'  => [
                    'earliestDeparture' => '2018-10-14 14:00:00',
                    'latestArrival'     => '2018-10-15 01:00:00',
                    'fromDateTime'      => '2018-10-14 14:00:00',
                ],
                'firstLeg'     => true,
                'lastLeg'      => true,
            ],
            [
                'firstLegData' => [
                    'earliestDeparture' => '2018-10-19 15:00:00',
                    'latestArrival'     => '2018-10-20 03:00:00',
                    'fromDateTime'      => '2018-10-19 15:00:00',
                ],
                'lastLegData'  => [
                    'earliestDeparture' => '2018-10-21 14:00:00',
                    'latestArrival'     => '2018-10-22 01:00:00',
                    'fromDateTime'      => '2018-10-21 14:00:00',
                ],
                'firstLeg'     => true,
                'lastLeg'      => true,
            ],
            [
                'firstLegData' => [
                    'earliestDeparture' => '2018-10-26 15:00:00',
                    'latestArrival'     => '2018-10-27 03:00:00',
                    'fromDateTime'      => '2018-10-26 15:00:00',
                ],
                'lastLegData'  => [
                    'earliestDeparture' => '2018-10-28 14:00:00',
                    'latestArrival'     => '2018-10-29 01:00:00',
                    'fromDateTime'      => '2018-10-28 14:00:00',
                ],
                'firstLeg'     => true,
                'lastLeg'      => false,
            ],
        ];

        $cheapRoundTripConnectionService = $this->getMockBuilder(CheapRoundTripConnectionService::class)->disableOriginalConstructor()->setMethods(['getRoundTrip'])->getMock();
        $cheapRoundTripConnectionService->expects($this->atLeastOnce())->method('getRoundTrip')->willReturnCallback(
            function (array $firstLeg, array $lastLeg) use (
                $programId,
                $from,
                $to,
                &$returnArray
            ): ?RoundTrip {
                $data = array_shift($returnArray);

                /** @var RoundTrip|MockObject $roundTrip */
                $roundTrip = $this->getMockBuilder(RoundTrip::class)->disableOriginalConstructor()->setMethods([
                    'getCheapestFirstLeg',
                    'getCheapestLastLeg',
                    '__toString',
                ])->getMock();

                $this->assertNotEmpty($data);

                $roundTrip->expects($this->any())->method('getCheapestFirstLeg')->willReturn($data['firstLeg'] ? new Connection() : null);
                $roundTrip->expects($this->any())->method('getCheapestLastLeg')->willReturn($data['lastLeg'] ? new Connection() : null);
                $roundTrip->expects($this->any())->method('__toString')->willReturn('');

                $this->assertSame($from, $firstLeg['from']);
                $this->assertSame($to, $firstLeg['to']);
                $this->assertSame($programId, $firstLeg['programId']);
                $this->assertSame(['1', '2'], $firstLeg['cabinClasses']);
                $this->assertEquals(
                    \DateTime::createFromFormat('Y-m-d H:i:s', $data['firstLegData']['earliestDeparture']),
                    $firstLeg['earliestDeparture']);
                $this->assertEquals(
                    \DateTime::createFromFormat('Y-m-d H:i:s', $data['firstLegData']['latestArrival']),
                    $firstLeg['latestArrival']);
                $this->assertEquals(
                    \DateTime::createFromFormat('Y-m-d H:i:s', $data['firstLegData']['fromDateTime']),
                    $firstLeg['fromDateTime']);

                $this->assertSame($to, $lastLeg['from']);
                $this->assertSame($from, $lastLeg['to']);
                $this->assertSame($programId, $lastLeg['programId']);
                $this->assertSame(['1', '2'], $lastLeg['cabinClasses']);
                $this->assertEquals(
                    \DateTime::createFromFormat('Y-m-d H:i:s', $data['lastLegData']['earliestDeparture']),
                    $lastLeg['earliestDeparture']);
                $this->assertEquals(
                    \DateTime::createFromFormat('Y-m-d H:i:s', $data['lastLegData']['latestArrival']),
                    $lastLeg['latestArrival']);
                $this->assertEquals(
                    \DateTime::createFromFormat('Y-m-d H:i:s', $data['lastLegData']['fromDateTime']),
                    $lastLeg['fromDateTime']);

                return $roundTrip;
            }
        );
        /** @var CheapRoundTripConnectionService $cheapRoundTripConnectionService */

        $entityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->setMethods([
            'persist',
            'flush',
        ])->getMock();
        $entityManager->expects($this->atLeastOnce())->method('persist');
        $entityManager->expects($this->atLeastOnce())->method('flush');
        /** @var EntityManager $entityManager */

        $sut = new CheapWeekEndRoundTripService($cheapRoundTripConnectionService, $this->getBasicLogger(),
            $entityManager, $programId, $startTime, $startTimeFrame, $returnTime, $returnTimeFrame);

        $sut->getRoundTrips($from, $to, $now);
    }

    private function getBasicLogger(): LoggerInterface
    {
        $mock = $this->getMockBuilder(LoggerInterface::class)->setMethods([
            'emergency',
            'alert',
            'critical',
            'error',
            'warning',
            'notice',
            'info',
            'debug',
            'log',
        ])->getMock();
        /** @var LoggerInterface $mock */
        return $mock;
    }
}
