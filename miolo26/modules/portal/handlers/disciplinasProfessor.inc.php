<?php

$MIOLO->checkAccess('FrmDisciplinasProfessor', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Disciplinas', $module), $module, $self);
$form = $ui->getForm($module, 'frmDisciplinasProfessor');
$theme->insertContent($form);
?>
