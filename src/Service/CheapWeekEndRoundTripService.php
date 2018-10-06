<?php

namespace Dpeuscher\BahnSearch\Service;

use Doctrine\ORM\EntityManager;
use Dpeuscher\BahnSearch\Entity\RoundTrip;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * @category  lib-bahn-search
 * @copyright Copyright (c) 2018 Dominik Peuscher
 */
class CheapWeekEndRoundTripService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var CheapRoundTripConnectionService
     */
    protected $cheapRoundTripConnectionService;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $programId;

    /**
     * @var int
     */
    protected $startTime;

    /**
     * @var int
     */
    protected $startTimeFrame;

    /**
     * @var int
     */
    protected $returnTime;

    /**
     * @var int
     */
    protected $returnTimeFrame;

    /**
     * CheapWeekEndRoundTripService constructor.
     *
     * @param CheapRoundTripConnectionService $cheapRoundTripConnectionService
     * @param LoggerInterface $logger
     * @param EntityManager $entityManager
     * @param string $programId
     * @param int $startTime
     * @param int $startTimeFrame
     * @param int $returnTime
     * @param int $returnTimeFrame
     */
    public function __construct(
        CheapRoundTripConnectionService $cheapRoundTripConnectionService,
        LoggerInterface $logger,
        ?EntityManager $entityManager = null,
        string $programId = '',
        int $startTime = 15,
        int $startTimeFrame = 12,
        int $returnTime = 15,
        int $returnTimeFrame = 12
    ) {
        $this->logger = $logger;
        $this->cheapRoundTripConnectionService = $cheapRoundTripConnectionService;
        $this->entityManager = $entityManager;
        $this->programId = $programId;
        $this->startTime = $startTime;
        $this->startTimeFrame = $startTimeFrame;
        $this->returnTime = $returnTime;
        $this->returnTimeFrame = $returnTimeFrame;
    }

    /**
     * @param string $from
     * @param string $to
     * @param \DateTime|null $baseDate
     * @return RoundTrip[]
     * @throws \Doctrine\ORM\ORMException
     */
    public function getRoundTrips(string $from, string $to, ?\DateTime $baseDate = null): array
    {
        $roundTrips = [];

        try {
            $currentDate = $baseDate ?? new \DateTime('Friday');
            $currentDate->setTime(0, 0, 0);
            while ($currentDate->format('l') !== 'Friday') {
                $currentDate->add(new \DateInterval('P1D'));
            }
            // @codeCoverageIgnoreStart
        } catch (\Exception $e) {
            return $roundTrips;
            // @codeCoverageIgnoreEnd
        }
        /** @noinspection UnSafeIsSetOverArrayInspection */
        do {
            $startTime = clone $currentDate;
            $startTime->setTime($this->startTime, 0);

            $returnTime = clone $currentDate;

            try {
                $returnTime->add(new \DateInterval('P2D'));
                // @codeCoverageIgnoreStart
            } catch (\Exception $e) {
                break;
                // @codeCoverageIgnoreEnd
            }

            $returnTime->setTime($this->returnTime, 0);

            $roundTrip = $this->getRoundTripsForDate($from, $to, $startTime, $this->startTimeFrame, $returnTime,
                $this->returnTimeFrame);

            if ($roundTrip !== null) {
                $roundTrips[] = $roundTrip;
            }

            try {
                $currentDate->add(new \DateInterval('P7D'));
                // @codeCoverageIgnoreStart
            } catch (\Exception $e) {
                break;
                // @codeCoverageIgnoreEnd
            }
            if ($this->entityManager !== null) {
                $this->entityManager->persist($roundTrip);
                $this->entityManager->flush();
            }

            $this->logger->info($roundTrip->__toString());
        } while (isset($roundTrip) && $roundTrip->getCheapestFirstLeg() !== null && $roundTrip->getCheapestLastLeg() !== null);

        return $roundTrips;
    }

    /**
     * @param string $from
     * @param string $to
     * @param \DateTime $firstLegStart
     * @param int $firstLegTimeFrame
     * @param \DateTime $lastLegStart
     * @param int $lastLegTimeFrame
     * @return RoundTrip
     * @throws \Doctrine\ORM\ORMException
     */
    private function getRoundTripsForDate(
        string $from,
        string $to,
        \DateTime $firstLegStart,
        int $firstLegTimeFrame,
        \DateTime $lastLegStart,
        int $lastLegTimeFrame
    ): ?RoundTrip {
        try {

            [$earliestDeparture, $latestArrival, $fromDateTime] = $this->calculateDates($firstLegStart,
                $firstLegTimeFrame);

            $firstLeg = [
                'from'              => $from,
                'to'                => $to,
                'programId'         => $this->programId,
                'fromDateTime'      => $fromDateTime,
                'cabinClasses'      => ['1', '2'],
                'earliestDeparture' => $earliestDeparture,
                'latestArrival'     => $latestArrival,
            ];

            [$earliestDeparture2, $latestArrival2, $fromDateTime2] = $this->calculateDates($lastLegStart,
                $lastLegTimeFrame);

            $lastLeg = [
                'from'              => $to,
                'to'                => $from,
                'programId'         => $this->programId,
                'fromDateTime'      => $fromDateTime2,
                'cabinClasses'      => ['1', '2'],
                'earliestDeparture' => $earliestDeparture2,
                'latestArrival'     => $latestArrival2,
            ];
            // @codeCoverageIgnoreStart
        } catch (\Exception $e) {
            return null;
            // @codeCoverageIgnoreEnd
        }
        return $this->cheapRoundTripConnectionService->getRoundTrip($firstLeg, $lastLeg);
    }

    /**
     * @param \DateTime $start
     * @param int $timeFrame
     * @return array
     * @throws \Exception
     */
    private function calculateDates(\DateTime $start, int $timeFrame): array
    {
        $earliestDeparture = clone $start;

        $latestArrival = clone $earliestDeparture;
        $latestArrival->add(new \DateInterval('PT' . $timeFrame . 'H'));

        $fromDateTime = clone $start;
        return [$earliestDeparture, $latestArrival, $fromDateTime];
    }
}
