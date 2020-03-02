<?php
echo "Inicializando GCron\n";
$path = getcwd();
$path = str_replace('html','', $path);
$logFile = 'gcron.txtlog';
$exec = "php {$path}modules/gnuteca3/misc/scripts/gcron.php";
echo $exec;

exec( $exec );
?>