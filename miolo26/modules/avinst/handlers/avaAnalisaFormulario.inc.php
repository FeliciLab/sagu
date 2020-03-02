<?php
if (($MIOLO->checkAccess(DB_TRANSACTION_ADMIN, DB_RIGHT_ADMIN)) || ($MIOLO->checkAccess(DB_TRANSACTION_ROOT, DB_RIGHT_ROOT)))
{
    $theme->clearContent();
    $ui = $MIOLO->getUI();
    $navbar->addOption('Analisa formulário', $module, $action);
    $form = $ui->getForm($module, 'frmAvaAnalisaFormulario');
    $theme->insertContent($form);
}
?>