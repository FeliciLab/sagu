<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 *
 * Este arquivo é parte do programa Gnuteca.
 *
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 *
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 16/03/2011
 *
 **/
interface GBackgroundTaskTemplate
{
    public function execute();
}

/*
 * Exige que deve ser implementado a função execute
 *
 */
class GBackgroundTask
{
    public $args;
    public $MIOLO;
    public $business;
    public $label;
    public $message;
    const STATUS_EXECUTION   = 1;
    const STATUS_SUCESS      = 2;
    const STATUS_ERROR       = 3;
    const STATUS_REEXECUTION = 4;
    const STATUS_RESUCESS    = 5;
    const STATUS_RERROR      = 6;

    public function __construct($args)
    {
        $this->args     = $args;
        $this->MIOLO    = MIOLO::getInstance();
        $this->business = $this->MIOLO->getBusiness('gnuteca3', 'BusBackgroundTaskLog');
        $this->business = new BusinessGnuteca3BusBackgroundTaskLog(); //para funcionar o autocomplete
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label ? $this->label : $this->args->task;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Executa um tarefa em segundo plano
     */
    public static function executeTask($task, $args)
    {
        $MIOLO      = MIOLO::getInstance();
        $business   = $MIOLO->getBusiness('gnuteca3', 'BusBackgroundTaskLog');
        $business   = new BusinessGnuteca3BusBackgroundTaskLog(); //para funcionar o autocomplete
        $taskObj    = new $task($args);

        $data = new stdClass();
        $data->beginDate        = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        $data->task             = $task;
        $data->label            = $taskObj->getLabel();
        $data->status           = GBackgroundTask::STATUS_EXECUTION;
        $data->operator         = GOperator::getOperatorId();
        $data->args             = serialize($args);
        $data->libraryUnitId    = $args->libraryUnitId;

        $business->setData($data);

        $business->insertBackgroundTaskLog();

        $execute = $taskObj->execute();

        $data->status = $execute ? GBackgroundTask::STATUS_SUCESS : GBackgroundTask::STATUS_ERROR;
        $data->endDate = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        $data->message = $taskObj->getMessage();

        $business->setData($data);

        $business->updateBackgroundTaskLog();

        $date = Gdate::now();
        $time = $date->getDate(GDate::MASK_TIMESTAMP_USER);

        return "$time - $task - '{$data->status} - {$taskObj->getMessage()}' \n";
    }


    /**
     * Re-executauma tarefa em segundo plano, passando o código do log
     * 
     * @param int $backgroundTaskLogId códigodo log de tarefa em segundo plano
     * @return boolean
     */
    public static function reExecuteTask( $backgroundTaskLogId )
    {
        if ( !$backgroundTaskLogId )
        {
            throw new Exception( _M('É necessário informar o código de registro da terafa!', 'gnuteca3') );
        }

        $MIOLO = MIOLO::getInstance();
        $business = $MIOLO->getBusiness('gnuteca3','BusBackgroundTaskLog');
        $business = new BusinessGnuteca3BusBackgroundTaskLog(); //para funcionar o autocomplete
        $data = $business->getBackgroundTaskLog( $backgroundTaskLogId );
        $data->status = GBackgroundTask::STATUS_REEXECUTION;
        $data->operator = GOperator::getOperatorId();
        $data->libraryUnitId = GOperator::getLibraryUnitLogged();

        //separa e inclui a tarefa
        $task = $data->task;
        $MIOLO->uses("classes/backgroundTasks/$task.class.php",'gnuteca3');

        $taskObj = new $task( unserialize( $data->args ) );
        //atualiza dados
        $business->setData($data);
        $business->updateBackgroundTaskLog();

        $execute =  $taskObj->execute(); //executa

        $data->status = $execute ? GBackgroundTask::STATUS_RESUCESS : GBackgroundTask::STATUS_RERROR;
        $data->endDate = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        $data->message = $taskObj->getMessage();

        $business->setData($data);
        $business->updateBackgroundTaskLog();

        //caso não tenha executado com sucesso, retorna error
        if ($data->status == GBackgroundTask::STATUS_RERROR )
        {
            throw new Exception( $data->message );
        }

        return $data->status;
    }

     /**
     * Executa uma tarefa em segundo plano. As tarefas em segundo plano devem estar na pasta backbagroundTasks.
     *
     * Será gerado um arquivo de log com o mesmo nome da tarefa.
     *
     * @param <string> $task passe somente o nome do script, sem a extensão .php
     * @param <stdClass> argumentos a serem passados para o outro lado
     * @param <boolean> se true, não roda se uma tarefa deste tipo já estiver rodando
     * @param <boolean> se true, somente roda em background, não executando caso o usuário tenha que esperar
     * @return <boolean> verdadeira se o script existir
     *
     */
    public static function executeBackgroundTask($task, $args, $runningOnce = false , $onlyInBackground = false, $informLibraryUnit = true )
    {
        $MIOLO      = MIOLO::getInstance();
        $logFile    =  "/tmp/backgroundTask.log";
        $fileName   =  "modules/gnuteca3/classes/backgroundTasks/$task.class.php";
        $script     =  "modules/gnuteca3/classes/backgroundTasks/getMioloConsole.php";
        $fullPath   = $MIOLO->getConf('home.miolo').'/';

        //se o script não existir retorna falso
        if ( !file_exists( $fullPath .$fileName ) )
        {
            return false;
        }

        $args->mioloPath = $MIOLO->getAbsolutePath();
        $args->task = $task;

        if ( $informLibraryUnit )
        {
            $args->libraryUnitId  = GOperator::getLibraryUnitLogged();
        }
        
        $args = serialize($args);

        //executa em segundo plano
        if ( GBackgroundTask::isPossible() )
        {
            //é possível que este código não funcione em servidores Windows
            exec( "php ../$script '$args' >> $logFile &"); //>> para adicionar ao log
            chmod( $logFile, 0777 );
        }
        //caso não seja possível executar em segundo plano faz a chamada da tarefa manualmente, forçando o usuário a esperar
        else if ( !$onlyInBackground ) 
        {
            $MIOLO->uses("classes/backgroundTasks/$task.class.php",'gnuteca3');

            return GBackgroundTask::executeTask($task, unserialize( $args ) ) ;
        }
        else
        {
            return false;
        }

        return true;
    }


    /**
     * Verifica se existe a possibilidade de executar tarefas em segundo plano.
     *
     * 1. Verifica se a função exec existe.
     * 2. Verifica se o php cli existe
     * 3. Verifica se não esta em Modo Seguro
     * 4. Verifica se a preferência de execução em segundo plano esta liberada.
     *
     * @return <boolean>
     *
     */
    public static function isPossible()
    {
        if (function_exists('exec'))
        {
            exec('php --version', $message); //executa o comando para obter a versão do php_cli
            if ( stripos( $message[1], 'The PHP Group') > 0  && !ini_get('safe_mode') && MUtil::getBooleanValue( EXECUTE_BACKGROUND_TASK ))
            {
                return true;
            }
        }

        return false;
    }
}
?>