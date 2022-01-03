<?php

namespace App\Service;

use Geocaching\GeocachingSdk;
use GuzzleHttp\Psr7\Response;
use Http\Client\Common\Exception\ClientErrorException;
use Http\Client\Common\Exception\HttpClientNoMatchException;
use Http\Client\Exception\HttpException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Throwable;

class UnpublishedService
{
    const MAX_REQUESTS_PER_MINUTE = 30;

    private GeocachingSdk $api;

    /**
     * @param GeocachingSdk $sdk
     */
    public function __construct(GeocachingSdk $api)
    {
        $this->api = $api;
    }

    public function getUnpublishedGeocaches(int $chunk = 100): array
    {
        return $this->handleSearchGeocaches('isPublished:false', $chunk);
    }

    public function getGeocaches(array $geocodes, bool $lite = false): array
    {
        return $this->handleGetGeocaches($geocodes, $lite);
    }

    protected function handleSearchGeocaches(string $query, int $chunk): array
    {
        $geocaches = [];

        $totalCount = (int) ($this->handleRequestSearchGeocaches($query, 0, 0))->getHeader('x-total-count')[0];
        $countRequests = ceil($totalCount / $chunk);

        $i = 0;
        while (count($geocaches) < $totalCount) {
            $result    = $this->handleRequestSearchGeocaches($query, $chunk, $chunk * $i);
            if(!$this->isOk($result->getStatusCode())) {
                throw new \Exception("Bad Request");
            }
            $geocaches = array_merge($geocaches, json_decode($result->getBody()));
            $i++;
            if ($countRequests > self::MAX_REQUESTS_PER_MINUTE) {
                sleep(2);
            }
        }

        return $geocaches;
    }

    protected function handleGetGeocaches(array $geocodes, bool $lite = false): array
    {
        $take = 50;
        $geocodesChunked = array_chunk($geocodes, $take);
        $countRequests   = count($geocodesChunked);

        $data = [];

        foreach ($geocodesChunked as $geocodes) {
            $result = $this->handleRequestGetGeocaches(implode(',', $geocodes), $take, $lite);
            if(!$this->isOk($result->getStatusCode())) {
                throw new \Exception("Bad Request");
            }
            $data = array_merge($data, json_decode($result->getBody()));
            if ($countRequests > self::MAX_REQUESTS_PER_MINUTE) {
                sleep(2);
            }
        }

        return $data;
    }

    protected function handleRequestSearchGeocaches(string $query, int $take, int $skip): ResponseInterface
    {
        return $this->api->searchGeocaches(
            ['lite'  => true,
             'q'      => $query,
             'sort'   => 'name',
             'take'   => $take,
             'skip'   => $skip,
             'fields' => 'referenceCode,name,url,geocacheType'
            ]
        );
    }

    protected function handleRequestGetGeocaches(string $geocodes, int $take, bool $lite): ResponseInterface
    {
        if ($lite) {
            $fields = 'referenceCode,name,url,geocacheType';
        } else {
            $fields = 'referenceCode,publishedDate,ownerAlias,'.
                      'OwnerCode,owner[username],name,difficulty,'.
                      'terrain,favoritePoints,placedDate,geocacheType,'.
                      'geocacheSize,status,location,postedCoordinates,'.
                      'shortDescription,longDescription,hints,attributes,'.
                      'relatedWebPage,url,containsHtml,additionalWaypoints';
        }

        return $this->api->getGeocaches(
            ['referenceCodes' => $geocodes,
             'take'           => $take,
             'fields'         => $fields
            ]
        );
    }

    public function isOk(int $statusCode): bool
    {
        return $statusCode >= 200 && $statusCode < 299;
    }
}