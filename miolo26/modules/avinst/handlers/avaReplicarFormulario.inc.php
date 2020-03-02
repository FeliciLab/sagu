<?php
//if (($MIOLO->checkAccess(DB_TRANSACTION_ADMIN, DB_RIGHT_ADMIN)) || ($MIOLO->checkAccess(DB_TRANSACTION_ROOT, DB_RIGHT_ROOT)))
{
    $theme->clearContent();
    $ui = $MIOLO->getUI();
    $navbar->addOption(_M('Replicar formulário', $module), $module, $action);
    $form = $ui->getForm($module, 'frmAvaReplicarFormulario');
    $theme->insertContent($form);
}
?>
