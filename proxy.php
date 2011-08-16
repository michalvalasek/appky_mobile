<?php

$host = str_replace('index.php','',$_SERVER['HTTP_REFERER']);
if (!in_array($host,array(
	'http://appky-mobile.pagodabox.com/',
))) die('Invalid host: '.$host);

$method = $_GET['method'];

$params['key'] = $_SERVER['APPKY_API_KEY'];
if (!empty($_GET['offset'])) $params['offset'] = (int)$_GET['offset'];
if (!empty($_GET['limit'])) $params['limit'] = (int)$_GET['limit'];

// Set your return content type
header('Content-type: application/xml');

// Website url to open
$daurl = 'http://appky.sk/api/'.$method.'/?'.http_build_query($params);

// Get that website's content
$handle = fopen($daurl, "r");

// If there is something, read and return
if ($handle) {
    while (!feof($handle)) {
        $buffer = fgets($handle, 4096);
        echo $buffer;
    }
    fclose($handle);
}
