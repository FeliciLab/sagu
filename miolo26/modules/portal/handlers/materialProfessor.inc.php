<?php
$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Material', $module), $module, $self);
$form = $ui->getForm($module, 'frmMaterialProfessor');
$theme->insertContent($form);
?>
