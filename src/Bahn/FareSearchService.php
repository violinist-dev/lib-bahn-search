<?php

namespace Dpeuscher\BahnSearch\Bahn;

use Dpeuscher\BahnSearch\Entity\Connection;
use Dpeuscher\Util\Cache\CacheReturner;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @category  lib-bahn-search
 * @copyright Copyright (c) 2018 Dominik Peuscher
 */
class FareSearchService
{
    /**
     * @var FindLocationService
     */
    private $findLocationService;

    /**
     * @var ?CacheReturner
     */
    private $cacheReturner;

    /**
     * @var Client
     */
    private $guzzle;

    /**
     * @var \DateTime
     */
    private $searchTime;

    /**
     * FareSearchService constructor.
     *
     * @param FindLocationService $findLocationService
     * @param Client $guzzle
     * @param CacheReturner $cacheReturner
     * @param \DateTime $searchTime
     */
    public function __construct(
        FindLocationService $findLocationService,
        Client $guzzle,
        ?CacheReturner $cacheReturner = null,
        \DateTime $searchTime = null
    ) {
        $this->findLocationService = $findLocationService;
        $this->guzzle = $guzzle;
        $this->cacheReturner = $cacheReturner;
        $this->searchTime = $searchTime;
    }

    /**
     * @param string $from
     * @param string $to
     * @param \DateTime $fromDateTime
     * @param string $programId
     * @param string[] $cabinClasses
     * @return Connection[]
     */
    public function findFares(
        string $from,
        string $to,
        \DateTime $fromDateTime,
        string $programId,
        array $cabinClasses
    ): array {
        $fromCode = $this->locationToCode($from);
        $toCode = $this->locationToCode($to);

        /** @var Connection[] $connections */
        $connections = [];
        foreach ($cabinClasses as $cabinClass) {

            $crawler = $this->goToResultsPage($from, $to, $fromDateTime, $programId, $fromCode, $toCode, $cabinClass);
            $crawler->filter('#resultsOverview > tbody.boxShadow')->each(function (Crawler $node) use (
                $cabinClass,
                $fromCode,
                $toCode,
                $fromDateTime,
                &$connections
            ) {
                $connections[] = $this->buildConnection($node, $cabinClass, $fromCode, $toCode, $fromDateTime);
            });
        }

        return $connections;
    }

