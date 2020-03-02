<?php
require 'sconsole.php';

/** 
 * Exemplos de uso:
 * 
 * php basic/classes/sconsolecsvteste.php sCSVPessoaAluno /tmp/pessoas.csv 
 * php basic/classes/sconsolecsvteste.php sCSVDocumento /tmp/documentos.csv  
 */

$import = new $argv[1]();
$import->setShowProgress(true);
$import->loadFile($argv[2], ',');
//$import->setCheckTransaction(false);
//$import->setExecuteRollback(true);
//$import->setLimitRecords(120);

if ( $import->check() )
{
    var_dump('Arquivo CSV esta OK!');
    $import->import();
}
else
{
    foreach ( $import->getErrorLog() as $linha => $erros )
    {
        echo "Linha: {$linha}: {$erros}\n\n";
    }
}

?>
