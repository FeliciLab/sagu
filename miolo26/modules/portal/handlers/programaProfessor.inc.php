<?php

$MIOLO->checkAccess('FrmProgramaProfessor', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Programa', $module), $module, $self);
$form = $ui->getForm($module, 'frmProgramaProfessor');
$theme->insertContent($form);
?>
