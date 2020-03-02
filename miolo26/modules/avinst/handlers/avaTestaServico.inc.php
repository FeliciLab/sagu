<?php

$theme->clearContent();
$ui = $MIOLO->getUI();
$navbar->addOption('Testar serviço', $module, $action);
$form = $ui->getForm($module, 'frmAvaTestaServico');
$theme->insertContent($form);

?>