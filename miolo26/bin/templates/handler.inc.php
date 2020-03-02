<?php
$theme->clearContent();
$ui = $MIOLO->getUI();
$navbar->addOption('#title', $module, $action);
$form = $ui->getForm($module, '#formName');
$theme->insertContent($form);
?>