<?php

require dirname(__DIR__) . '/config.php';

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' || 
    !array_key_exists('username', $_SESSION)) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

//Attributes
$list_attributes = json_decode(file_get_contents(ROOT . '/attributes.json'));

$cookie_filename = sprintf(COOKIE_FILENAME, md5($_SESSION['username']));

$errors = [];

$cache['guid'] = $_POST['guid'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, sprintf(URL_GEOCACHE, $cache['guid']));
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_filename);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$content = curl_exec($ch);
if(!$content) {
    renderAjax(array('success' => false, 'message' => 'Request error: ' . curl_error($ch)));
}
curl_close($ch);

/**
 * Fetch informations
 */

//GCCODE
if(preg_match('/<span id="ctl00_ContentBody_CoordInfoLinkControl1_uxCoordInfoCode" class="CoordInfoCode">(.*)<\/span>/', $content, $gccode)) {
    $cache['name'] = $gccode[1];
}
else {
    renderAjax(array('success' => false, 'guid' => $cache['guid'], 'message' => 'Unable to retrieve the GC code.'));
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, sprintf(URL_TILE, mt_rand(1, 4), $cache['name']));
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_filename);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$json_content = curl_exec($ch);
if(!$json_content) {
    renderAjax(array('success' => false, 'message' => 'Request error: ' . curl_error($ch)));
}
curl_close($ch);
$infos = json_decode($json_content);
if(!$infos || !$infos->status) {
    renderAjax(array('success' => false, 'guid' => $cache['guid'], 'message' => 'Unable to retrieve some informations.'));
}

$cache['difficulty'] = $infos->data[0]->difficulty->text;
$cache['terrain']    = $infos->data[0]->terrain->text;
$cache['container']  = $infos->data[0]->container->text;
$cache['type']       = $infos->data[0]->type->text;
$cache['owner']      = $infos->data[0]->owner->text;
$cache['urlname']    = $infos->data[0]->name;
$cache['url'] = sprintf(URL_GEOCACHE, $cache['guid']);

$d = explode('/', $infos->data[0]->hidden);
$cache['date'] = date('c', mktime(0, 0, 0, $d[0], $d[1], $d[2]));

//coordonnées
if(preg_match('/mapLatLng = {"lat":(.*),"lng":(.*),"type":.*,"name":".*"};/', $content, $coordinates)) {
    $cache['lat'] = $coordinates[1];
    $cache['lng'] = $coordinates[2];
}
else {
    $errors[] = 'Unable to retrieve Latitude and Longitude.';
}

//Cache id
if(preg_match('/seek\/log.aspx\?ID=(\d+)/', $content, $cache_id)) {
    $cache['cache_id'] = $cache_id[1];
}
else {
    $errors[] = 'Unable to retrieve Cache ID.';
}

//Location/Username
if(preg_match('/<title>\s*.*\((.*)\) in (.*), (.*) created by (.*)\s*<\/title>/msU', $content, $matches)) {
    $cache['state'] = $matches[2];
    $cache['country'] = $matches[3];
    $cache['placed_by'] = $matches[4];
}
else {
    $errors[] = 'Unable to retrieve State, Country and Placed By.';
}

//owner Id
if(preg_match('/userInfo = {ID: (\d+)};/', $content, $owner_id)) {
    $cache['owner_id'] = $owner_id[1];
}
else {
    $errors[] = 'Unable to retrieve Owner ID.';
}

//Short description
if(preg_match('/<span id="ctl00_ContentBody_ShortDescription">(.*)<\/span>/msU', $content, $short_description)) {
    $cache['short_description'] = str_ireplace("\x0D", "", trim($short_description[1]));
}
else {
    $errors[] = 'Unable to retrieve the Short Description.';
}

//Long description
if(preg_match('/<div class="UserSuppliedContent">\s*<span id="ctl00_ContentBody_LongDescription">(.*)<\/span>\s*<\/div>/msU', $content, $long_description)) {
    $cache['long_description'] = str_ireplace("\x0D", "", trim($long_description[1]));
}
else {
    $errors[] = 'Unable to retrieve the Long Description.';
}

//Hint
if(preg_match('/<div id="div_hint" class="span-8 WrapFix">\s*(.*)\s*<\/div>/msU', $content, $hint)) {
    $cache['encoded_hints'] = str_ireplace("\x0D", "", trim($hint[1]));
}
else {
    $errors[] = 'Unable to retrieve Encoded Hints.';
}

//Attributes
if(preg_match_all('/attributes\/([a-z-_]+)-(yes|no).gif/i', $content, $attributes)) {
    foreach ($attributes[1] as $key => $attribute) {
        if(!array_key_exists($attribute, $list_attributes)) {
            $errors[] = 'Problems with  '. $attribute . ' attribute';
            continue;
        }
        $cache['attributes'][] = ['id'  => $list_attributes->$attribute->id, 
                                  'inc' => $attributes[2][$key] == 'yes' ? '1' : '0',
                                  'text'=> $list_attributes->$attribute->text];
    }
}

$loader = new Twig_Loader_Filesystem(TEMPLATE_DIR);
$twig   = new Twig_Environment($loader, array('debug' => true, 'cache' => TEMPLATE_COMPILED_DIR));

$gpx_file = $twig->render('waypoint.gpx', $cache);

$hd = fopen(sprintf(WAYPOINT_FILENAME, $cache['guid']), 'w');
fwrite($hd, $gpx_file);
fclose($hd);

if(!empty($errors)) {
    renderAjax(array('success' => false, 'guid' => $cache['guid'], 'message' => implode('<br />', $errors)));
}

renderAjax(array('success' => true, 'guid' => $cache['guid']));
