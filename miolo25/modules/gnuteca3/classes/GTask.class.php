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
 * Gnuteca Task
 *
 * @author Luiz Gilberto Gregory Filho [luz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 06/08/2009
 *
 **/
abstract class GTask
{
    protected   $MIOLO, $module;
    protected   $myTask;
    protected   $busScheduleTaskLog;
    protected   $parameters;
    private     $startTime;

    const STARTING_LOG_MSG  = "Iniciando agendamento de tarefa";
    const SUCCESS_LOG_MSG   = "Sucesso no agendamento da tarefa";
    const UNSUCCESS_LOG_MSG = "Falha no agendamento da tarefa";

    /**
     *
     * @param OBJECT $MIOLO
     */
    function __construct($MIOLO, $myTask)
    {
        $this->MIOLO        = $MIOLO;
        $this->myTask       = $myTask;
        $this->module       = $this->MIOLO->getCurrentModule();

        $this->busScheduleTaskLog = $this->MIOLO->getBusiness($this->module, 'BusScheduleTaskLog');
    }

    /**
     * retorna a id da tarefa que pode ser setada apenas no construct
     *
     * @return integer
     */
    protected function getMyTaskId()
    {
    	if ( $this->myTask->ScheduleTaskId )
    	{
    		$taskId = $this->myTask->ScheduleTaskId;
    	}
    	else
    	{
    		$taskId = $this->myTask->scheduleTaskId;
    	} 
        
        return $taskId;
    }

    /**
     * Registra um conteudo no log
     *
     * @param string $msg
     */
    protected function registerLog($msg, $status)
    {
        $this->busScheduleTaskLog->scheduleTaskId = $this->getMyTaskId();
        $this->busScheduleTaskLog->log = $msg;
        $this->busScheduleTaskLog->status = $status;
        
        $this->busScheduleTaskLog->insertScheduleTaskLog();
    }

    /**
     * Registra log de inicialização
     */
    public function startLog()
    {
        $this->registerLog(self::STARTING_LOG_MSG, 'START');
    }

    /**
     * Registra log de finalização
     *
     * @param boolean $ok
     */
    public function concludeLog($ok = true, $extraMessage = '' )
    {
        if ($ok)
        {
            $this->registerLog(self::SUCCESS_LOG_MSG, 'END_SUCESS');
        }
        else
        {
            $this->registerLog(self::UNSUCCESS_LOG_MSG . '.  Erro: ' . $extraMessage, 'END_ERROR') ;
        }
    }

    /**
     * Set o tempo que iniciou a execução do script principal.
     *
     * @param unknown_type $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * Este método verifica se é preciso executar o script
     *
     * @return boolean
     */
    public function needExecute()
    {
        $time = str_replace(array("Y", "m", "d", "H", "w"),
                            array($this->startTime->Y, $this->startTime->m, $this->startTime->d, $this->startTime->H, $this->startTime->w),
                            $this->myTask->ScheduleCycleValueType);
        
        $find = false;  
        // Check para ver se esta no dia e hora correta para executar                  
        if ( $cycles = explode(';', $this->myTask->ScheduleTaskCycleValue) )
        {
        	foreach( $cycles as $cycle )
        	{
        	   	if ( $time == $cycle )
        	   	{
        	   		$find = true;
        	   		break;
        	   	}
        	}
        }
        elseif($time == $this->myTask->ScheduleTaskCycleValue)
        {
                $find = true;
        }
        
        if (!$find)
        {
        	return false;
        }

        // REGISTRA LOG DE INICIO DA TAREFA
        $this->startLog();
        return true;
    }

    /**
     * Este metodo verifica se é preciso ser executado
     *
     * @return booelan
     */
    public function execute()
    {
        return $this->needExecute();
    }

    /**
     * Seta os parametros para a classe
     *
     * @param string $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = explode('|', $parameters);
    }
    
    /**
     * Verifica se está rodando de dentro da GCron
     * 
     * @global array $_SERVER
     * @return boolean
     */
    public function isRunningFromGCron()
    {
        global $_SERVER;
        
        if ( !$_SERVER['argv'] )
        {
            throw new Exception( _M('Tarefa somente pode ser executada pela GCron pois precisa de acesso administrativo.','gnuteca3') );
        }
        
        return true;
    }
    
    /* Executar tarefa utilizando parâmetro
     * Foi implementada para utilizarmos na Univates
     * Será um método o mais genérico possível, pasivo para novas utilizações
     *
     * Criada em 06/08/2014
     * Por: Tcharles Silva
     */
    public function executeTaskId($taskId, $parameter)
    {
        //Instancia a busTask
        $busTask = $this->MIOLO->getBusiness($this->module, 'BusTask'); //instancia o business da tarefa
        $busScheduleTask = $this->MIOLO->getBusiness($this->module, 'BusScheduleTask'); //instancia o business da tarefa
        
        //pega o id da tarefa passada pelo parâmetro
        $scheduleTaskId = $taskId; 
        
        //Pega o objeto tarefa
        $scheduleTask = $busScheduleTask->getScheduleTask($scheduleTaskId); //pega a tarefa agendada
        
        //Obtem a tarefa
        $task = $busTask->getTask($scheduleTask->taskId);
        
        /* ?? LOCAL FILE ?? */
        $localFile = $this->MIOLO->getConf('home.modules') . '/' . $this->module . '/misc/scripts'; //pega o path dos scripts
        
        $busScheduleTask->setScriptPath($localFile); //seta o path dos scripts
        
        $task = (object) array_merge((array) $task, (array) $scheduleTask); //mescla os dados da tarefa e tarefa agendada
        
        $obj = $busScheduleTask->instanceObjectTask($task); //intancia objeto da tarefa
        
        if($parameter)
        {
            $obj->setParameters($parameter); //seta os parâmetro
        }
        
        $obj->setStartTime(); //registra a data/hora de início
        $obj->startLog(); //grava log de start
        
        try
        {
            $ok = $obj->execute();
            $obj->concludeLog(true, 'SUCESSO!' );
            return true;
        }
        catch ( Exception $e )
        {
            $obj->concludeLog(false, $e->getMessage() ); 
            return false;
        }
    }
}
?>