<?php

//procura path do miolo
if ( !$_SERVER['HTTP_HOST'] ) //chamado por phpunit
{
    $pwd = $_SERVER['PWD']; //pwd atual
    $script = $_SERVER['argv'][0]; //chamada do script

    $mioloPath = '';
    if ( ($pos = strpos($script, 'modules')) !== false )
    {
        $mioloPath = substr($script, 0, $pos);

        if ( substr($mioloPath, 0, 1) != '/' )
        {
            $mioloPath = $pwd . $mioloPath;
        }
    }
    else if ( ($pos = strpos($pwd, 'modules')) !== false )
    {
        $mioloPath = substr($pwd, 0, $pos);
    }
}

$mioloClassesPath   = "$mioloPath/classes";
$module             = 'gnuteca3';
$mioloConsoleFile   = "$mioloPath/modules/$module/classes/mioloconsole.class.php";

// CHECK MIOLO CONSOLE EXISTS
if(!file_exists($mioloPath))
{
    die("\n\n\nMiolo Path not exists!!! \nFile: $mioloPath\n\n\n");
}
if(!file_exists($mioloConsoleFile))
{
    die("\n\n\nMiolo Console File not exists!!! \nFile: $mioloConsoleFile\n\n\n");
}

require_once($mioloConsoleFile);
$MIOLOConsole = new MIOLOConsole();
$GLOBALS['MIOLO'] = $MIOLO = $MIOLOConsole->getMIOLOInstance($mioloPath, $module);
$MIOLOConsole   ->loadMIOLO();
$MIOLO->uses('handlers/gnutecaClasses.inc.php', $module);
$MIOLO->uses('handlers/define.inc.php', $module);

?>
