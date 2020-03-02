<?php
$MIOLO = MIOLO::getInstance();

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Ouvidoria', $module), $module, $self);
$form = $ui->getForm('relcliente', 'frmRccMensagemOuvidoriaPortal');
$theme->insertContent($form);

?>
