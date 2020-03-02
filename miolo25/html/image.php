<?
$module = $_REQUEST['module'];
$name = $_REQUEST['name'];
include_once '../etc/miolo/miolo.conf';

// capture some statistics
$MIOLO->trace("HTTP_REFERER='" . $_SERVER['HTTP_REFERER'] . "'");
$MIOLO->trace("HTTP_USER_AGENT='".$_SERVER['HTTP_USER_AGENT']."'");
$ui = $MIOLO->getUI();
$ui->dumpImageModule($module,$name);
?>
