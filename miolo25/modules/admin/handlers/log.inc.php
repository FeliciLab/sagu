<?php
$perms->checkAccess('group', A_ACCESS, true);
$home = 'main:admin';
$navbar->addOption('Log',$module,$self);
$ui = $MIOLO->getUI();
//if( MIOLO::_REQUEST('pointer') )
//{
//    $form = $ui->getForm($module,'frmLogInfo');
//}
//else
//{
    $form = $ui->getForm($module,'frmLog');
//}
if( ! MIOLO::_REQUEST('pointer') )
{
    $theme->appendContent($form);
}
?>
