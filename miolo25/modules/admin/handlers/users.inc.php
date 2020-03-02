<?php
$perms->checkAccess( 'user', A_ACCESS, true );

$navbar->addOption( _M( 'Users', $module ), $module, $self );

$ui = $MIOLO->getUI();

switch ( MIOLO::_REQUEST('function') )
{
    case 'insert':
    case 'update':
    case 'delete':
        $form = $ui->getForm( $module, 'frmUser' );
        break;

    case 'search':
    default:
        $form = $ui->getForm( $module, 'frmUserSearch' );
        break;
}

$theme->insertContent( $form );

?>