<?php
$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Mensagens', $module), $module, $self);
$form = $ui->getForm($module, 'frmMensagensProfessor');
$theme->insertContent($form);
?>
