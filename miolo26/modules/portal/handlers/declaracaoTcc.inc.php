<?php
$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Declaração de orientação de TCC', $module), $module, $self);
$form = $ui->getForm($module, 'frmDeclaracaoTcc');
$theme->insertContent($form);
?>
