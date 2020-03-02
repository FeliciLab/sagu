<?php
$perms->checkAccess( 'transaction', A_ACCESS, true );

$navbar->addOption( _M( 'Transactions', $module ), $module, $self );

$ui = $MIOLO->getUI();

$event = MIOLO::_request( 'event' ) ? MIOLO::_request( 'event' ) : MIOLO::_request( "{$MIOLO->page->getFormId()}__EVENTTARGETVALUE" );
if ( ! $event || $event == 'search:click' || $event == MToolBar::BUTTON_SEARCH . ':click' )
{
    $form = $ui->getForm( $module, 'frmTransactionSearch' );
}
else
{
    $form = $ui->getForm( $module, 'frmTransaction' );
}
$theme->insertContent( $form );
?>
