<?php

$file = end(explode('/', $_REQUEST['file']));

header('Content-type: application/php');
header('Content-Disposition: attachment; filename="' . $file . '"');
readfile("/tmp/MIOLO_$file");

?>