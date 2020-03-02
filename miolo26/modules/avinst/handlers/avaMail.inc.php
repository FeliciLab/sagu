<?php
if (($MIOLO->checkAccess(DB_TRANSACTION_ADMIN, DB_RIGHT_ADMIN)) || ($MIOLO->checkAccess(DB_TRANSACTION_ROOT, DB_RIGHT_ROOT)))
{
    $theme->clearContent();
    $ui = $MIOLO->getUI();
    $navbar->addOption(_M('Envio de emails', $module), $module, $action);

    switch ( MIOLO::_REQUEST( 'function' ) )
    {
        case 'insert':
        case 'edit':
            $form = $ui->getForm($module, 'frmAvaMail');
            break;
        case 'search':
        default :
            $form = $ui->getForm($module, 'frmSearchAvaMail');
    }
    $theme->insertContent($form);
}
?>