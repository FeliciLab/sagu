<?php
$MIOLO->history->pop();
$lookup = new MLookup($module);
$lookup->execute();
$lookup->setContent();
?>