<?php

/**
 * Unpublished Geocaches
 *
 * @author  Surfoo <surfooo@gmail.com>
 * @link    https://github.com/Surfoo/unpublished-geocaches
 * @license http://opensource.org/licenses/eclipse-2.0.php
 * @package Geocaching\Unpublished
 */

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;
use GuzzleHttp\Exception\ClientException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Unpublished
{
    protected $raw_html       = null;
    protected $document       = null;

    public $guid              = null;
    public $cache_id          = null;
    public $date              = null;
    public $difficulty        = null;
    public $terrain           = null;
    public $container         = null;
    public $type              = null;
    public $owner             = null;
    public $owner_id          = null;
    public $urlname           = null;
    public $url               = null;
    public $name              = null;
    public $lat               = null;
    public $lng               = null;
    public $state             = null;
    public $country           = null;
    public $placed_by         = null;
    public $short_description = null;
    public $short_desc_html   = null;
    public $long_description  = null;
    public $long_desc_html    = null;
    public $encoded_hints     = null;
    public $attributes        = null;
    public $waypoints         = null;

    public $errors = array();

    /**
     * __construct
     * @param SessionCookieJar $cookieJar
     */
    public function __construct(SessionCookieJar $cookieJar) {
        $this->cookie = $cookieJar;
        $this->list_attributes = json_decode(file_get_contents(ROOT . '/attributes.json'));

        // create a log channel
        $this->logger = new Logger('unpublished');
        $this->logger->pushHandler(new StreamHandler(ROOT . '/logs/unpublished.log', Logger::ERROR));
    }

    /**
     * set raw HTML
     * @param string $content
     */
    public function setRawHtml($content) {
        $this->raw_html = $content;

        $this->document = new DomDocument;
        $this->document->strictErrorChecking = false;
        libxml_use_internal_errors(true);
        $this->document->loadHTML($content);
        libxml_clear_errors();
    }

    /**
     * getCacheDetails
     * @return string $this->raw_html
     */
    public function getCacheDetails() {
        $client = new Client([
            'base_uri' => sprintf(URL_GEOCACHE, $this->name),
            'timeout'  => 30,
            'cookies' => $this->cookie
        ]);
        try {
            $response = $client->request('GET', sprintf(URL_GEOCACHE, $this->name), ['http_errors' => true]);
        } catch (ClientException $e) {
            renderAjax(array('success' => false,
                             'gccode' => $this->name,
                             'message' => $e->getResponse()->getReasonPhrase() . ' (' . $e->getResponse()->getStatusCode() . ')'));
        } catch(Exception $e) {
            renderAjax(array('success' => false, 'gccode' => $this->name, 'message' => $e->getMessage()));
        }

        $htmlResponse = (string) $response->getBody();

        $this->setRawHtml($htmlResponse);

        return $this->raw_html;
    }

    /**
     * getGeocacheDatas
     * @return void
     */
    public function getGeocacheDatas() {
        return array('guid' => $this->guid,
                     'cache_id' => $this->cache_id,
                     'date' => $this->date,
                     'difficulty' => $this->difficulty,
                     'terrain' => $this->terrain,
                     'container' => $this->container,
                     'type' => $this->type,
                     'owner' => $this->owner,
                     'owner_id' => $this->owner_id,
                     'urlname' => $this->urlname,
                     'url' => $this->url,
                     'name' => $this->name,
                     'lat' => $this->lat,
                     'lng' => $this->lng,
                     'state' => $this->state,
                     'country' => $this->country,
                     'placed_by' => $this->placed_by,
                     'short_description' => $this->short_description,
                     'short_desc_html' => $this->short_desc_html,
                     'long_description' => $this->long_description,
                     'long_desc_html' => $this->long_desc_html,
                     'encoded_hints' => $this->encoded_hints,
                     'attributes' => $this->attributes,
                     'additional_waypoints' => $this->waypoints,
                     'time' => date('c')
            );
    }

    /**
     * setSomeBasicInformations
     * @return void
     */
    public function setSomeBasicInformations() {
        if (!$this->name) {
            return false;
        }

        $client = new Client([
            'base_uri' => sprintf(URL_TILE, mt_rand(1, 4), $this->name),
            'timeout'  => 30,
            'cookies' => $this->cookie
        ]);

        $response = $client->request('GET', sprintf(URL_TILE, mt_rand(1, 4), $this->name));
        $jsonResponse = (string) $response->getBody();

        $infos = json_decode($jsonResponse);
        if (!$infos || !$infos->status) {
            renderAjax(array('success' => false, 'guid' => $this->guid, 'message' => 'Unable to retrieve some informations.'));
        }
        $this->name       = $infos->data[0]->gc;
        $this->difficulty = $infos->data[0]->difficulty->text;
        $this->terrain    = $infos->data[0]->terrain->text;
        $this->container  = $infos->data[0]->container->text;
        $this->type       = $infos->data[0]->type->text;
        $this->owner      = $infos->data[0]->owner->text;
        $this->urlname    = $infos->data[0]->name;

        $this->url        = sprintf(URL_GEOCACHE, $this->guid);
        $d = explode('/', $infos->data[0]->hidden);
        $this->date = date('c', mktime(0, 0, 0, $d[0], $d[1], $d[2]));
    }

    /**
     * setGuid
     * @return void
     */
    public function setGuid() {
        if (!$this->raw_html) {
            return false;
        }
        if (preg_match('/guid=([a-z0-9-]{36})/', $this->raw_html, $guid)) {
            $this->guid = $guid[1];
        } else {
            $this->errors[] = 'GUID';
            $this->logger->error('GUID is missing', ['name' => $this->name]);
        }
    }

    /**
     * setGcCode
     * @return boolean
     */
    public function setGcCode() {
        if (!$this->document) {
            return false;
        }

        if (!is_null($this->name)) {
            return true;
        }

        $id = $this->document->getElementById('ctl00_ContentBody_CoordInfoLinkControl1_uxCoordInfoCode');
        if (isset($id->textContent)) {
            $this->name = $id->textContent;
            return true;
        }

        return false;
    }

    /**
     * setCoordinates
     */
    public function setCoordinates() {
        if (!$this->raw_html) {
            return false;
        }
        if (preg_match('/mapLatLng = {"lat":(.*),"lng":(.*),"type":.*,"name":".*"};/', $this->raw_html, $coordinates)) {
            $this->lat = $coordinates[1];
            $this->lng = $coordinates[2];
        } else {
            $this->errors[] = 'Latitude and Longitude';
            $this->logger->error('Latitude and Longitude are missing', ['name' => $this->name]);
        }
    }

    /**
     * setCacheId
     */
    public function setCacheId() {
        if (!$this->raw_html) {
            return false;
        }
        if (preg_match('/seek\/log.aspx\?id=(\d+)/', $this->raw_html, $cache_id)) {
            $this->cache_id = $cache_id[1];
        } else {
            $this->errors[] = 'Cache ID';
            $this->logger->error('Cache ID is missing', ['name' => $this->name]);
        }
    }

    /**
     * setLocation
     */
    public function setLocation() {
        if (!$this->raw_html) {
            return false;
        }

        if (preg_match('#<span id="ctl00_ContentBody_Location">[^\s]+ (?:<a href=[^>]*>)?(.*?)<#', $this->raw_html, $matche)) {
            if(isset($matche[1])) {
                $matches = explode(', ', $matche[1]);
            }
            if (!isset($matches[1]) || $matches[1] == '') {
                $this->state   = '';
                $this->country = $matches[0];
            } else {
                $this->state   = $matches[0];
                $this->country = $matches[1];
            }
        } else {
            $this->errors[] = 'State, Country';
            $this->logger->error('State, Country are missing', ['name' => $this->name]);
        }
    }

    /**
     * setUsername
     */
    public function setUsername() {
        if (!$this->raw_html) {
            return false;
        }
        if (preg_match('#<div id="ctl00_ContentBody_mcd1">[^<]+<a href="[^"]+">([^<]+)</a>#msU', $this->raw_html, $matches)) {
            $this->placed_by = $matches[1];
        } else {
            $this->errors[] = 'Placed By';
            $this->logger->error('Placed By is missing', ['name' => $this->name]);
        }
    }

    /**
     * setOwnerId
     */
    /*public function setOwnerId() {
        if (!$this->raw_html) {
            return false;
        }
        if (preg_match('/userInfo = {ID: (\d+)};/', $this->raw_html, $owner_id)) {
            $this->owner_id = $owner_id[1];
        } else {
            $this->errors[] = 'Unable to retrieve Owner ID.';
        }
    }*/

    /**
     * setShortDescription
     */
    public function setShortDescription() {
        if (!$this->document) {
            return false;
        }
        $id = $this->document->getElementById('ctl00_ContentBody_ShortDescription');
        if(isset($id->textContent)) {
            $this->short_description = str_ireplace("\x0D", "", trim($id->textContent));
            $this->short_desc_html   = ($this->short_description != strip_tags($this->short_description)) ? 'True' : 'False';
        } else {
            $this->errors[] = 'Short Description';
            $this->logger->error('Short Description is missing', ['name' => $this->name]);
        }
    }

    /**
     * setLongDescription
     */
    public function setLongDescription() {
        if (!$this->document) {
            return false;
        }
        $id = $this->document->getElementById('ctl00_ContentBody_LongDescription');
        if(isset($id->textContent)) {
            $this->long_description = str_ireplace("\x0D", "", trim($id->textContent));
            $this->long_desc_html   = ($this->long_description != strip_tags($this->long_description)) ? 'True' : 'False';
        } else {
            $this->errors[] = 'Long Description';
            $this->logger->error('Long Description is missing', ['name' => $this->name]);
        }
    }

    /**
     * setEncodedHints
     */
    public function setEncodedHints() {
        if (!$this->document) {
            return false;
        }

        $id = $this->document->getElementById('div_hint');
        if(isset($id->textContent)) {
            $this->encoded_hints = str_ireplace("\x0D", '', trim($id->textContent));
            $this->encoded_hints = str_replace(array('<br />', '<br>'), "\n", $this->encoded_hints);

            $chars = str_split($this->encoded_hints);
            $encode = true;
            foreach ($chars as &$char) {
                if (in_array($char, array('[', '<'))) {
                    $encode = false;
                    continue;
                }
                if (in_array($char, array(']', '>'))) {
                    $encode = true;
                    continue;
                }
                if ($encode) {
                    $char = str_rot13($char);
                }
            }
            $this->encoded_hints = implode('', $chars);
        } else {
            $this->errors[] = 'Encoded Hints';
            $this->logger->error('Encoded Hints is missing', ['name' => $this->name]);
        }
    }

    /**
     * setAttributes
     */
    public function setAttributes() {
        if (!$this->raw_html) {
            return false;
        }
        if (preg_match_all('/attributes\/([a-z-_]+)-(yes|no).gif/i', $this->raw_html, $attributes)) {
            foreach ($attributes[1] as $key => $attribute) {
                if (!array_key_exists($attribute, $this->list_attributes)) {
                    $this->errors[] = htmlentities('"' . $attribute . '" attribute is unknown');
                    $this->logger->error('Attribute is missing', ['name' => $this->name, 'attribute' => $attribute]);
                    continue;
                }
                $this->attributes[] = ['id'  => $this->list_attributes->$attribute->id,
                                       'inc' => $attributes[2][$key] == 'yes' ? '1' : '0',
                                       'text'=> $this->list_attributes->$attribute->text];
            }
        }
    }

    /**
     * setWaypoints
     */
    public function setWaypoints() {
        if (!$this->raw_html || !preg_match('/<table class="Table alternating-row-stacked" id="ctl00_ContentBody_Waypoints">\s*(.*)\s*<\/table>/msU',
                                            $this->raw_html, $waypoint_html)) {
            return false;
        }

        $wpBegin = strrpos($waypoint_html[1], '<tbody>');
        $wpEnd = strrpos($waypoint_html[1], '</tbody>');
        $waypoint_html = substr($waypoint_html[1], $wpBegin + 7, $wpEnd - $wpBegin + 7);

        preg_match_all('/<tr.*>(.*)<\/tr>/msU', $waypoint_html, $lines);
        $counter = 0;

        $this->long_description .= "\n" . '<p>Additional Hidden Waypoints</p>';

        foreach ($lines[1] as $key => $line) {
            preg_match_all('/<td.*>(.*)<\/td>/msU', $line, $cells);

            $cells = array_map('trim', $cells[1]);

            if ($key % 2 == 0) {
                $counter++;

                preg_match('/\((.*)\)/', $cells[4], $wpttype);
                preg_match('/>(.*)<\/a>/', $cells[4], $wptname);
                preg_match('/wpt.aspx\?WID=([a-z0-9-]*)/i', $cells[4], $wptwid);
                $this->waypoints[$counter]['lat']  = '';
                $this->waypoints[$counter]['lng']  = '';
                $this->waypoints[$counter]['type'] = trim($wpttype[1]);
                $this->waypoints[$counter]['name'] = trim($wptname[1]);
                $this->waypoints[$counter]['wid']  = trim($wptwid[1]);

                $coordinates = '';
                $cells[5] = trim(html_entity_decode($cells[5]), chr(0xC2) . chr(0xA0));
                if (strpos($cells[5], '???') !== 0) {
                    preg_match_all('/([NSWE\d]+)/', $cells[5], $numbers);

                    $decimalCoordinates = $this->degreeDecimalToDecimal($numbers[0]);
                    $this->waypoints[$counter]['lat']  = $decimalCoordinates['latitude'];
                    $this->waypoints[$counter]['lng']  = $decimalCoordinates['longitude'];
                    $coordinates = substr($cells[5], 0, -6);
                }

                $this->long_description .= $this->waypoints[$counter]['type'] . ' - ' . $this->waypoints[$counter]['name'] . '<br />';
                $this->long_description .= $coordinates . '<br />';
                $this->long_desc_html    = 'True';
            } elseif (is_array($this->waypoints) && array_key_exists($counter, $this->waypoints)) {
                $this->waypoints[$counter]['note'] = $this->br2nl(trim($cells[2]));
                $this->long_description .= $this->waypoints[$counter]['note'] . '<br />';
            }
        }
    }

    /**
     * degreeDecimalToDecimal
     * @param  array  $coordinates
     * @return array
     */
    protected function degreeDecimalToDecimal(array $coordinates) {
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

    /**
     * br2nl
     * @param  string $string
     * @return string
     */
    protected function br2nl($string) {
        return preg_replace('#<br\s*?/?>#i', "\n", $string);
    }

}
