<?php

$MIOLO->checkAccess('FrmNotasProfessor', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Grupo de disciplinas', $module), $module, $self);
$form = $ui->getForm($module, 'frmGrupoDisciplina');
$theme->insertContent($form);
?>
