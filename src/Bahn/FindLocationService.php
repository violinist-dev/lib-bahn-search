<?php

namespace Dpeuscher\BahnSearch\Bahn;

use GuzzleHttp\Client;

/**
 * @category  lib-bahn-search
 * @copyright Copyright (c) 2018 Dominik Peuscher
 */
class FindLocationService
{
    private const URL = 'https://reiseauskunft.bahn.de/bin/ajax-getstop.exe/dn?REQ0JourneyStopsS0A=7&REQ0JourneyStopsB=12&REQ0JourneyStopsS0G=%s';
    private const REMOVE_PREFIX = 'SLs.sls=';
    private const REMOVE_POSTFIX = ';SLs.showSuggestion();';

    /**
     * @var Client
     */
    private $guzzle;

    /**
     * FindLocationService constructor.
     *
     * @param Client $guzzle
     */
    public function __construct(Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function findLocationCodeByName(string $name): string
    {
        $url = sprintf(self::URL, urlencode(utf8_encode($name)));
        $response = $this->guzzle->get($url, ['headers' => ['Accept' => 'text/plain; charset=utf-8']]);
        $content = $response->getBody()->getContents();
        if (stripos($response->getHeader('Content-Type')[0], 'charset=ISO-8859-1') !== false) {
            $content = iconv('ISO-8859-1', 'UTF-8', $content);
        }
        if (\mb_strpos($content, self::REMOVE_PREFIX) === 0) {
            $content = \mb_substr($content, \mb_strlen(self::REMOVE_PREFIX));
        }
        if (\mb_substr($content, -\mb_strlen(self::REMOVE_POSTFIX)) === self::REMOVE_POSTFIX) {
            $content = \mb_substr($content, 0, -\mb_strlen(self::REMOVE_POSTFIX));
        }
        $json = json_decode($content);

        return $json->suggestions[0]->id ?? null;
    }
}
