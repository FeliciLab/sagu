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
 * Schedule task search form
 *
 * @author Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 01/07/2010
 *
 **/
class FrmScheduleTaskSearch extends GForm
{
    private $busTask;
    
    public function __construct()
    {
    	$this->setAllFunctions('ScheduleTask', array('scheduleTaskId', 'taskId', 'scheduleCycleId', 'parametersS'), array('scheduleTaskId', 'taskId', 'scheduleCycleId'), array('scheduleTaskId', 'taskId', 'scheduleCycleId', 'parameters'));
        parent::__construct();
    }

    
    public function mainFields()
    {
    	$fields[] = new MTextField('scheduleTaskIdS', $this->scheduleTaskIdS->value, _M('Agendar tarefa',$this->module), FIELD_ID_SIZE);
        $this->busTask = $this->MIOLO->getBusiness($this->module, 'BusTask');
        $fields[] = new GSelection('taskIdS', '', _M('Tarefa',$this->module), $this->busTask->listTask());
        $fields[] = new GSelection('scheduleCycleIdS', '', _M('Ciclo do agendamento',$this->module), $this->business->listScheduleCycles());
        $fields[] = new MTextField('descriptionS', '', _M('Descrição', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('cycleValueS', '', _M('Valor', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('enableS', null, _M('Está ativo',$this->module), GUtil::listYesNo(0));
        $fields[] = new MTextField('parametersS', '', _M('Parâmetro', $this->module), FIELD_DESCRIPTION_SIZE);
        
        $this->setFields($fields);
    }
    
    
    /**
     * This method execute an task
     */
    public function executeScript()
    {
        if(!MIOLO::_REQUEST('executeConfirm')) //pede confirmação da execução
        {
            $opts['executeConfirm'] = 'ok';
            $opts['event'] = 'executeScript';
            $opts['function'] = 'execute';
            $opts['scheduleTaskId'] = MIOLO::_REQUEST('scheduleTaskId');
            $gotoYes = $this->MIOLO->getActionURL( $this->module, $this->_action, null, $opts );
            $msg     = _M('Tem certeza que deseja executar a tarefa', $this->module);
            $this->question($msg, $gotoYes);

            return;
        }

        $scheduleTaskId = MIOLO::_REQUEST('scheduleTaskId'); //pega o id  tarefa agendada
        $scheduleTask = $this->business->getScheduleTask($scheduleTaskId); //pega a tarefa agendada
        $busTask = $this->MIOLO->getBusiness($this->module, 'BusTask'); //instancia o business da tarefa
        $task = $busTask->getTask($scheduleTask->taskId); //pega a tarefa

        $localFile = $this->MIOLO->getConf('home.modules') . '/' . $this->module . '/misc/scripts'; //pega o path dos scripts
        $this->business->setScriptPath($localFile); //seta o path dos scripts
        $task = (object) array_merge((array) $task, (array) $scheduleTask); //mescla os dados da tarefa e tarefa agendada

        $obj = $this->business->instanceObjectTask($task); //intancia objeto da tarefa
        
        if ( !$obj )
        {
            throw new Exception( _M("Não foi possível criar a tarefa!", $this->module)  ) ;
        }
        
        $obj->setParameters($task->parameters); //seta os parâmetros

        $obj->setStartTime(); //registra a data/hora de início
        $obj->startLog(); //grava log de start
        
        $error = null;
       
        try
        {
            $ok = $obj->execute(); //executa
        }
        catch ( Exception $e )
        {
            //caso de erro registra no log e joga o erro na tela
            $obj->concludeLog(false, $e->getMessage() ); 
            throw $e;
        }
        
        //mostra a mensagem
        if( $ok instanceof Exception ) //Para tarefas que tenham lancado uma excessao.
        {
            $this->error(_M('A tarefa não foi executada!', $this->module), null, null, null, true);
            $obj->concludeLog(false, $ok->getMessage());
        }
        else if($ok) //Para qualquer tarefa que retorne true, sera mostrada mensagem de sucesso.
        {
            $obj->concludeLog($ok); //conclusão log
            $this->information(_M('A tarefa foi executada com sucesso!', $this->module), $this->getGotoCurrentActionAndSearchEventUrl());
        }
        else //Para qualquer tarefa que retorne por algum motivo false em sua execucao, ira acusar erro desconhecido.
        {
            $this->error(_M('A tarefa não foi executada!', $this->module), null, null, null, true);
            $obj->concludeLog(false, 'Erro desconhecido.');            
        }
    }
    
    /**
     * Método que mostra o log da tarefa
     */
    public function showLogOfTask()
    {
        $tb = new MDiv('divTable', $this->mountTable(10));
        
        $this->injectContent($tb, true, _M('Histórico do agendamento ', $this->module) . MIOLO::_REQUEST('scheduleTaskId') );
    }
    
    /**
     * Método que monsta a tabela de logs
     * @param integer $limit
     * @param integer id do agendamento
     * @return tabela com logs 
     */
    private function mountTable($limit=null, $scheduleTaskId=null)
    {
        $busLog = $this->MIOLO->getBusiness($this->module, 'BusScheduleTaskLog');
        $data = new stdClass();
        $data->scheduleTaskId = $scheduleTaskId ? $scheduleTaskId : MIOLO::_REQUEST('scheduleTaskId');
        $busLog->setData($data);
        $logs = $busLog->searchScheduleTaskLog('date desc, ord desc', $limit);
        
        if ( is_array($logs) )
        {
            $tbColumns = array( _M('Índice', $this->module), _M('Histórico', $this->module), _M('Data', $this->module),);
            $tb = new MTableRaw('', null, $tbColumns);
                        
            foreach ( $logs as $i => $log )
            {
                $logs[$i][0] = $i;
                //quando for erro, mostra mensagem em vermelho
                if ( $log[3] == 'END_ERROR' )
                {
                    $tb->setRowAttribute($i, 'style', 'color:#FF0000');
                }
                
                //esconde a última  e penúltima coluna
                $tb->setCellAttribute($i, 3, 'style', 'display:none');
                $tb->setCellAttribute($i, 4, 'style', 'display:none');
            }
            
            //modifica a última linha para ficar com somente uma coluna
            if ( ($limit == 10) && ( $busLog->countLogForScheduleTask() > 10) )
            {
                $link = new MLink('linkMoreRegisters', _M('Mostrar mais registros'), 'javascript:' . GUtil::getAjax('moreLogRegisters', $data->scheduleTaskId));
                $logs = array_merge($logs, array($link->generate()));
                $tb->setCellAttribute($i+1, 0, 'colspan', '3');
                $tb->setCellAttribute($i+1, 0, 'align', 'center');
            }
            
            $tb->setData($logs);
            $tb->zebra = true;
        }
        else
        {
            $tb = new MLabel(_M('Nenhum registro encontrado.', $this->module));
        }
        
        return $tb;
    }
    
    
    /**
     * Método ajax para atualizar a tabela de log com mais registros
     */
    public function moreLogRegisters($args)
    {
        $tb = $this->mountTable(5000, $args);
        
        $this->setResponse($tb, 'divTable');
    }
    
}
?>
