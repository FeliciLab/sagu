<?php
$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Preferências', $module), $module, $self);
$form = $ui->getForm($module, 'frmPreferencias');
$theme->insertContent($form);
?>
