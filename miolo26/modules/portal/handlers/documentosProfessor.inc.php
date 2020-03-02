<?php

$MIOLO->checkAccess('FrmDocumentosProfessor', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Documentos', $module), $module, $self);
$form = $ui->getForm($module, 'frmDocumentosProfessor');
$theme->insertContent($form);
?>
