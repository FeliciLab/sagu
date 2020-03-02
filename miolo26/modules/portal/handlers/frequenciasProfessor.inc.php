<?php

$MIOLO->checkAccess('FrmFrequenciasProfessor', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Frequencias', $module), $module, $self);
$form = $ui->getForm($module, 'frmFrequenciasProfessor');
$theme->insertContent($form);
?>
