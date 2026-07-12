<?php

namespace App\Service;

use Geocaching\Exception\RateLimitExceededException;
use Geocaching\GeocachingSdk;
use Http\Client\Exception\HttpException;
use Psr\Http\Message\ResponseInterface;

class UnpublishedService
{
    public function __construct(private readonly GeocachingSdk $api)
    {
    }

    /**
     * @return array<int, mixed>
     */
    public function getUnpublishedGeocaches(int $chunk = 100): array
    {
        return $this->handleSearchGeocaches('isPublished:false', $chunk);
    }

    /**
     * @param string[] $geocodes
     *
     * @return array<int, mixed>
     */
    public function getGeocaches(array $geocodes, bool $lite = false): array
    {
        return $this->handleGetGeocaches($geocodes, $lite);
    }

    /**
     * @return array<int, mixed>
     */
    protected function handleSearchGeocaches(string $query, int $chunk): array
    {
        $geocaches = [];

        $countResponse = $this->request(
            fn () => $this->handleRequestSearchGeocaches($query, 0, 0),
            'Failed to count geocaches for query "' . $query . '"'
        );

        $totalCountHeader = $countResponse->getHeader('x-total-count');
        if (!isset($totalCountHeader[0])) {
            throw new GeocachingApiException($countResponse->getStatusCode(), 'Missing x-total-count header for query "' . $query . '"');
        }

        $totalCount = (int) $totalCountHeader[0];

        $i = 0;
        while (count($geocaches) < $totalCount) {
            $result = $this->request(
                fn () => $this->handleRequestSearchGeocaches($query, $chunk, $chunk * $i),
                'Failed to search geocaches for query "' . $query . '"'
            );
            $geocaches = array_merge($geocaches, json_decode($result->getBody()));
            ++$i;
        }

        return $geocaches;
    }

    /**
     * @param string[] $geocodes
     *
     * @return array<int, mixed>
     */
    protected function handleGetGeocaches(array $geocodes, bool $lite = false): array
    {
        $take            = 50;
        $geocodesChunked = array_chunk($geocodes, $take);

        $data = [];

        foreach ($geocodesChunked as $geocodes) {
            $result = $this->request(
                fn () => $this->handleRequestGetGeocaches(implode(',', $geocodes), $take, $lite),
                'Failed to fetch geocaches'
            );
            $data = array_merge($data, json_decode($result->getBody()));
        }

        return $data;
    }

    /**
     * Issues an SDK request and normalizes both a non-2xx response and an
     * HttpException raised by the SDK's reliability plugins (retries are
     * already exhausted by the time we see this) into a GeocachingApiException.
     */
    private function request(\Closure $call, string $errorMessage): ResponseInterface
    {
        try {
            $response = $call();
        } catch (RateLimitExceededException $e) {
            throw new GeocachingApiException($e->getResponse()->getStatusCode(), $errorMessage . ': rate limit exceeded', $e->getRetryAfterSeconds(), $e);
        } catch (HttpException $e) {
            throw new GeocachingApiException($e->getResponse()->getStatusCode(), $errorMessage, previous: $e);
        }

        if (!$this->isOk($response->getStatusCode())) {
            throw new GeocachingApiException($response->getStatusCode(), $errorMessage);
        }

        return $response;
    }

    protected function handleRequestSearchGeocaches(string $query, int $take, int $skip): ResponseInterface
    {
        return $this->api->searchGeocaches(
            ['lite'      => true,
                'q'      => $query,
                'sort'   => 'name',
                'take'   => $take,
                'skip'   => $skip,
                'fields' => 'referenceCode,name,url,geocacheType',
            ]
        );
    }

    protected function handleRequestGetGeocaches(string $geocodes, int $take, bool $lite): ResponseInterface
    {
        if ($lite) {
            $fields = 'referenceCode,name,url,geocacheType';
        } else {
            $fields = 'referenceCode,publishedDate,ownerAlias,' .
                      'OwnerCode,owner[username],name,difficulty,' .
                      'terrain,favoritePoints,placedDate,geocacheType,' .
                      'geocacheSize,status,location,postedCoordinates,' .
                      'shortDescription,longDescription,hints,attributes,' .
                      'relatedWebPage,url,containsHtml,additionalWaypoints';
        }

        return $this->api->getGeocaches(
            ['referenceCodes' => $geocodes,
                'take'        => $take,
                'fields'      => $fields,
            ]
        );
    }

    public function isOk(int $statusCode): bool
    {
        return $statusCode >= 200 && $statusCode < 299;
    }
}
