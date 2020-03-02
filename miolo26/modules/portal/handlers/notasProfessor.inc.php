<?php

$MIOLO->checkAccess('FrmNotasProfessor', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Notas', $module), $module, $self);
$form = $ui->getForm($module, 'frmNotasProfessor');
$theme->insertContent($form);
?>
