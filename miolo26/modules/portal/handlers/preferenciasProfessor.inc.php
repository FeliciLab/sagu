<?php

$MIOLO->checkAccess('FrmPreferenciasProfessor', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Preferências', $module), $module, $self);
$form = $ui->getForm($module, 'frmPreferenciasProfessor');
$theme->insertContent($form);
?>
