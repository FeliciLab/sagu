<?php

$MIOLO->checkAccess('DocHorasProfessores', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Planilha de horas dos professores', $module), $module, $self);
$form = $ui->getForm($module, 'frmHorasProfessores');
$theme->insertContent($form);

?>
