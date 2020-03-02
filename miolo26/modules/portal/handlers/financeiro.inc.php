<?php

$MIOLO->checkAccess('FrmFinanceiroAluno', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Financeiro', $module), $module, $self);
$form = $ui->getForm($module, 'frmFinanceiro');
$theme->insertContent($form);
?>
