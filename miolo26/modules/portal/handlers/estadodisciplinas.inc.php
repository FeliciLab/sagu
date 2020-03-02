<?php

$MIOLO->checkAccess('DocEstadoDisciplinas', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Estado das disciplinas dos professores', $module), $module, $self);
$form = $ui->getForm($module, 'frmEstadoDisciplinas');
$theme->insertContent($form);

?>
