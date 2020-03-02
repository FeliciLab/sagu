<?php
require 'sconsole.php';

$MIOLO->uses( 'classes/bBaseDeDados.class.php','base');
$MIOLO->uses( 'classes/bSyncExecute.class.php','base');

// Modulo onde estao os arquivos que devem ser sincronizados: XML's, functions.sql, views.sql ...
$syncModule = 'basic';

// Definicao para base de dados funcionar adequadamente utilizando o modulo correto
if ( !defined('DB_NAME') )
{
    define('DB_NAME', $syncModule);
}

bBaseDeDados::iniciarTransacao();
try
{
    consoleOutput(_M("Executando sync na base de dados.", $syncModule));
    //Realiza sincronização
    bSyncExecute::executeSync($syncModule);

    bBaseDeDados::finalizarTransacao();
    
    consoleOutput(_M('SyncDb executado com sucesso.'));
}
catch (Exception $e)
{
    bBaseDeDados::reverterTransacao();
    
    consoleOutput(_M("Houveram complicacoes durante a execucao da sincronizacao, verifique abaixo:"));

    $msgErro = explode('<br />', $e->getMessage());

    consoleOutput(_M("Erro na BASE: ") . $msgErro[2]);
    consoleOutput($msgErro[4]);
}
?>