    /**
     * @param string $from
     * @param string $to
     * @param \DateTime $fromDateTime
     * @param string $programId
     * @param $fromCode
     * @param $toCode
     * @param $cabinClass
     * @return Crawler
     */
    protected function goToResultsPage(
        string $from,
        string $to,
        \DateTime $fromDateTime,
        string $programId,
        $fromCode,
        $toCode,
        $cabinClass
    ): Crawler {
        $browser = new \Goutte\Client();
        $browser->setClient($this->guzzle);

        $crawler = $browser->request('GET', 'https://reiseauskunft.bahn.de/bin/query.exe/dn?program=' . $programId);
        $form = $crawler->selectButton('Suchen')->form();
        $form->setValues([
            'advancedProductMode'                   => 'yes',
            'existIntermodalDep_enable'             => 'yes',
            'existIntermodalDest_enable'            => 'yes',
            'existOptimizePrice'                    => '1',
            'existOptionBits'                       => 'yes',
            'existProductAutoReturn'                => 'yes',
            'existProductNahverkehr'                => '1',
            'HWAI=JS!ajax'                          => 'yes',
            'HWAI=JS!js'                            => 'yes',
            #'HWAI=QUERY!direction'                  => 'both!',
            'HWAI=QUERY!displayed'                  => 'yes',
            'HWAI=QUERY!hideExtInt'                 => 'no',
            'HWAI=QUERY!prodAdvanced'               => '0',
            'HWAI=QUERY!rit'                        => 'no',
            'HWAI=QUERY$PRODUCTS$0_0!show'          => '[yes,yes]',
            #'HWAI=QUERY$PRODUCTS$1_0!show'          => 'yes',
            'HWAI=QUERY$via$0!number'               => '0',
            'HWAI=QUERY$via$1!number'               => '0',
            #'ignoreTypeCheck'                       => 'yes',
            'queryPageDisplayed'                    => 'yes',
            'REQ0HafasChangeTime'                   => '0:1',
            'REQ0HafasOptimize1'                    => '0:1',
            'REQ0HafasSearchForw'                   => '1',
            'REQ0JourneyDate'                       => $fromDateTime->format('D+,d.m.y'),
            'REQ0JourneyDep__enable'                => 'Foot',
            'REQ0JourneyDep_Bike_maxDist'           => '5000',
            'REQ0JourneyDep_Bike_minDist'           => '0',
            'REQ0JourneyDep_Foot_maxDist'           => '2000',
            'REQ0JourneyDep_Foot_minDist'           => '0',
            'REQ0JourneyDep_KissRide_maxDist'       => '50000',
            'REQ0JourneyDep_KissRide_minDist'       => '2000',
            'REQ0JourneyDest__enable'               => 'Foot',
            'REQ0JourneyDest_Bike_maxDist'          => '5000',
            'REQ0JourneyDest_Bike_minDist'          => '0',
            'REQ0JourneyDest_Foot_maxDist'          => '2000',
            'REQ0JourneyDest_Foot_minDist'          => '0',
            'REQ0JourneyDest_KissRide_maxDist'      => '50000',
            'REQ0JourneyDest_KissRide_minDist'      => '2000',
            'REQ0JourneyProduct_opt_section_0_list' => '0:0000',
            'REQ0JourneyProduct_prod_section_0_0'   => '1',
            'REQ0JourneyProduct_prod_section_0_1'   => '1',
            'REQ0JourneyProduct_prod_section_0_2'   => '1',
            'REQ0JourneyProduct_prod_section_0_3'   => '1',
            'REQ0JourneyProduct_prod_section_0_4'   => '1',
            'REQ0JourneyProduct_prod_section_0_5'   => '1',
            'REQ0JourneyProduct_prod_section_0_6'   => '1',
            'REQ0JourneyProduct_prod_section_0_7'   => '1',
            'REQ0JourneyProduct_prod_section_0_8'   => '1',
            'REQ0JourneyProduct_prod_section_0_9'   => '1',
            'REQ0JourneyRevia'                      => 'yes',
            'REQ0JourneyStops1ID'                   => '',
            'REQ0JourneyStops2ID'                   => '',
            'REQ0JourneyStopsS0A'                   => '255',
            'REQ0JourneyStopsS0a'                   => '131072',
            'REQ0JourneyStopsS0G'                   => $from,
            'REQ0JourneyStopsS0ID'                  => $fromCode,
            'REQ0JourneyStopsS0o'                   => '8',
            'REQ0JourneyStopsZ0A'                   => '255',
            'REQ0JourneyStopsZ0a'                   => '131072',
            'REQ0JourneyStopsZ0G'                   => $to,
            'REQ0JourneyStopsZ0ID'                  => $toCode,
            'REQ0JourneyStopsZ0o'                   => '8',
            'REQ0JourneyTime'                       => $fromDateTime->format('H:i'),
            'REQ0Tariff_Class'                      => $cabinClass,
            'REQ0Tariff_TravellerAge.1'             => '',
            'REQ0Tariff_TravellerReductionClass.1'  => '0',
            'REQ0Tariff_TravellerType.1'            => 'E',
            'REQ1HafasSearchForw'                   => '1',
            'REQ1JourneyDate'                       => '',
            #'REQ1JourneyDate'                       => 'So,+11.11.18',
            #'REQ1JourneyProduct_opt_section_0_list' => '0:0000',
            #'REQ1JourneyProduct_prod_section_0_0'   => '1',
            #'REQ1JourneyProduct_prod_section_0_1'   => '1',
            #'REQ1JourneyProduct_prod_section_0_2'   => '1',
            #'REQ1JourneyProduct_prod_section_0_3'   => '1',
            #'REQ1JourneyProduct_prod_section_0_4'   => '1',
            #'REQ1JourneyProduct_prod_section_0_5'   => '1',
            #'REQ1JourneyProduct_prod_section_0_6'   => '1',
            #'REQ1JourneyProduct_prod_section_0_7'   => '1',
            #'REQ1JourneyProduct_prod_section_0_8'   => '1',
            #'REQ1JourneyProduct_prod_section_0_9'   => '1',
            'REQ1JourneyStops1ID'                   => '',
            'REQ1JourneyStops2ID'                   => '',
            'REQ1JourneyTime'                       => '',
            #'REQ1JourneyTime'                       => '17:00',
            'rtMode'                                => '10',
            'start'                                 => 'Suchen',
            'traveller_Nr'                          => '1',
        ]);
        $crawler = $browser->submit($form);
        $max = 4;
        $nextLink = $crawler->selectLink('Später');
        $nextLinkNode = $nextLink->getNode(0);
        while ($nextLinkNode && false === strpos($nextLinkNode->getAttribute('class'), 'disabled') && $max--) {
            $crawler = $browser->click($nextLink->link());
            $nextLink = $crawler->selectLink('Später');
            $nextLinkNode = $nextLink->getNode(0);
        }
        /*$max = 4;
        $nextLink = $crawler->selectLink('Früher');
        $nextLinkNode = $nextLink->getNode(0);
        while ($nextLinkNode && false === strpos($nextLinkNode->getAttribute('class'), 'disabled') && $max--) {
            $link = $crawler->selectLink('Früher')->link();
            $crawler = $browser->click($link);
            $nextLink = $crawler->selectLink('Früher');
            $nextLinkNode = $nextLink->getNode(0);
        }*/
        return $crawler;
    }

