#!/usr/bin/php
<?php

if ( $argc < 2 )
{
    $message = "Usage: {$argv[0]} <update XML file>\n";
    die($message);
}

$updateDataFile = realpath($argv[1]);

$pathInfo = pathinfo($argv[0]);

$BIN_PATH = realpath($pathInfo['dirname']);
$MIOLO_PATH = realpath($BIN_PATH . '/..');

// Backup miolo.conf
$confBackup = "$BIN_PATH/updater/backup/miolo.conf";
$cmd = "cp $MIOLO_PATH/etc/miolo.conf $confBackup";
exec($cmd, $output, $return);

if ( $return !== 0 )
{
    die("Não foi possível realizar o backup do miolo.conf.\n");
}

// Set server as in maintenance (down)
$conf = simplexml_load_file($confBackup);
if ( file_exists("$MIOLO_PATH/.down") )
{
    die("Uma outra atualização está em andamento.\n");
}
else
{
    file_put_contents("$MIOLO_PATH/.down", $conf->theme->main);
    $GLOBALS['MIOLO_UPDATER'] = 'updating';
}

// Load miolo.conf
$tempConf = simplexml_load_file($MIOLO_PATH . '/etc/miolo.conf');

// Set modern theme to avoid unnecessary database calls
$tempConf->theme->main = 'modern';
$tempConf->theme->lookup = 'modern';

$handler = fopen("$MIOLO_PATH/etc/miolo.conf", 'w');
fwrite($handler, $tempConf->asXML());
fclose($handler);


$path = realpath($MIOLO_PATH . '/classes/utils');

chdir($path);
require_once 'mioloupdater.class.php';
chdir($BIN_PATH);

$mu = new MIOLOUpdater($updateDataFile);
$mu->update();

?>
