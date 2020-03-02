<?php

$MIOLO->trace('file:'.$_SERVER['SCRIPT_NAME']);

$MIOLO->checkAccess('admin', A_ACCESS, true);

$navbar->addOption( _M('Modules', $module) ,$module, 'main:modules');

$ui = $MIOLO->getUI();

$theme->clearContent();

$close = $MIOLO->getActionURL($module,'main');

$cmPanel = new ActionPanel('pnlAdmin', _M('Install New Module',$module),'', $close, $ui->getImage($module,'modules-16x16.png'));

$cmPanel->addAction( _M('From File',       $module), $ui->getImage($module, 'module_add-32x32.png'), $module, 'main:modules:add_modules');
$cmPanel->addAction( _M('From Repository', $module), $ui->getImage($module, 'module_add-32x32.png'), $module, 'main:modules:add_online_modules');

$handled = $MIOLO->invokeHandler($module, $context->shiftAction() );

if ( ! $handled )
{
    $theme->insertContent($cmPanel);
}