    /**
     * @param Crawler $node
     * @param string $cabinClass
     * @param string $fromCode
     * @param string $toCode
     * @param \DateTime $fromDateTime
     * @return Connection
     * @throws \Exception
     */
    protected function buildConnection(
        Crawler $node,
        string $cabinClass,
        string $fromCode,
        string $toCode,
        \DateTime $fromDateTime
    ): Connection {
        $firstCabinClass = $cabinClass;
        $secondCabinClass = $cabinClass;
        $node->filter('tr.firstrow > td.station.first')->each(function (Crawler $node) use (&$from) {
            $from = trim($node->text(), "  \t\n\r\0\x0B");
        });
        $node->filter('tr.last > td.station.stationDest')->each(function (Crawler $node) use (&$to) {
            $to = trim($node->text(), "  \t\n\r\0\x0B");
        });
        $node->filter('tr.firstrow > td.time')->each(function (Crawler $node) use (&$fromTime) {
            $fromTime = trim($node->text(), "  \t\n\r\0\x0B");
        });
        $node->filter('tr.last > td.time')->each(function (Crawler $node) use (&$toTime) {
            $toTime = trim($node->text(), "  \t\n\r\0\x0B");
        });
        $node->filter('tr.firstrow > td.duration')->each(function (Crawler $node) use (&$duration) {
            $duration = trim($node->text(), "  \t\n\r\0\x0B");
        });
        $node->filter('tr.firstrow > td.changes')->each(function (Crawler $node) use (&$changes) {
            $changes = trim($node->text(), "  \t\n\r\0\x0B");
        });
        $node->filter('tr.firstrow > td.products')->each(function (Crawler $node) use (&$products) {
            $products = trim($node->text(), "  \t\n\r\0\x0B");
        });
        $node->filter('tr.firstrow > td.farePep')->each(function (Crawler $node) use (
            &$firstFare,
            &$firstFareFull
        ) {
            $firstFare = null;
            if (preg_match('/(?:^|\D)((\d+,\d{2})[\s ]*EUR)/u', $node->text(), $matches)) {
                $firstFare = (float)str_replace(',', '.', $matches['2']);
                $firstFareFull = trim(str_replace($matches['1'], ' ', $node->text()), "  \t\n\r\0\x0B");
            } else {
                $firstFareFull = trim($node->text(), "  \t\n\r\0\x0B");
            }
        });
        $node->filter('tr.firstrow > td.fareStd')->each(function (Crawler $node) use (
            &$secondFare,
            &$secondFareFull
        ) {
            $secondFare = null;
            if (preg_match('/(?:^|\D)((\d+,\d{2})[\s ]*EUR)/u', $node->text(), $matches)) {
                $secondFare = (float)str_replace(',', '.', $matches['2']);
                $secondFareFull = trim(str_replace($matches['1'], ' ', $node->text()), "  \t\n\r\0\x0B");
            } else {
                $secondFareFull = trim($node->text(), "  \t\n\r\0\x0B");
            }
        });
        if (preg_match('/1.Klasse/', $firstFareFull)) {
            $firstCabinClass = '1';
        }
        if (preg_match('/1.Klasse/', $secondFareFull)) {
            $secondCabinClass = '1';
        }
        $connection = new Connection();
        $connection->setFromLocation($from);
        $connection->setFromLocationId($fromCode);
        $connection->setToLocation($to);
        $connection->setToLocationId($toCode);
        $connection->setChanges($changes);
        if (!empty($firstFare)) {
            $connection->setCheapFare($firstFare);
            $connection->setCheapFareCabinClass($firstCabinClass);
            $connection->setCheapFareText($firstFareFull);
        }
        if (!empty($secondFare)) {
            $connection->setFlexFare($secondFare);
            $connection->setFlexFareCabinClass($secondCabinClass);
            $connection->setFlexFareText($secondFareFull);
        }
        $connection->setDuration(strtok($duration, ':') * 60 + strtok(':'));
        $departureDateTime = $this->findDateByNearestDay($fromDateTime, $fromTime);
        $arrivalDateTime = clone $departureDateTime;
        $arrivalDateTime->add(new \DateInterval('PT' . $connection->getDuration() . 'M'));
        $connection->setFromTime(clone $departureDateTime);
        $connection->setToTime(clone $arrivalDateTime);
        $connection->setProducts($products);
        $connection->setResultTime($this->searchTime);

        if (!empty($firstFare) && ($secondFare === null || $firstFare < $secondFare)) {
            $connection->setMinimumFare($firstFare);
            $connection->setMinimumFareCabinClass($firstCabinClass);
            $connection->setMinimumFareText($firstFareFull);
        } elseif (!empty($secondFare)) {
            $connection->setMinimumFare($secondFare);
            $connection->setMinimumFareCabinClass($secondCabinClass);
            $connection->setMinimumFareText($secondFareFull);
        }

        return $connection;
    }

