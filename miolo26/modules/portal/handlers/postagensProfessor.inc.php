<?php

$MIOLO->checkAccess('FrmPostagensProfessor', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Postagens', $module), $module, $self);
$form = $ui->getForm($module, 'frmPostagensProfessor');
$theme->insertContent($form);
?>
