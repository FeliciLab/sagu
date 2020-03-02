<?php
$moduleRequisicao        = MIOLO::_REQUEST('module');   
$classModule   = $moduleRequisicao . '.class.php';
$dirClass      = __DIR__ . '/../../' . $moduleRequisicao . '/classes/' . $classModule;
$accessChecked = false;

if ( file_exists($dirClass) )
{
    require_once $dirClass;
    $objModule = new $moduleRequisicao();
            
    if ( method_exists($objModule, 'isAllowedAction') )
    {
        $accessChecked = $objModule->isAllowedAction();
    }
}

$MIOLO->logMessage('[LOGIN] file:'.$_SERVER['SCRIPT_NAME']);
$MIOLO->logMessage('[LOGIN] Using login prompt');
$ui = $MIOLO->getUI();
$theme->clearContent();
$module = 'base';
$navbar->addOption('Login', $module, 'login');
$login = $auth->getLogin();
$return_to = MIOLO::_REQUEST('return_to');



// If the user is logged in, send to the start up module
if ($login && ($return_to == '') || $accessChecked)
{
   //$form = $ui->getForm($module,'frmAccess',$login);
   $MIOLO->invokeHandler($MIOLO->getConf('options.common'), 'main');
}
else
{
    $formularioLogin = "frmLogin";
    
    // Caso o módulo requisitado seja o da avaliação, considera o formulário personalizado
    if( $moduleRequisicao === "avinst" )
    {
        if( strlen(SAGU::getParameter("avinst", "FORMULARIO_DE_LOGIN_PERSONALIZADO")) > 0 )
        {
            $formularioLogin = SAGU::getParameter("avinst", "FORMULARIO_DE_LOGIN_PERSONALIZADO");
        }
    }
    
    $form = $ui->getForm($module, $formularioLogin);
}
if ($theme->getContent()  == '')
{
   $theme->insertContent($form);
}
?>
