<?php
$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Documentos', $module), $module, $self);
$form = $ui->getForm($module, 'frmDocumentos');
$theme->insertContent($form);
?>
