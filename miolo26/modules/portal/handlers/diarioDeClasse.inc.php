<?php
$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Diário de classe', $module), $module, $self);
$form = $ui->getForm($module, 'frmDocumentDiarioDeClasse');
$theme->insertContent($form);
?>
