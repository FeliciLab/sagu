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
 * gtcTask business
 *
 * @author Luiz Gilberto Gregory F [luiz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 * Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 06/08/2009
 *
 **/


class BusinessGnuteca3BusScheduleTask extends GBusiness
{

    /**
     * ATTRIBUTES
     *
     */
    public $scheduleTaskId,        //      integer,
           $taskId,                //      integer,
           $scheduleCycleId,       //      integer,
           $description,           //      varchar,
           $cycleValue,            //      varchar,
           $enable,                //      boolean,
           $parameters;            //      varchar
    public $scheduleTaskIdS,        //      integer,
           $taskIdS,                //      integer,
           $scheduleCycleIdS,       //      integer,
           $descriptionS,           //      varchar,
           $cycleValueS,            //      varchar,
           $enableS,                //      boolean,
           $parametersS;            //      varchar        

    public $columns,
           $table       = 'gtcScheduleTask',
           $pkeys       = 'scheduleTaskId',
           $cols        = 'taskId, scheduleCycleId, description, cycleValue, enable, parameters';

    private $scriptBasePath = null,
            $startTime;
            
    const SCHEDULECYCLE_SEM_CICLO = 1;
    const SCHEDULECYCLE_ANUAL = 2;
    const SCHEDULECYCLE_MENSAL = 3;
    const SCHEDULECYCLE_SEMANAL = 4;
    const SCHEDULECYCLE_DIARIO = 5;         

    /**
     * Constructor class
     *
     */
    public function __construct()
    {
        $this->columns = "{$this->pkeys}, {$this->cols}";
        parent::__construct($this->table, $this->pkeys, $this->cols);
    }


    public function insertScheduleTask()
    {
    	$this->scheduleTaskId = $this->getNextBusScheduleId();
        return $this->autoInsert();
    }


    public function updateScheduleTask()
    {
       $data = array($this->taskId,
                     $this->scheduleCycleId,
                     $this->description,
                     $this->cycleValue,
                     $this->enable,
                     $this->parameters,
                     $this->scheduleTaskId);

        $this->clear();
        $this->setColumns($this->cols);
        $this->setTables($this->tables);
        $this->setWhere('scheduletaskid = ?');
        $sql = $this->update($data);
        $rs  = $this->execute($sql);
        return $rs;
    }

    /**
     * Apaga tarefa agendada e os logs gerados pela mesma caso esta tenha.
     * Resolvi instânciar outro bus aqui pela rastreabilidade e também por
     * no momento ele não ser necessário em nenhuma outra função da classe.
     * 
     * @param type $taskId
     * @return type
     */
    public function deleteScheduleTask($taskId)
    {
        $MIOLO = MIOLO::getInstance();
        $busScheduleTaskLog = $MIOLO->getBusiness ( $this->module, 'BusScheduleTaskLog' );
        
        if ( $busScheduleTaskLog->getScheduleTaskLog( $taskId, TRUE ) )
        {
            $busScheduleTaskLog->deleteScheduleTaskLog( $taskId );
        }
        
        return $this->autoDelete( $taskId );
    }

    public function getScheduleTask($taskId)
    {
        $this->clear();
        return $this->autoGet($taskId);
    }

    public function getAllScheduleTasksEnabled()
    {
        $this->clear();
        $this->setColumns
        (
            "gst.scheduleTaskId as ScheduleTaskId,
             gst.description    as ScheduleTaskDescription,
             gst.cycleValue     as ScheduleTaskCycleValue,
             gst.parameters     as ScheduleTaskParameters,
             gsc.description    as ScheduleCycleDescription,
             gsc.valueType      as ScheduleCycleValueType,
             gt.description     as TaskDescription,
             gt.parameters      as TaskParameters,
             gt.scriptName      as TaskScriptName"
        );

        $this->setTables
        (
            "gtcScheduleTask gst INNER JOIN gtcTask          gt   USING (taskId)
                                 INNER JOIN gtcScheduleCycle gsc  USING (scheduleCycleId)"
        );

        $this->setWhere("gst.enable = ? AND gt.enable = ?");
        $sql    = $this->select(array('t', 't'));
        $sql   .= " ORDER BY gst.scheduleCycleId DESC";

        return $this->query($sql, true);
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $path
     */
    public function setScriptPath($path)
    {
        $this->scriptBasePath = $path;
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $time
     */
    public function setStartTime()
    {
        $this->startTime->Y = date("Y");
        $this->startTime->m = date("m");
        $this->startTime->d = date("d");
        $this->startTime->H = date("H");
        $this->startTime->i = date("i");
        $this->startTime->s = date("s");
        $this->startTime->w = date("w"); 
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $time
     * @return unknown
     */
    public function getStartTime()
    {
        return $this->startTime;
    }


    /**
     * Este método é responsavel por executar as tarefas
     *
     */
    public function executeAllTasks($debug = false)
    {
        $tasks = $this->getAllScheduleTasksEnabled();

        if($debug)
        {
            echo "\n\n Task Ativas\n";
            echo print_r($tasks, 1);
            echo "\n";
        }

        if(!$tasks)
        {
            if($debug)
            {
                echo "Sem Task Ativas";
            }

            return;
        }

        $this->setStartTime();

        foreach ($tasks as $index => $taskAttributes)
        {
            // INSTANCIA O OBJETO
            $taskObject = $this->instanceObjectTask($taskAttributes);

            if(!$taskObject)
            {
                if($debug)
                {
                    echo "\n\nNão foi possível instanciar a classe da tarefa. Indice: $index;\n";
                }
                continue;
            }

            $taskObject->setStartTime($this->getStartTime());

            // VERIFICA SE PRECISA EXECUTAR
            if(!$taskObject->needExecute())
            {
                if($debug)
                {
                    echo "\n\nTarefa não precisa ser executada neste momento. Indice: $index;\n";
                }
                continue;
            }

            if(!method_exists($taskObject, "execute"))
            {
                if($debug)
                {
                    echo "\n\nMétodo execute() não foi implementado na tarefa. Indice: $index;\n";
                }
                continue;
            }

            $taskObject->setParameters($taskAttributes->ScheduleTaskParameters);
	    $ok = false;	            

            try 
            {
                $ok = $taskObject->execute();
             
                if($debug)
                {
                    echo "\n\nTarefa foi executada: Result: $ok. Indice: $index;\n\n";
                }

                $taskObject->concludeLog($ok);
            }
            catch ( Exception $e)
            {
                $taskObject->concludeLog($ok, $e->getMessage());
            }

        }
    }


    /**
     * Este metodo verifica se o arquivo da TASK existe e apos tenta instanciar um objeto desta classe
     *
     * @param object attributes $taskAttributes
     * @return object instance
     */
    public function instanceObjectTask($taskAttributes)
    {
    	if ( $taskAttributes->TaskScriptName )
    	{
            $taskFileInclude = ($taskAttributes->TaskScriptName[0] == "/") ? $taskAttributes->TaskScriptName : "{$this->scriptBasePath}/{$taskAttributes->TaskScriptName}";
    	}
    	else 
    	{
    		$taskFileInclude = ($taskAttributes->scriptName[0] == "/") ? $taskAttributes->scriptName : "{$this->scriptBasePath}/{$taskAttributes->scriptName}";
    	}
        if(!file_exists($taskFileInclude))
        {
            return false;
        }
        $this->MIOLO->getClass($this->module, 'GTask');
        require_once($taskFileInclude);

        $className = str_replace(".task.php", "", basename($taskFileInclude));
        
        if ( ! @class_exists( $className ) )
        {
            return false;
        }
        
        return new $className( $this->MIOLO, $taskAttributes );
    }
    
    
    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listScheduleTask()
    {
        $this->clear();
        $this->setColumns($this->cols);
        $this->setTables($this->table);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }
    
    
    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchScheduleTask()
    {
        $data = array();
        if ( $this->scheduleTaskIdS )
        {
            $this->setWhere('a.scheduletaskid = ?');
            $data[] = $this->scheduleTaskIdS;
        }
        
        if ( $this->taskIdS )
        {
            $this->setWhere('a.taskid = ?');
            $data[] = $this->taskIdS;
        }
        
        if ( $this->scheduleCycleIdS )
        {
            $this->setWhere('a.schedulecycleid = ?');
            $data[] = $this->scheduleCycleIdS;
        }
        
        if ( $this->descriptionS )
        {
            $this->setWhere("lower(a.description) LIKE lower('%{$this->descriptionS}%')");
        }
        
        if ( $this->cycleValueS )
        {
            $this->setWhere('a.cyclevalue = ?');
            $data[] = $this->cycleValueS;
        }

        if ( $this->enableS )
        {
            $this->setWhere('a.enable = ?');
            $data[] = $this->enableS;           
        }
        
        $this->setOrderBy('a.scheduletaskid');
        
        if ( $this->parametersS )
        {
            $this->setWhere('lower(a.parameters) LIKE lower (?)');
            $data[] = $this->parametersS . '%';
        }
    	
    	
        $this->setTables('gtcscheduletask a
                        LEFT JOIN gtcschedulecycle b
                               ON (a.schedulecycleid = b.schedulecycleid)
                        LEFT JOIN gtctask c
                               ON (a.taskid = c.taskid)');
        
        $this->setColumns('a.scheduletaskid,
                           c.description,
					       b.description,
					       a.description,
					       a.cyclevalue,
					       a.enable,
					       a.parameters');

        $sql = $this->select($data);
        $rs  = $this->query($sql);
        
        return $rs;
    }
    
    
    /**
     * This method list schedule cycles
     * 
     * @param (boolean) get
     * @return (array) with cycles or cycle
     */
    public function listScheduleCycles($get = false)
    {
    	$cycles = array( self::SCHEDULECYCLE_SEM_CICLO => _M('Sem ciclo', $this->module),
    	              self::SCHEDULECYCLE_ANUAL => _M('Anual', $this->module),
    	              self::SCHEDULECYCLE_MENSAL => _M('Mensal', $this->module),
    	              self::SCHEDULECYCLE_SEMANAL => _M('Semanal', $this->module),
    	              self::SCHEDULECYCLE_DIARIO => _M('Diário', $this->module) );
    	if ( $get )
    	{
    		return $cycles[$get];
    	}
    	else 
    	{
    		return $cycles;
    	}              
    }
    
    
    /**
     * This method list the value of schedule cycle
     * 
     * @param unknown_type $get
     * @return (array) with values or value
     */
    public function listValuetypeForScheduleCycles($get = false)
    {
        $cycles = array( self::SCHEDULECYCLE_SEM_CICLO => 'd/m/Y H',
                      self::SCHEDULECYCLE_ANUAL => 'd/m H',
                      self::SCHEDULECYCLE_MENSAL => 'd H',
                      self::SCHEDULECYCLE_SEMANAL => 'w H',
                      self::SCHEDULECYCLE_DIARIO => 'H' );
        if ( $get )
        {
            return $cycles[$get];
        }
        else 
        {
            return $cycles;
        }                  
    }
    
    
    /**
     * This method return the next id from scheduleTask
     * 
     * @return (int) id
     */
    public function getNextBusScheduleId()
    {
    	$this->clear();
        $this->setColumns('max(scheduletaskid)');
        $this->setTables($this->table);
        $sql = $this->select();
    	
        $rs  = $this->query($sql);
        return $rs[0][0] + 1;
    }

}
?>
