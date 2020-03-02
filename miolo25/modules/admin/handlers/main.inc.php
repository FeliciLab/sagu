<?php

$MIOLO->trace('file:'.$_SERVER['SCRIPT_NAME']);

if ( ! $module )
{
    $module = 'admin';
}

if (($sa = $context->shiftAction()) != NULL )
{
    $a = $sa;
}
elseif ($module != 'admin')
{
    $a = 'main';
}

//$MIOLO->checkAccess('admin',A_ACCESS,true);

$navbar->addOption( _M('Administration', $module) ,$module, 'main');

$MIOLO->uses('classes/adminForm.class.php', $module);
$MIOLO->uses('classes/adminSearchForm.class.php', $module);

$ui = $MIOLO->getUI();

$theme->clearContent();

$close = $MIOLO->getActionURL($module,'main');
$cmPanel = new MActionPanel('pnlAdmin', _M('Administration Module',$module),'', $close, $ui->getImage($module,'system-16x16.png'));
$cmPanel->addAction( _M('Modules', $module), $ui->getImage($module, 'modules-32x32.png'), $module, 'main:modules');
$cmPanel->addAction( _M('Transactions', $module)  , $ui->getImage($module, 'process-32x32.png')  , $module, 'main:transactions');
$cmPanel->addAction( _M('Users', $module)  , $ui->getImage($module, 'user-32x32.png')  , $module, 'main:users');
$cmPanel->addAction( _M('Groups', $module) , $ui->getImage($module, 'groups-32x32.png'), $module, 'main:groups');
$cmPanel->addAction( _M('Database Dump', $module) , $ui->getImage($module, 'dbdump-32x32.png'), $module, 'main:dbdump');
$cmPanel->addAction( _M('Configuration (miolo.conf)', $module) , $ui->getImage($module, 'conf-32x32.png'), $module, 'main:conf');
$cmPanel->addAction( _M('Logs', $module) , $ui->getImage($module, 'log-32x32.png'), $module, 'main:log');
$cmPanel->addAction( _M('Logout', $module) , $ui->getImage($module, 'logout-32x32.png'), $odule, 'logout');
$handled = $MIOLO->invokeHandler($module,$a);
if (! $handled)
{
    $theme->insertContent($cmPanel);
}
//include_once($MIOLO->getConf('home.modules') .'/main_menu.inc.php');
