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
 *
 * @since
 * Class created on 06/08/2009
 *
 **/


class BusinessGnuteca3BusScheduleTaskLog extends GBusiness
{
    public $scheduleTaskId, //integer,
           $log,            //text,
           $date,           //datetime
           $status;         //varchar

    public $columns,
           $table       = 'gtcScheduleTaskLog',
           $pkeys       = 'scheduleTaskId',
           $cols        = 'log, date, status';


    public function __construct()
    {
        $this->columns = "{$this->pkeys}, {$this->cols}";
        parent::__construct($this->table, $this->pkeys, $this->cols);

        $this->MIOLO->getClass($this->module, 'GDate');
    }


    public function insertScheduleTaskLog()
    {
        $this->date = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        
        return $this->autoInsert();
    }


    public function updateScheduleTaskLog()
    {
        return $this->autoUpdate();
    }


    public function deleteScheduleTaskLog($taskId)
    {
        return $this->autoDelete($taskId);
    }


    public function getScheduleTaskLog( $taskId, $boolean = FALSE )
    {
        if( $boolean )
        {
            $this->clear();
            $result[0] = $this->autoGet( $taskId );
            return is_object($result[0]);
        }
        else
        {
            $this->clear();
            return $this->autoGet( $taskId );
        }
    }

    /**
     * Check se uma tarefa ja foi executada.
     *
     * @param db date $startDate
     * @param db date $endDate
     * @param string $logMsg
     * @return boolean
     */
    public function checkRun($scheduleTaskId, $startDate, $endDate, $logMsg)
    {
        $this->clear();
        $this->setColumns("1");
        $this->setTables($this->table);
        $this->setWhere(" scheduleTaskId = ? AND (date >= ? AND date <= ?) AND log = ?");
        $sql = $this->select(array($scheduleTaskId, $startDate, $endDate, $logMsg));
        $rs  = $this->query($sql);
        return (!$rs) ? false : true;
    }

    public function searchScheduleTaskLog($orderBy='date desc', $limit=null)
    {
    	if ($this->scheduleTaskId)
    	{
            $this->setWhere('scheduletaskid = ?');
            $data[] = $this->scheduleTaskId;
    	}
    	if ( $this->log )
    	{
            $this->setWhere('log = ?');
            $data[] = $this->log;
    	}
    	
        if ( $this->date )
        {
            $this->setWhere('date = ?');
            $data[] = $this->date;
        }
        
        $this->setTables('gtcscheduletasklog');
        
        $this->setColumns("scheduletaskid, 
                           log,
                           date,
                           status,
                           (CASE WHEN status = 'START' THEN 1 ELSE 2 END) as ord");
        $this->setOrderBy($orderBy);
        $sql = $this->select($data);
       
        //FIXME: setLimit não funcionou
        
        if ( $limit )
        {
            $sql .= " LIMIT {$limit}";
        }
        
        $rs  = $this->query($sql);
    	
        return $rs;
    }
    
    /**
     * Método que quantatidade de logs por agendamento
     * @return integer quantidade de registros no log 
     */
    public function countLogForScheduleTask()
    {
        $this->clear();
        
        $data = array();
        if ($this->scheduleTaskId)
    	{
            $this->setWhere('scheduletaskid = ?');
            $data[] = $this->scheduleTaskId;
    	}
        
        $this->setTables('gtcscheduletasklog');
        
        $this->setColumns('count(*)');
        
        $sql = $this->select($data);
        $rs  = $this->query($sql);
        
        
        return $rs[0][0];
    }
    
    public function setData($data)
    {
        $this->scheduleTaskId =
        $this->log = 
        $this->date =
        $this->status = null;
        
        parent::setData($data);
    }
    
    
}
?>