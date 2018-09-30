<?php

namespace Dpeuscher\BahnSearchTest\Service;

use Dpeuscher\BahnSearch\Bahn\FareSearchService;
use Dpeuscher\BahnSearch\Service\CheapConnectionService;
use PHPUnit\Framework\TestCase;

/**
 * Class CheapConnectionServiceTest
 *
 * @covers \Dpeuscher\BahnSearch\Service\CheapConnectionService
 */
class CheapConnectionServiceTest extends TestCase
{

    public function testGetCheapestConnections()
    {
        /** @var FareSearchService $fareSearchMock */
        $fareSearchMock = $this->getMockBuilder(FareSearchService::class)->disableOriginalConstructor()->getMock();
        $service = new CheapConnectionService($fareSearchMock);
        $this->assertInstanceOf(CheapConnectionService::class, $service);
    }
}
