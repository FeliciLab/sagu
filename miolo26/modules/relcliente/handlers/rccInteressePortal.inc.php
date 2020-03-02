<?php
$MIOLO = MIOLO::getInstance();

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Interesse', $module), $module, $self);
$form = $ui->getForm('relcliente', 'frmRccInteressePortal');
$theme->insertContent($form);

?>
