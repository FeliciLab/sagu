<?php

$MIOLO->checkAccess('FrmHistoricoAluno', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Histórico escolar', $module), $module, $self);
$form = $ui->getForm($module, 'frmHistoricoEscolar');
$theme->insertContent($form);
?>
