<?php

$file = end(explode('/', $_REQUEST['file']));

header('Content-type: ' . mime_content_type("/tmp/MIOLO_$file"));
header('Content-Disposition: attachment; filename="' . $file . '"');
readfile("/tmp/MIOLO_$file");

?>