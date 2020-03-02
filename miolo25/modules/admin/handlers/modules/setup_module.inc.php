<?php
$MIOLO->trace('file:'.$_SERVER['SCRIPT_NAME']);
 
$theme->clearContent();

$MIOLO->checkAccess('admin', A_ACCESS, true);

$navbar->addOption( _M('Module Setup'), $module, 'main:modules:setup_module');

$ui   = $MIOLO->getUI();

$form = $ui->getForm($module,'frmSetupModule');
//$form->setAction();

$theme->appendContent($form);

$a = $context->shiftAction();

if ( ! $MIOLO->invokeHandler($module,$a) )
{
    $theme->insertContent($cmPanel);
}

?>
