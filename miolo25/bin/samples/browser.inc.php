<?php
$theme->clearContent();
$ui = $MIOLO->getUI();
$navbar->addOption(_M('Browser', $module), $module, $action);

switch ( MIOLO::_REQUEST( 'function' ) )
{
    case 'insert':
    case 'edit':
        $form = $ui->getForm($module, 'frmBrowser');
        break;

    case 'search':
    default:
        $form = $ui->getForm($module, 'frmSearchBrowser');
}

$theme->insertContent($form);
?>
