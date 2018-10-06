<?php

namespace Dpeuscher\BahnSearchTest\Bahn;

use Dpeuscher\BahnSearch\Bahn\FindLocationService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * Class FindLocationServiceTest
 * @covers \Dpeuscher\BahnSearch\Bahn\FindLocationService
 */
class FindLocationServiceTest extends TestCase
{
    private const RESULT_CODE_MUNICH = 'A=1@O=München Hbf@X=11558339@Y=48140229@U=80@L=008000261@B=1@p=1537914459@';

    private const FIXTURE_FOLDER = __DIR__ . '/fixtures';

    public const FIXTURE_SUCCESSFUL_RESPONSE_MUNICH = self::FIXTURE_FOLDER . '/FindLocationServiceFixture.Muenchen.js';
    public const FIXTURE_SUCCESSFUL_RESPONSE_HAMBURG = self::FIXTURE_FOLDER . '/FindLocationServiceFixture.Hamburg.js';

    /**
     * @var FindLocationService
     */
    private $sut;

    public function setup()
    {
        $this->sut = $this->getMockSuccessfulResultService();
    }

    public function testFindLocationCodeByName()
    {
        $this->assertEquals(utf8_encode(self::RESULT_CODE_MUNICH), $this->sut->findLocationCodeByName('München Hbf'));
    }

    public function getMockSuccessfulResultService(): FindLocationService
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'text/plain; charset=ISO-8859-1'],
                file_get_contents(self::FIXTURE_SUCCESSFUL_RESPONSE_HAMBURG)),
            new Response(200, ['Content-Type' => 'text/plain; charset=ISO-8859-1'],
                file_get_contents(self::FIXTURE_SUCCESSFUL_RESPONSE_MUNICH)),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        return new FindLocationService($client);
    }
}
