<?php

$theme->clearContent();
$ui = $MIOLO->getUI();
$navbar->addOption(_M('Componente', $module), $module, $action);

switch ( MIOLO::_REQUEST( 'function' ) )
{
    case 'insert':
    case 'edit':
        $form = $ui->getForm($module, 'frmAvaWidget');
        break;
    case 'search':
    default :
        $form = $ui->getForm($module, 'frmSearchAvaWidget');
}

$theme->insertContent($form);

?>