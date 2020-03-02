<?
$module = $_REQUEST['module'];
$name = $_REQUEST['name'];
include_once '../etc/miolo/miolo.conf';

// capture some statistics
$MIOLO->Trace("HTTP_REFERER='" . $_SERVER['HTTP_REFERER'] . "'");
$MIOLO->Trace("HTTP_USER_AGENT='".$_SERVER['HTTP_USER_AGENT']."'");
$ui = $MIOLO->GetUI();
$ui->DumpImageModule($module,$name);
?>
