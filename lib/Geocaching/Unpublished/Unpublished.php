<?php

class Unpublished
{
    public $raw_html = null;

    public $guid     = null;
    public $cache_id     = null;
    public $difficulty     = null;
    public $terrain     = null;
    public $container     = null;
    public $type     = null;
    public $owner     = null;
    public $owner_id     = null;
    public $urlname     = null;
    public $url     = null;
    public $name     = null;
    public $lat     = null;
    public $lng     = null;
    public $state     = null;
    public $country     = null;
    public $placed_by     = null;
    public $short_description     = null;
    public $long_description     = null;
    public $encoded_hints     = null;
    public $attributes     = null;

    public $data   = array();

    public $errors = array();

    public function __construct() {
        $this->list_attributes = json_decode(file_get_contents(ROOT . '/attributes.json'));
    }

    public function getCacheDetails() {
        global $header, $cookie_filename;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf(URL_GEOCACHE, $this->guid));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_filename);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $this->raw_html = curl_exec($ch);
        if(!$this->raw_html) {
            renderAjax(array('success' => false, 'message' => 'Request error: ' . curl_error($ch)));
        }
        curl_close($ch);
        if(!$this->raw_html) {
            return false;
        }
        return $this->raw_html;
    }

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
                     'long_description' => $this->long_description,
                     'encoded_hints' => $this->encoded_hints,
                     'attributes' => $this->attributes,
            );
    }

    public function setSomeBasicInformations() {
        if(!$this->name) {
            return false;
        }
        global $header; //, $cookie_filename;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf(URL_TILE, mt_rand(1, 4), $this->name));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_filename);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $json_content = curl_exec($ch);
        if(!$json_content) {
            renderAjax(array('success' => false, 'message' => 'Request error: ' . curl_error($ch)));
        }
        curl_close($ch);

        $infos = json_decode($json_content);
        if(!$infos || !$infos->status) {
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

    public function setGuid() {
        if(!$this->raw_html) {
            return false;
        }
        if(preg_match('/guid=\'(.*)\';/', $this->raw_html, $guid)) {
            $this->guid = $guid[1];
        }
        else {
            $this->errors[] = 'Unable to retrieve GUID.';
        }
    }

    public function setGcCode() {
        if(!$this->raw_html || !is_null($this->name)) {
            return false;
        }
        if(preg_match('/<span id="ctl00_ContentBody_CoordInfoLinkControl1_uxCoordInfoCode" class="CoordInfoCode">(.*)<\/span>/', $this->raw_html, $gccode)) {
            $this->name = $gccode[1];
            return true;
        }
        return false;
    }

    public function setCoordinates() {
        if(!$this->raw_html) {
            return false;
        }
        if(preg_match('/mapLatLng = {"lat":(.*),"lng":(.*),"type":.*,"name":".*"};/', $this->raw_html, $coordinates)) {
            $this->lat = $coordinates[1];
            $this->lng = $coordinates[2];
        }
        else {
            $this->errors[] = 'Unable to retrieve Latitude and Longitude.';
        }
    }

    public function setCacheId() {
        if(!$this->raw_html) {
            return false;
        }
        if(preg_match('/seek\/log.aspx\?ID=(\d+)/', $this->raw_html, $cache_id)) {
            $this->cache_id = $cache_id[1];
        }
        else {
            $this->errors[] = 'Unable to retrieve Cache ID.';
        }

    }

    public function setLocationUsername() {
        if(!$this->raw_html) {
            return false;
        }
        if(preg_match('/<title>\s*.*\((.*)\) in (.*), (.*) created by (.*)\s*<\/title>/msU', $this->raw_html, $matches)) {
            $this->state = $matches[2];
            $this->country = $matches[3];
            $this->placed_by = $matches[4];
        }
        else {
            $this->errors[] = 'Unable to retrieve State, Country and Placed By.';
        }
    }

    public function setOwnerId() {
        if(!$this->raw_html) {
            return false;
        }
        if(preg_match('/userInfo = {ID: (\d+)};/', $this->raw_html, $owner_id)) {
            $this->owner_id = $owner_id[1];
        }
        else {
            $this->errors[] = 'Unable to retrieve Owner ID.';
        }
    }

    public function setShortDescription() {
        if(!$this->raw_html) {
            return false;
        }
        if(preg_match('/<span id="ctl00_ContentBody_ShortDescription">(.*)<\/span>/msU', $this->raw_html, $short_description)) {
            $this->short_description = str_ireplace("\x0D", "", trim($short_description[1]));
        }
        else {
            $this->errors[] = 'Unable to retrieve the Short Description.';
        }
    }

    public function setLongDescription() {
        if(!$this->raw_html) {
            return false;
        }
        if(preg_match('/<div class="UserSuppliedContent">\s*<span id="ctl00_ContentBody_LongDescription">(.*)<\/span>\s*<\/div>/msU', $this->raw_html, $long_description)) {
            $this->long_description = str_ireplace("\x0D", "", trim($long_description[1]));
        }
        else {
            $this->errors[] = 'Unable to retrieve the Long Description.';
        }
    }

    public function setEncodedHints() {
        if(!$this->raw_html) {
            return false;
        }
        if(preg_match('/<div id="div_hint" class="span-8 WrapFix">\s*(.*)\s*<\/div>/msU', $this->raw_html, $hint)) {
            $this->encoded_hints = str_ireplace("\x0D", "", trim($hint[1]));
        }
        else {
            $this->errors[] = 'Unable to retrieve Encoded Hints.';
        }
    }

    public function setAttributes() {
        if(!$this->raw_html) {
            return false;
        }
        if(preg_match_all('/attributes\/([a-z-_]+)-(yes|no).gif/i', $this->raw_html, $attributes)) {
            foreach ($attributes[1] as $key => $attribute) {
                if(!array_key_exists($attribute, $this->list_attributes)) {
                    $this->errors[] = 'Problems with  '. $attribute . ' attribute';
                    continue;
                }
                $this->attributes[] = ['id'  => $this->list_attributes->$attribute->id, 
                                       'inc' => $attributes[2][$key] == 'yes' ? '1' : '0',
                                       'text'=> $this->list_attributes->$attribute->text];
            }
        }
    }
    
}