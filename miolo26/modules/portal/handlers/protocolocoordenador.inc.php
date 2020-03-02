<?php

global $module;

if ( !isset($module) )
{
    $module = $MIOLO->getConf('options.startup');
}

$ui = $MIOLO->getUI();
$form = $ui->getForm($module, 'frmProtocoloCoordenador');
$theme->setContent($form);

$shiftAction = $context->shiftAction();

if ( $shiftAction )
{
    $MIOLO->invokeHandler($module, $shiftAction);
}

?>
