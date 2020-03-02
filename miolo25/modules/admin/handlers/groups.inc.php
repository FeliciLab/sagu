<?php

$perms->checkAccess('group', A_ACCESS, true);

$navbar->addOption(_M('Groups', $module), $module, $self);

$ui = $MIOLO->getUI();

switch ( MIOLO::_REQUEST('function') )
{
    case 'insert':
    case 'update':
    case 'delete':
        $form = $ui->getForm($module, 'frmGroup');
        break;

    case 'search':
    default:
        $form = $ui->getForm($module, 'frmGroupSearch');
        break;
}

$theme->insertContent($form);

?>