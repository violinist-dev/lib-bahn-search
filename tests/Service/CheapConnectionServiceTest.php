<?php

namespace Dpeuscher\BahnSearchTest\Service;

use Dpeuscher\BahnSearch\Bahn\FareSearchService;
use Dpeuscher\BahnSearch\Service\CheapConnectionService;
use PHPUnit\Framework\TestCase;

class CheapConnectionServiceTest extends TestCase
{

    public function testGetCheapestConnections()
    {
        $fareSearchMock = $this->getMockBuilder(FareSearchService::class)->disableOriginalConstructor()->getMock();
        $service = new CheapConnectionService($fareSearchMock);
        $this->assertInstanceOf(CheapConnectionService::class, $service);
    }
}
