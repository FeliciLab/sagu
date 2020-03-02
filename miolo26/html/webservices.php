<?php

/**
 * Handle webservices requests.
 * This file handles webservices requests made to a module. To be able to handle the request, the module needs to implement a server class in webservices directory under module's directory.
 * 
 * \b Package: \n
 * Core
 * 
 * @see  
 *
 * @since
 * Class created on 23/01/2008
 *
 * @author Vilson Cristiano GÃ¤rtner [vilson@solis.coop.br]
 *
 * \b Maintainers: \n
 * Vilson Cristiano GÃ¤rtner [vilson@solis.coop.br]
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de SoluÃ§Ãµes Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) SOLIS - Cooperativa de SoluÃ§Ãµes Livres \n
 *  
 * \b License: \n
 * Licensed under GPL (for further details read the COPYING file or http://www.gnu.org/copyleft/gpl.html )
 *
 * @version $Id$
 */

// Get the requested info
$currentModule = $_GET['module'] = $_REQUEST['module'];
$class  = $_GET['class']  = $_REQUEST['class'];
$client = $_SERVER['REMOTE_ADDR'] . '-' . $_SERVER['REMOTE_HOST'];
$time = date('d/m/Y-G:i:s');

$_SERVER['SCRIPT_NAME']     = '/index.php'; //forces index.php to prevent mcontext url problem
$_SERVER['QUERY_STRING']    = 'module=' . $currentModule . '&action=dummy&class=' . $class; //forces invalid action on URL to prevent mcontext url problem

// Temporary log file
$tmpDir  = sys_get_temp_dir();
$logFile = $tmpDir . '/webservices.log';

require_once '../classes/miolo.class.php';

ob_start();

$MIOLO = MIOLO::getInstance();
$MIOLO->handlerRequest();
$uri = $MIOLO->getConf('home.url');

echo "Webservices called from $client at $time\n";
echo "Requested Data:\n";
echo "Module: $currentModule, Class: $class, ";

$wsdlFile = "/webservices/wsdl/$class.wsdl";
$wsdlPath = $MIOLO->getModulePath( $module, $wsdlFile );

$wsdl = NULL;
if ( file_exists($wsdlPath) )
{
    $wsdl = $wsdlPath;
}

$file = "/webservices/$class.class";
$path = $MIOLO->getModulePath( $module, $file );

//Instanciate a new soapserver
$server = new SoapServer( NULL, array( 'uri' => $uri ) );

if ( file_exists($path) )
{
    include_once($path);
	
    $server->setClass($class);
	
    $funcs = $server->getFunctions();
    
    echo "Methods: ";
    
    foreach($funcs as $f)
    {
        echo "$f, ";
    }
    
    echo "\n";
    
    $out = ob_get_contents();
    error_log("$out \n", 3, $logFile);
    ob_end_clean();
	
    $server->handle();
}
else
{
    echo "\nERROR! File not found: $path \n";
    
    $out = ob_get_contents();
    error_log("$out \n", 3, $logFile);
    ob_end_clean();
    
    $server->fault('171', 'Sorry, webservices not found!');
}

?>
