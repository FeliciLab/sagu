<?php
//mmcache_cache_page($SERVER['PHP_SELF'].'?GET='.serialize($_GET),300);
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0
//var_dump($_POST);


$module = $_REQUEST['module'];
$action = $_REQUEST['action'];
$item   = $_REQUEST['item'];

$theme_layout = 'popup';
include_once '../etc/miolo/miolo.conf';

// capture some statistics
$MIOLO->trace("HTTP_REFERER='" . $_SERVER['HTTP_REFERER'] . "'");
$MIOLO->trace("HTTP_USER_AGENT='".$_SERVER['HTTP_USER_AGENT']."'");

// page processing
$MIOLO->handler();
?>