<?php

namespace Dpeuscher\BahnSearchTest\Bahn;

use Dpeuscher\BahnSearch\Bahn\FareSearchService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * Class FareSearchServiceTest
 *
 * @covers \Dpeuscher\BahnSearch\Bahn\FareSearchService
 * @covers \Dpeuscher\BahnSearch\Bahn\FindLocationService
 * @covers \Dpeuscher\BahnSearch\Entity\Connection
 */
class FareSearchServiceTest extends TestCase
{
    private const FIXTURE_FOLDER = __DIR__ . '/fixtures';

    public const FIXTURE_SUCCESSFUL_RESPONSE_MUNICH_HAMBURG = self::FIXTURE_FOLDER . '/FareSearchService_guzzlemock_%s.json';
    public const FIXTURE_SUCCESSFUL_RESPONSE_MUNICH_HAMBURG_RESULTS = self::FIXTURE_FOLDER . '/FareSearchService_guzzlemock_results.php.bin';
    public const FIXTURE_SUCCESSFUL_RESPONSE_MUNICH_HAMBURG_SEARCHTIME = self::FIXTURE_FOLDER . '/FareSearchService_guzzlemock_searchtime.php.bin';
    public const FIXTURE_SUCCESSFUL_RESPONSE_MUNICH_HAMBURG_RESULTS_LOG = self::FIXTURE_FOLDER . '/FareSearchService_guzzlemock_results.log';

    /**
     * @var FareSearchService
     */
    private $sut;

    public function testRecordNewFixtures()
    {
        $this->markTestSkipped('Only used to update fixtures');

        $container = [];
        $history = Middleware::history($container);
        $stack = HandlerStack::create();
        $stack->push($history);

        $guzzle = new Client(['handler' => $stack]);
        $findLocationServiceTest = new FindLocationServiceTest();
        $searchTime = new \DateTime();

        $this->sut = new FareSearchService($findLocationServiceTest->getMockSuccessfulResultService(), $guzzle, null,
            $searchTime);
        $results = $this->sut->findFares('München Hbf', 'Hamburg Hbf', (new \DateTime('tomorrow'))->setTime(22, 0), '',
            ['1', '2']);

        foreach ($container as $nr => $transaction) {
            /** @var Request $request */
            $request = $transaction['request'];
            /** @var Response $response */
            $response = $transaction['response'];

            $body = (string)$response->getBody();
            if (stripos($response->getHeader('Content-Type')[0], 'charset=ISO-8859-1') !== false) {
                $body = iconv('ISO-8859-1', 'UTF-8', $body);
            }
            $mock = [
                'request'  => [
                    'uri'    => (string)$request->getUri(),
                    'header' => $request->getHeaders(),
                    'method' => $request->getMethod(),
                ],
                'response' => [
                    'statusCode' => $response->getStatusCode(),
                    'header'     => $response->getHeaders(),
                    'body'       => $body,
                ],
            ];

            file_put_contents(sprintf(self::FIXTURE_SUCCESSFUL_RESPONSE_MUNICH_HAMBURG, $nr + 1),
                json_encode($mock, JSON_PRETTY_PRINT));
        }
        file_put_contents(self::FIXTURE_SUCCESSFUL_RESPONSE_MUNICH_HAMBURG_RESULTS, serialize($results));
        file_put_contents(self::FIXTURE_SUCCESSFUL_RESPONSE_MUNICH_HAMBURG_RESULTS_LOG, implode("\n", $results));
        file_put_contents(self::FIXTURE_SUCCESSFUL_RESPONSE_MUNICH_HAMBURG_SEARCHTIME, serialize($searchTime));
    }

    public function testFindFares()
    {
        $this->sut = $this->getMockSuccessfulResultService();
        /** @var \DateTime $dateTime */
        $dateTime = unserialize(file_get_contents(self::FIXTURE_SUCCESSFUL_RESPONSE_MUNICH_HAMBURG_SEARCHTIME));
        $dateTime->add(new \DateInterval('P1D'));
        $connections = $this->sut->findFares('München Hbf', 'Hamburg Hbf', (clone $dateTime)->setTime(22, 0),
            '', ['1', '2']);
        $results = unserialize(file_get_contents(self::FIXTURE_SUCCESSFUL_RESPONSE_MUNICH_HAMBURG_RESULTS));
        $this->assertEquals($results, $connections);
    }

    public function getMockSuccessfulResultService(): FareSearchService
    {
        $responses = [];
        for ($nr = 1; file_exists(sprintf(self::FIXTURE_SUCCESSFUL_RESPONSE_MUNICH_HAMBURG, $nr)); $nr++) {
            $data = json_decode(file_get_contents(sprintf(self::FIXTURE_SUCCESSFUL_RESPONSE_MUNICH_HAMBURG, $nr)),
                JSON_OBJECT_AS_ARRAY);
            array_push($responses,
                new Response($data['response']['statusCode'], $data['response']['header'],
                    $data['response']['body'])
            );
        }
        $mock = new MockHandler($responses);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $findLocationServiceTest = new FindLocationServiceTest();
        $searchTime = unserialize(file_get_contents(self::FIXTURE_SUCCESSFUL_RESPONSE_MUNICH_HAMBURG_SEARCHTIME));

        return new FareSearchService($findLocationServiceTest->getMockSuccessfulResultService(), $client, null,
            $searchTime);
    }
}
