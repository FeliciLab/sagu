<?php

$args       = unserialize( $argv[1] );
$mioloPath  = $args->mioloPath;
$task       = $args->task;

//caso não tenha o caminho definido
if ( !$mioloPath )
{
    //tenta pegar o caminho do miolo usando PWD do sistema
    ob_start();
    system('pwd');
    $filePath = ob_get_contents();
    ob_clean();

    //tira linha nova e pega só o diretório
    $filePath = dirname( str_replace("\n", '', $filePath));
    $filePath = explode('/',$filePath );
    //tira as últimas duas pastas
    unset($filePath[ count($filePath) - 1 ] );
    unset($filePath[ count($filePath) - 1 ] );
    //monta a string novamente
    $filePath = implode('/',$filePath );

    if ( file_exists( $filePath ) && file_exists( "$mioloPath/modules/gnuteca3/classes/mioloconsole.class.php"))
    {
       $mioloPath = $filePath;
    }
}

//caso não tenha encontrado (possivalmente no windows, procura em locais padrão)
if ( !$mioloPath )
{
    //configurações do miolo
    $dirs = array(
        getenv('HOME') . '/solis/svn/miolo25-gnuteca3trunk',
        '/var/www/miolo25-gnuteca3/',
        '/var/www/miolo25-gnuteca/',
        getenv('HOME') .'/solis/svn/miolo25-gnuteca3/',
        getenv('HOME') . '/solis/svn/miolo25-gnuteca/',
        getenv('HOME') . '/svn/miolo25gnuteca3/'
        );

    foreach ( $dirs as $dir )
    {
        echo "dir = $dir\n";
        if ( is_dir($dir) )
        {
            $mioloPath = $dir;
            break;
        }
    }
}

$mioloConsoleFile   = $mioloPath . '/modules/gnuteca3/classes/mioloconsole.class.php';
$module             = 'gnuteca3';
$localFile          = str_replace("\\", "/", dirname(__FILE__));

// checa se miolo existe
if(!file_exists($mioloPath))
{
    die("\nMiolo Path not exists!!! \nFile: $mioloPath\n");
}
if(!file_exists($mioloConsoleFile))
{
    die("\nMiolo Console File not exists!!! \nFile: $mioloConsoleFile\n");
}

require_once($mioloConsoleFile);
$MIOLOConsole = new MIOLOConsole();
$GLOBALS['MIOLO'] = $MIOLO = $MIOLOConsole->getMIOLOInstance($mioloPath, $module);
$MIOLOConsole   ->loadMIOLO();
$MIOLO->uses('handlers/gnutecaClasses.inc.php', 'gnuteca3');
$MIOLO->uses('handlers/define.inc.php', 'gnuteca3');

include_once("GBackgroundTask.class.php");
include_once("$task.class.php");

echo GBackgroundTask::executeTask($task, $args);
?>