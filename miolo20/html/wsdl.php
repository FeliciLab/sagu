<?php

require_once '../classes/miolo.class';

$MIOLO = MIOLO::getInstance();
$MIOLO->conf = new MConfigLoader();        
$MIOLO->conf->loadConf();
$MIOLO->HandlerRequest();

$webservice =  $_GET['webservice']  = $_REQUEST['webservice'];

ini_set('soap.wsdl_cache_enabled',0);   // Disable caching in PHP
require_once('../classes/contrib/phpWsdl/class.phpwsdl.php');

//Caso venha parâmetro especificando o webservices
if ( strlen($webservice) > 0 )
{
    $a = explode(":", $webservice);
    $module = $a[0];
    $class = $a[1];
    
    $soap=PhpWsdl::CreateInstance(
            null,                                                           // PhpWsdl will determine a good namespace
            null,                   // Change this to your SOAP endpoint URI (or keep it NULL and PhpWsdl will determine it)
            './cache',                                                      // Change this to a folder with write access
            Array(                                                          // All files with WSDL definitions in comments
                    "../modules/$module/webservices/$class.class",
            ),
            null,                                                           // The name of the class that serves the webservice will be determined by PhpWsdl
            null,                                                           // This demo contains all method definitions in comments
            null,                                                           // This demo contains all complex types in comments
            false,                                                          // Don't send WSDL right now
            false);
    
    $soap->EndPoint = $soap->EndPoint ."?webservice=$module:$class";
}
else
{
    //Se não vier parâmetro utiliza o configurado no conf
    $soap=PhpWsdl::CreateInstance(
            null,                                                           // PhpWsdl will determine a good namespace
            null,                   // Change this to your SOAP endpoint URI (or keep it NULL and PhpWsdl will determine it)
            './cache',                                                      // Change this to a folder with write access
            Array(                                                          // All files with WSDL definitions in comments
                    "../" . $MIOLO->getConf('home.defaultwsdl'),
            ),
            null,                                                           // The name of the class that serves the webservice will be determined by PhpWsdl
            null,                                                           // This demo contains all method definitions in comments
            null,                                                           // This demo contains all complex types in comments
            false,                                                          // Don't send WSDL right now
            false);
}

PhpWsdl::$CacheTime=0;

if($soap->IsWsdlRequested())                    // WSDL requested by the client?
        $soap->Optimize=false;

$soap->RunServer();

?>
