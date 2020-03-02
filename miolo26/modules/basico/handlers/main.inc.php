<?php
$MIOLO->Trace('file:' . $_SERVER['SCRIPT_NAME']);

if ( !$module )
{
    $module = 'basico';
}

if ( ($sa = $context->shiftAction() ) != NULL )
{
    $a = $sa;
}
elseif ( $module != 'basico' )
{
    $a = 'main';
}

$ui = $MIOLO->getUI();
$login = $MIOLO->getLogin();
$adminModule = $MIOLO->mad;
$loginModule = $MIOLO->getConf('login.module');

$theme->clearContent();

$handled = $MIOLO->invokeHandler($module, $a);

if ( !$handled )
{
    $cmPanel = new MActionPanel('pnlBasico', _M('Menu', $module), '', $close, $ui->getImage($adminModule, 'system-32x32.png'));

    $cmPanel->addAction(_M('Relacionamento com o Cliente', $module), $ui->getImage($adminModule, 'system-32x32.png'), 'relcliente', 'main');
    
    $cmPanel->addAction(_M('Logout', $module), $ui->getImage($loginModule, 'logout-32x32.png'), $loginModule, 'logout');


    $theme->insertContent($cmPanel);
}

?>
