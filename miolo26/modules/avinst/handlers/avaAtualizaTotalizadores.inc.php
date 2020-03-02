<?php
if (($MIOLO->checkAccess(DB_TRANSACTION_ADMIN, DB_RIGHT_ADMIN)) || ($MIOLO->checkAccess(DB_TRANSACTION_ROOT, DB_RIGHT_ROOT)))
{
    $theme->clearContent();
    $ui = $MIOLO->getUI();
    $navbar->addOption('Atualizar totalizadores', $module, $action);
    $form = $ui->getForm($module, 'frmAtualizaTotalizadores');
    $theme->insertContent($form);
}
?>