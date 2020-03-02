<?php

//$MIOLO->checkAccess('FrmEstatisticasProfessor', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Estatisticas', $module), $module, $self);
$form = $ui->getForm($module, 'frmEstatisticaCoordenador');
$theme->insertContent($form);
?>
