<?php

$theme->clearContent();
$ui = $MIOLO->getUI();
$action = 'main:avaRespondeFormulario';
$navbar->addOption('Responder formulário', $module, $action);
$form = $ui->getForm($module, 'frmAvaRespondeFormulario');
$theme->insertContent($form);

?>