<?php
$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Declaração de participação em banca', $module), $module, $self);
$form = $ui->getForm($module, 'frmDeclaracaoParticipacaoBanca');
$theme->insertContent($form);
?>
