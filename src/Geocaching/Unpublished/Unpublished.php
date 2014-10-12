<?php

/**
 * Unpublished Geocaches
 *
 * @author  Surfoo <surfooo@gmail.com>
 * @link    https://github.com/Surfoo/unpublished-geocaches
 * @license http://opensource.org/licenses/eclipse-2.0.php
 * @package Geocaching\Unpublished
 */

class Unpublished
{
    protected $raw_html       = null;

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

    public function __construct()
    {
        $this->list_attributes = json_decode(file_get_contents(ROOT . '/attributes.json'));
    }

    public function setRawHtml($content)
    {
        $this->raw_html = $content;
    }

    public function getCacheDetails()
    {
        global $header;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf(URL_GEOCACHE, $this->guid));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $_SESSION['cookie']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $result = curl_exec($ch);
        if (!$result) {
            renderAjax(array('success' => false, 'message' => 'Request error: ' . curl_error($ch)));
        }
        curl_close($ch);

        $this->setRawHtml($result);

        return $this->raw_html;
    }

    public function getGeocacheDatas()
    {
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

    public function setSomeBasicInformations()
    {
        if (!$this->name) {
            return false;
        }
        global $header;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf(URL_TILE, mt_rand(1, 4), $this->name));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $json_content = curl_exec($ch);
        if (!$json_content) {
            renderAjax(array('success' => false, 'message' => 'Request error: ' . curl_error($ch)));
        }
        curl_close($ch);

        $infos = json_decode($json_content);
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

    public function setGuid()
    {
        if (!$this->raw_html) {
            return false;
        }
        if (preg_match('/guid=\'(.*)\';/', $this->raw_html, $guid)) {
            $this->guid = $guid[1];
        } else {
            $this->errors[] = 'Unable to retrieve GUID.';
        }
    }

    public function setGcCode()
    {
        if (!$this->raw_html || !is_null($this->name)) {
            return false;
        }
        if (preg_match('/<span id="ctl00_ContentBody_CoordInfoLinkControl1_uxCoordInfoCode" class="CoordInfoCode">(.*)<\/span>/', $this->raw_html, $gccode)) {
            $this->name = $gccode[1];

            return true;
        }

        return false;
    }

    public function setCoordinates()
    {
        if (!$this->raw_html) {
            return false;
        }
        if (preg_match('/mapLatLng = {"lat":(.*),"lng":(.*),"type":.*,"name":".*"};/', $this->raw_html, $coordinates)) {
            $this->lat = $coordinates[1];
            $this->lng = $coordinates[2];
        } else {
            $this->errors[] = 'Unable to retrieve Latitude and Longitude.';
        }
    }

    public function setCacheId()
    {
        if (!$this->raw_html) {
            return false;
        }
        if (preg_match('/seek\/log.aspx\?ID=(\d+)/', $this->raw_html, $cache_id)) {
            $this->cache_id = $cache_id[1];
        } else {
            $this->errors[] = 'Unable to retrieve Cache ID.';
        }
    }

    public function setLocationUsername()
    {
        if (!$this->raw_html) {
            return false;
        }
        if (preg_match('/<title>\s*.*\((.*)\) in ([.+])?[,\s]?(.*) created by (.*)\s*<\/title>/msU', $this->raw_html, $matches)) {
            $this->state = $matches[2];
            $this->country = $matches[3];
            $this->placed_by = $matches[4];
        } else {
            $this->errors[] = 'Unable to retrieve State, Country and Placed By.';
        }
    }

    /*public function setOwnerId()
    {
        if (!$this->raw_html) {
            return false;
        }
        if (preg_match('/userInfo = {ID: (\d+)};/', $this->raw_html, $owner_id)) {
            $this->owner_id = $owner_id[1];
        } else {
            $this->errors[] = 'Unable to retrieve Owner ID.';
        }
    }*/

    public function setShortDescription()
    {
        if (!$this->raw_html) {
            return false;
        }
        if (preg_match('/<span id="ctl00_ContentBody_ShortDescription">(.*)<\/span>/msU', $this->raw_html, $short_description)) {
            $this->short_description = str_ireplace("\x0D", "", trim($short_description[1]));
            $this->short_desc_html   = ($this->short_description != strip_tags($this->short_description)) ? 'True' : 'False';
        } else {
            $this->errors[] = 'Unable to retrieve the Short Description.';
        }
    }

    public function setLongDescription()
    {
        if (!$this->raw_html) {
            return false;
        }
        if (preg_match('/<div class="UserSuppliedContent">\s*<span id="ctl00_ContentBody_LongDescription">(.*)<\/span>\s*<\/div>/msU', $this->raw_html, $long_description)) {
            $this->long_description = str_ireplace("\x0D", "", trim($long_description[1]));
            $this->long_desc_html   = ($this->long_description != strip_tags($this->long_description)) ? 'True' : 'False';
        } else {
            $this->errors[] = 'Unable to retrieve the Long Description.';
        }
    }

    public function setEncodedHints()
    {
        if (!$this->raw_html) {
            return false;
        }
        if (preg_match('/<div.*?id="div_hint" class="span-8 WrapFix">\s*(.*)\s*<\/div>/msU', $this->raw_html, $hint)) {
            $this->encoded_hints = str_ireplace("\x0D", '', trim($hint[1]));
            $this->encoded_hints = str_replace(array('<br />', '<br>'), "\n", $this->encoded_hints);

            $chars = str_split($this->encoded_hints);
            $encode = true;
            foreach($chars as &$char) {
                if(in_array($char, array('[', '<'))) {
                    $encode = false;
                    continue;
                }
                if(in_array($char, array(']', '>'))) {
                    $encode = true;
                    continue;
                }
                if($encode) {
                    $char = str_rot13($char);
                }
            }
            $this->encoded_hints = implode('', $chars);
        } else {
            $this->errors[] = 'Unable to retrieve Encoded Hints.';
        }
    }

    public function setAttributes()
    {
        if (!$this->raw_html) {
            return false;
        }
        if (preg_match_all('/attributes\/([a-z-_]+)-(yes|no).gif/i', $this->raw_html, $attributes)) {
            foreach ($attributes[1] as $key => $attribute) {
                if (!array_key_exists($attribute, $this->list_attributes)) {
                    $this->errors[] = 'Problem with "' . $attribute . '" attribute';
                    continue;
                }
                $this->attributes[] = ['id'  => $this->list_attributes->$attribute->id,
                                       'inc' => $attributes[2][$key] == 'yes' ? '1' : '0',
                                       'text'=> $this->list_attributes->$attribute->text];
            }
        }
    }

    public function setWaypoints()
    {
        if (!$this->raw_html || !preg_match('/<table class="Table" id="ctl00_ContentBody_Waypoints">\s*(.*)\s*<\/table>/msU',
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
            if($key % 2 == 0) {
                $counter++;

                preg_match('/lat=([\d.]*)&amp;lng=([\d.]*)/', $cells[7], $wptcoord);
                preg_match('/\((.*)\)/', $cells[5], $wpttype);
                preg_match('/>(.*)<\/a>/', $cells[5], $wptname);
                preg_match('/wpt.aspx\?WID=([a-z0-9-]*)/i', $cells[5], $wptwid);

                $this->waypoints[$counter]['lat']  = '';
                $this->waypoints[$counter]['lng']  = '';
                $this->waypoints[$counter]['type'] = trim($wpttype[1]);
                $this->waypoints[$counter]['name'] = trim($wptname[1]);
                $this->waypoints[$counter]['wid']  = trim($wptwid[1]);

                $coordinates = '';
                if(strpos($cells[6], '???') !== 0) {
                    $this->waypoints[$counter]['lat']  = trim($wptcoord[1]);
                    $this->waypoints[$counter]['lng']  = trim($wptcoord[2]);
                    $coordinates = substr($cells[6], 0, -6);
                }

                $this->long_description .= $this->waypoints[$counter]['type'] . ' - ' . $this->waypoints[$counter]['name'] . '<br />';
                $this->long_description .= $coordinates . '<br />';
                $this->long_desc_html    = 'True';
            }
            elseif(is_array($this->waypoints) && array_key_exists($counter, $this->waypoints)) {
                $this->waypoints[$counter]['note'] = trim($cells[2]);
                $this->long_description .= $this->waypoints[$counter]['note'] . '<br />';
            }
        }
    }
}
