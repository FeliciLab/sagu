<?php

$theme->clearContent();
$ui = $MIOLO->getUI();
$navbar->addOption(_M('Serviços', $module), $module, $action);

switch ( MIOLO::_REQUEST( 'function' ) )
{
    case 'insert':
    case 'edit':
        $form = $ui->getForm($module, 'frmAvaServico');
        break;
    case 'search':
    default :
        $form = $ui->getForm($module, 'frmSearchAvaServico');
}

$theme->insertContent($form);

?>