<?php

$MIOLO->checkAccess('FrmResultadoFinalProfessor', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Resultado final', $module), $module, $self);
$form = $ui->getForm($module, 'frmResultadoFinal');
$theme->insertContent($form);
?>
