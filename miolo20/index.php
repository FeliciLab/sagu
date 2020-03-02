<?php

/**
 * MIOLO Installer Redirect
 * Author: Vilson Cristiano Gartner <vison@miolo.org.br> / <vilson@solis.coop.br>
 * Date: 13 Nov. 2006
 */

$location    = $_SERVER['REQUEST_URI'];
$directory   = substr($location, 0,  strrpos( $location, '/') + 1 );
$newLocation = $directory . 'etc/webinstaller/index.php';

header("Location: $newLocation");
exit;
?>
