<?php
$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Contato dos alunos', $module), $module, $self);
$form = $ui->getForm($module, 'frmContatoAlunos');
$theme->insertContent($form);
?>
