<?php

/**
 * Unpublished Geocaches
 *
 * @author  Surfoo <surfooo@gmail.com>
 * @link    https://github.com/Surfoo/unpublished-geocaches
 * @license http://opensource.org/licenses/eclipse-2.0.php
 * @package Geocaching\Unpublished
 */

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Geocaching\Sdk\GeocachingSdk;
use Geocaching\Exception\GeocachingSdkException;
use Geocaching\Lib\Adapters\GuzzleHttpClient;

class Unpublished
{

    const MAX_REQUESTS_PER_MINUTE = 30;

    /**
     * @var GeocachingSdk
     */
    protected $sdk;

    /**
     * @param GeocachingSdk $sdk
     */
    public function __construct(GeocachingSdk $sdk)
    {
        $this->sdk = $sdk;
    }

    /**
     * getMyProfile
     *
     * @return array
     */
    public function getMyProfile(): array
    {
        return ($this->sdk->getUser('me', [
                              'fields' => 'referenceCode,username,hideCount,findCount,favoritePoints,membershipLevelId,avatarUrl,bannerUrl,url,homeCoordinates,geocacheLimits'
                        ]))->getBody(true);
    }

    /**
     * getUnpublishedGeocaches
     *
     * @return array
     */
    public function getUnpublishedGeocaches(int $chunk = 100): array
    {
        return $this->handleSearchGeocaches('isPublished:false', $chunk);
    }

    /**
     * @param string $query
     * @param int $chunk
     * 
     * @return array
     */
    public function searchGeocaches(string $query, int $chunk): array
    {
        return $this->handleSearchGeocaches($query, $chunk);
    }

    /**
     * getGeocaches
     *
     * @param array $geocodes
     *
     * @return array
     */
    public function getGeocaches(array $geocodes): array
    {
        return $this->handleGetGeocaches($geocodes);
    }

    /**
     * handleSearchGeocaches
     * 
     * @param string $query
     * @param int    $chunk
     * 
     * @return array
     */
    protected function handleSearchGeocaches(string $query, int $chunk): array
    {
        $geocaches = [];
        $totalCount = (int) ($this->handleRequestSearchGeocaches($query, 0, 0))->getHeader('x-total-count')[0];

        $countRequests = ceil($totalCount / $chunk);

        $i = 0;
        while (count($geocaches) < $totalCount) {
            $result    = $this->handleRequestSearchGeocaches($query, $chunk, $chunk * $i);
            $geocaches = array_merge($geocaches, $result->getBody(true));
            $i++;
            if ($countRequests > self::MAX_REQUESTS_PER_MINUTE) {
                sleep(2);
            }
        }

        return $geocaches;
    }

    /**
     * handleGetGeocaches
     * 
     * @param array $geocodes
     * 
     * @return array
     */
    protected function handleGetGeocaches(array $geocodes): array
    {
        $take = 50;
        $geocodesChunked = array_chunk($geocodes, $take);
        $countRequests   = count($geocodesChunked);

        $data = [];

        foreach($geocodesChunked as $geocodes) {
            $result = $this->handleRequestGetGeocaches(implode(',', $geocodes), $take);
            $data   = array_merge($data, $result->getBody(true));
            if ($countRequests > self::MAX_REQUESTS_PER_MINUTE) {
                sleep(2);
            }
        }

        return $data;
    }

    /**
     * handleSearchGeocaches
     *
     * @param string $query
     * @param int    $take
     * @param int    $skip
     *
     * @return GuzzleHttpClient
     */
    protected function handleRequestSearchGeocaches(string $query, int $take, int $skip): GuzzleHttpClient
    {
        return $this->sdk->searchGeocaches(
            ['lite'  => true,
            'q'      => $query,
            'sort'   => 'name',
            'take'   => $take,
            'skip'   => $skip,
            'fields' => 'referenceCode,name,url,geocacheType'
            ]);
    }

    /**
     * handleGetGeocaches
     *
     * @param string $geocodes
     * @param int $take
     *
     * @return GuzzleHttpClient
     */
    public function handleRequestGetGeocaches(string $geocodes, int $take): GuzzleHttpClient
    {
        return $this->sdk->getGeocaches(
            ['referenceCodes' => $geocodes,
             'take'           => $take,
             'fields'         => 'referenceCode,publishedDate,ownerAlias,OwnerCode,owner[username],name,difficulty,terrain,favoritePoints,placedDate,geocacheType,geocacheSize,status,location,postedCoordinates,shortDescription,longDescription,hints,attributes,relatedWebPage,url,containsHtml,additionalWaypoints',]);
    }

    /**
     * @param array $coordinates
     * 
     * @return array
     */
    protected function degreeDecimalToDecimal(array $coordinates): array {
        $longitude = $coordinates[5] + round((($coordinates[6] . '.' . $coordinates[7]) / 60), 5);
        if (strtoupper($coordinates[4]) == 'W') {
            $longitude *=-1;
        }
        $latitude = $coordinates[1] + round((($coordinates[2] . '.' . $coordinates[3]) / 60), 5);
        if (strtoupper($coordinates[0]) == 'S') {
            $latitude *=-1;
        }

        return array('longitude' => $longitude,
                     'latitude'  => $latitude);
    }
}
