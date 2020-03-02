<?php
$navbar->addOption(_M('Main'), $module, 'main');

$ui = $MIOLO->getUI();
$theme->clearContent();

$panel = new MActionPanel('pnlmage', _M('Main'), '', $close);

// add actions to the panel
#actions

// append the content into the theme content
$theme->appendContent($panel);

$shiftAction = $context->shiftAction();

if ( $shiftAction )
{
    $handled = $MIOLO->invokeHandler($module, $shiftAction);
}

?>