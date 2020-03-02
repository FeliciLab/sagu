<?php
/**
 * Cron from Gnuteca
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 11/11/2010
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solucoes Livres \n
 * The Gnuteca3 Development Team
 *
 * \b CopyLeft: \n
 * CopyLeft (L) 2010 SOLIS - Cooperativa de Solucoes Livres \n
 *
 * \b License: \n
 * Licensed under GPL (for further details read the COPYING file or http://www.gnu.org/copyleft/gpl.html
 *
 * \b History: \n
 * See history in SVN repository: http://gnuteca.solis.coop.br
 *
 */
include ('iniciaMiolo.php');

//obtém os parâmetros
for ($x=1; $x < $argc; $x++)
{
    if (substr($argv[$x], 0, 1) == '-') //verifica se é uma opção
    {
        switch (substr($argv[$x], 1)) //verifica qual é a opção
        {
            case 'h':   $pAjuda = true;
                        break;
            case 'c':  $pSemWhile = true;
                break;
        }
    }
}

//exibe a ajuda
if ( $pAjuda )
{
    $msg[] = "Usar: $argv[0] [Opcoes]";
    $msg[] = "";
    $msg[] = "Opcoes:";
    $msg[] = " -h       Exibe esta ajuda";
    $msg[] = " -c       Executar a GCron somente uma vez (usado pelo Cron)";
        
    echo implode("\n", $msg) . "\n\n";
    
    return 0;
}

$confPath = $mioloPath.'/etc/miolo.conf';

//instancia o miolo.conf para obter caminho de arquivamento
if ( file_exists( $confPath ) )
{
    $confContent = file_get_contents($confPath);
    
    if ( $confContent )
    {
        $conf = new SimpleXMLElement( $confContent );
        $tmpPath = $conf->gnuteca->files.'';
    }

    //procura no module
    if ( !$tmpPath )
    {
        $confPath = $conf->home->modules . '/gnuteca3/etc/module.conf';
        $confContent = file_get_contents($confPath);
    
        if ( $confContent )
        {
            $conf = new SimpleXMLElement( $confContent );
            $tmpPath = $conf->gnuteca->files.'';
        }
    }
}

if ( !$tmpPath )
{
    $tmpPath = $mioloPath . "/modules/{$module}/html/files";
}

$gCronPath = $tmpPath . '/gcron.log';

define(SLEEP_TIME, 300); //define ciclo de execução em segundos
$executeDate = file_get_contents($gCronPath); //obtém a data/hora em que a gcron foi rodada pela última vez

if ( strlen($executeDate) > 0 )
{
    $horaAnterior = substr($executeDate, 11,2); //obtém a hora de execução da GCron
    $minutoAnterior = ( $horaAnterior * 60 ) + substr($executeDate, 14,2);
}
else
{
    $horaAnterior = '';
    $minutoAnterior = '';
}

//caso a gcron esteja rodando para processo
if ( file_exists( $tmpPath . 'gcronrun' ) )
{
    $minutoAtual = date('H') * 60 + ( date('i') );
    $diff = $minutoAnterior - $minutoAtual;
    
    //caso já tenha passado 10 minutos permite continuar
    if ( $diff > 10 )
    {
        unlink( $tmpPath . 'gcronrun' );
    }
    else
    {
        return true;
    }
}

file_put_contents( $tmpPath . 'gcronrun', 'gcronrun'); //registra que está rodando

while(true)
{
    file_put_contents($gCronPath, date('d/m/Y H:i:s')); //grava o log de execução
    
    $hour = date('H');
    
    if ( $horaAnterior != $hour )
    {
        exec("php $mioloPath/modules/{$module}/misc/scripts/runScheduleTasks.php true"); //thread que executa as tarefas
        $horaAnterior = $hour;
    }
    
    //executa a GCron somente uma vez
    if ( $pSemWhile )
    {
        break;
    }
    
    sleep(SLEEP_TIME); //aguarda o tempo estimado em SLEEP_TIME segundos
}

unlink( $tmpPath . 'gcronrun' );
?>
