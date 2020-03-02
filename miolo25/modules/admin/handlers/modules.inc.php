<?php

$MIOLO->trace('file:'.$_SERVER['SCRIPT_NAME']);

$MIOLO->checkAccess('admin',A_ACCESS,true);

$navbar->addOption( _M('Modules', $module) ,$module, 'main:modules');

$ui = $MIOLO->getUI();

$theme->clearContent();

$close = $MIOLO->getActionURL($module,'main');

$cmPanel = new ActionPanel('pnlAdmin', _M('Modules Maintainance',$module),'', $close, $ui->getImage($module,'modules-16x16.png') );

$cmPanel->addAction( _M('Edit Modules', $module)      , $ui->getImage($module, 'module_edit-32x32.png'), $module, 'main:modules:edit_modules');
$cmPanel->addAction( _M('Install New Module', $module), $ui->getImage($module, 'module_add-32x32.png'), $module, 'main:modules:modules_new');
$cmPanel->addAction( _M('Remove Modules', $module)    , $ui->getImage($module, 'module_del-32x32.png'), $module, 'main:modules:rem_modules');

$handled = $MIOLO->invokeHandler($module,'modules/'.$context->shiftAction() );

if ( ! $handled )
{
    $theme->insertContent($cmPanel);
}