    protected function findDateByNearestDay(\DateTime $compare, string $time): \DateTime
    {
        try {
            $prevDate = clone $compare;
            $currentDate = clone $compare;
            $nextDate = clone $compare;
            $prevDate->sub(new \DateInterval('P1D'));
            $nextDate->add(new \DateInterval('P1D'));

            $prevDate->setTime(strtok($time, ':'), strtok(':'));
            $currentDate->setTime(strtok($time, ':'), strtok(':'));
            $nextDate->setTime(strtok($time, ':'), strtok(':'));

            $prevDiff = $prevDate->diff($compare);
            $currentDiff = $currentDate->diff($compare);
            $nextDiff = $nextDate->diff($compare);

            $prevDiff->invert = $currentDiff->invert = $nextDiff->invert = 0;

            $compareFormat = '%a%H%I%S';

            $prevDiffNum = $prevDiff->format($compareFormat);
            $currentDiffNum = $currentDiff->format($compareFormat);
            $nextDiffNum = $nextDiff->format($compareFormat);

            if ($prevDiffNum < $currentDiffNum && $prevDiffNum < $nextDiffNum) {
                return $prevDate;
            } /** @noinspection RedundantElseClauseInspection */
            elseif ($nextDiffNum < $prevDiffNum && $nextDiffNum < $currentDiffNum) {
                return $nextDate;
            } else {
                return $currentDate;
            }
            // @codeCoverageIgnoreStart
        } catch (\Exception $e) {
            die('Should not happen: ' . $e);
            // @codeCoverageIgnoreEnd
        }
    }

    private function locationToCode(string $from): string
    {
        $callback = function ($from) {
            return $this->findLocationService->findLocationCodeByName($from);
        };
        return $this->cacheReturner ? $this->cacheReturner->return($from, $callback) : $callback($from);
    }
}
