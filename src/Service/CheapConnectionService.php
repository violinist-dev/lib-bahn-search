<?php

namespace Dpeuscher\BahnSearch\Service;

use Doctrine\ORM\EntityManager;
use Dpeuscher\BahnSearch\Bahn\FareSearchService;
use Dpeuscher\BahnSearch\Entity\Connection;

/**
 * @category  lib-bahn-search
 * @copyright Copyright (c) 2018 Dominik Peuscher
 */
class CheapConnectionService
{
    /**
     * @var FareSearchService
     */
    protected $fareSearchService;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * CheapConnectionService constructor.
     *
     * @param FareSearchService $fareSearchService
     * @param EntityManager $entityManager
     */
    public function __construct(FareSearchService $fareSearchService, ?EntityManager $entityManager = null)
    {
        $this->fareSearchService = $fareSearchService;
        $this->entityManager = $entityManager;
    }

    /**
     * @param $firstLeg
     * @return Connection[]
     * @throws \Doctrine\ORM\ORMException
     */
    public function getCheapestConnections($firstLeg): array
    {
        $from = $firstLeg['from'];
        $to = $firstLeg['to'];
        $programId = $firstLeg['programId'];
        $cabinClasses = $firstLeg['cabinClasses'];
        $fromDateTime = $firstLeg['fromDateTime'];
        $earliestDeparture = $firstLeg['earliestDeparture'];
        $latestArrival = $firstLeg['latestArrival'];
        $connections = $this->fareSearchService->findFares($from, $to, $fromDateTime, $programId, $cabinClasses);
        $this->sortConnections($connections, $earliestDeparture, $latestArrival);

        if ($this->entityManager !== null) {
            foreach ($connections as $connection) {
                $this->entityManager->persist($connection);
            }
            $this->entityManager->flush();
        }

        $cheapest = null;
        $cheapestFirstClass = null;
        foreach ($connections as $connection) {
            if ($cheapest === null && \in_array($connection->getMinimumFareCabinClass(), ['1', '2'], true)) {
                $cheapest = $connection;
                continue;
            }
            if ($cheapestFirstClass === null && $connection->getMinimumFareCabinClass() === '1') {
                $cheapestFirstClass = $connection;
                continue;
            }
        }
        return [$cheapest, $cheapestFirstClass];
    }

    /**
     * @param Connection[] &$connections
     * @param \DateTime $earliestDeparture
     * @param \DateTime $latestArrival
     */
    protected function sortConnections(&$connections, $earliestDeparture, $latestArrival): void
    {
        usort($connections, function (Connection $con1, Connection $con2) use ($earliestDeparture, $latestArrival) {
            if ($con1->getFromTime() < $earliestDeparture && $con2->getFromTime() >= $earliestDeparture) {
                return false;
            }
            if ($con1->getToTime() < $latestArrival && $con2->getToTime() >= $latestArrival) {
                return false;
            }
            if ($con2->getFromTime() < $earliestDeparture && $con1->getFromTime() >= $earliestDeparture) {
                return true;
            }
            if ($con2->getToTime() < $latestArrival && $con1->getToTime() >= $latestArrival) {
                return true;
            }
            if ($con1->getMinimumFare() < $con2->getMinimumFare()) {
                return false;
            }
            if ($con1->getMinimumFare() > $con2->getMinimumFare()) {
                return true;
            }
            if ($con1->getChanges() < $con2->getChanges()) {
                return false;
            }
            if ($con1->getChanges() > $con2->getChanges()) {
                return true;
            }
            if (abs($con1->getDuration() - $con2->getDuration()) >= 60) {
                return $con1->getDuration() > $con2->getDuration();
            }
            if ($con1->getFromTime() < $con2->getFromTime()) {
                return false;
            }
            if ($con1->getFromTime() > $con2->getFromTime()) {
                return true;
            }
            if ($con1->getMinimumFareCabinClass() > $con2->getMinimumFareCabinClass()) {
                return false;
            }
            if ($con1->getMinimumFareCabinClass() < $con2->getMinimumFareCabinClass()) {
                return true;
            }
            return false;
        });
    }
}
