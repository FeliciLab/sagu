<?php
if (($MIOLO->checkAccess(DB_TRANSACTION_ADMIN, DB_RIGHT_ADMIN)) || ($MIOLO->checkAccess(DB_TRANSACTION_ROOT, DB_RIGHT_ROOT)))
{
    $theme->clearContent();
    $ui = $MIOLO->getUI();
    $navbar->addOption('Avaliação', $module, $action);

    switch ( MIOLO::_REQUEST( 'function' ) )
    {
        case 'insert':
        case 'edit':
            $form = $ui->getForm($module, 'frmAvaAvaliacao');
            break;
        case 'search':
        default :
            $form = $ui->getForm($module, 'frmSearchAvaAvaliacao');
    }
    $theme->insertContent($form);
}
?>