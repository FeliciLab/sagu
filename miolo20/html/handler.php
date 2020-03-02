<?php
//mmcache_cache_page($SERVER['PHP_SELF'].'?GET='.serialize($_GET),300);
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0
//var_dump($_POST);


$module = $_REQUEST['module'];
$action = $_REQUEST['action'];
$item   = $_REQUEST['item'];

include_once '../etc/miolo/miolo.conf';

// capture some statistics
$MIOLO->Trace("HTTP_REFERER='" . $_SERVER['HTTP_REFERER'] . "'");
$MIOLO->Trace("HTTP_USER_AGENT='".$_SERVER['HTTP_USER_AGENT']."'");

// page processing
$MIOLO->Handler();
	
?>
