<?php
if ($MIOLO->checkAccess(DB_TRANSACTION_ROOT, DB_RIGHT_ROOT))
{
    $theme->clearContent();
    $ui = $MIOLO->getUI();
    $navbar->addOption('Perfil', $module, $action);

    switch ( MIOLO::_REQUEST( 'function' ) )
    {
        case 'insert':
        case 'edit':
            $form = $ui->getForm($module, 'frmAvaPerfil');
            break;
        case 'search':
        default :
            $form = $ui->getForm($module, 'frmSearchAvaPerfil');
    }
    $theme->insertContent($form);
}
?>
