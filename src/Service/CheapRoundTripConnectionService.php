<?php

namespace Dpeuscher\BahnSearch\Service;

use Dpeuscher\BahnSearch\Entity\RoundTrip;

/**
 * @category  lib-bahn-search
 * @copyright Copyright (c) 2018 Dominik Peuscher
 */
class CheapRoundTripConnectionService
{
    /**
     * @var CheapConnectionService
     */
    protected $cheapConnectionService;

    /**
     * CheapRoundTripConnectionService constructor.
     *
     * @param CheapConnectionService $cheapConnectionService
     */
    public function __construct(CheapConnectionService $cheapConnectionService)
    {
        $this->cheapConnectionService = $cheapConnectionService;
    }

    /**
     * @param array $firstLeg
     * @param array $lastLeg
     * @return RoundTrip
     * @throws \Doctrine\ORM\ORMException
     */
    public function getRoundTrip($firstLeg, $lastLeg): RoundTrip
    {
        [$conFirst, $conFirstClassFirst] = $this->cheapConnectionService->getCheapestConnections($firstLeg);
        [$conLast, $conFirstClassLast] = $this->cheapConnectionService->getCheapestConnections($lastLeg);

        $fullPriceFirstClass = null;
        $fullPriceCheapest = null;
        if ($conFirstClassFirst !== null && $conFirstClassLast !== null) {
            $fullPriceFirstClass = $conFirstClassFirst->getMinimumFare() + $conFirstClassLast->getMinimumFare();
        }
        if ($conFirst !== null && $conLast !== null) {
            $fullPriceCheapest = $conFirst->getMinimumFare() + $conLast->getMinimumFare();
        }

        $roundTrip = new RoundTrip();
        $roundTrip->setCheapestFirstLeg($conFirst);
        $roundTrip->setCheapestFirstLegFirstClass($conFirstClassFirst);
        $roundTrip->setCheapestLastLeg($conLast);
        $roundTrip->setCheapestLastLegFirstClass($conFirstClassLast);
        $roundTrip->setFromLocation($firstLeg['from']);
        $roundTrip->setToLocation($firstLeg['to']);
        $roundTrip->setProgramId($firstLeg['programId']);
        $roundTrip->setFromDepDateTime($firstLeg['fromDateTime']);
        $roundTrip->setToDepDateTime($lastLeg['fromDateTime']);
        $roundTrip->setFullPrice($fullPriceCheapest);
        $roundTrip->setFullPriceFirstClass($fullPriceFirstClass);

        return $roundTrip;
    }
}
