<?php

$MIOLO->checkAccess('FrmDisciplinasAluno', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Disciplinas', $module), $module, $self);
$form = $ui->getForm($module, 'frmDisciplinas');
$theme->insertContent($form);
?>
