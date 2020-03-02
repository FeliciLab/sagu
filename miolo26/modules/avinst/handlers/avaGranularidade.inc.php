<?php
if ($MIOLO->checkAccess(DB_TRANSACTION_ROOT, DB_RIGHT_ROOT))
{
    $theme->clearContent();
    $ui = $MIOLO->getUI();
    $navbar->addOption('Granularidade', $module, $action);

    switch ( MIOLO::_REQUEST( 'function' ) )
    {
        case 'insert':
        case 'edit':
            $form = $ui->getForm($module, 'frmAvaGranularidade');
            break;
        case 'search':
        default :
            $form = $ui->getForm($module, 'frmSearchAvaGranularidade');
    }
    $theme->insertContent($form);
}
?>