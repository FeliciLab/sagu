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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 *
 * @since
 * Class created on 11/01/2011
 *
 * */
class BusinessGnuteca3BusBackgroundTaskLog extends GBusiness
{

    public $backgroundTaskLogId;
    public $beginDate;
    public $beginDateFinal;
    public $endDate;
    public $endDateFinal;
    public $task;
    public $label;
    public $status;
    public $message;
    public $operator;
    public $args;
    public $libraryUnitId;
    public $backgroundTaskLogIdS;
    public $beginDateS;
    public $beginDateFinalS;
    public $endDateS;
    public $endDateFinalS;
    public $taskS;
    public $labelS;
    public $statusS;
    public $messageS;
    public $operatorS;
    public $argsS;
    public $libraryUnitIdS;

    public function __construct()
    {
        parent::__construct('gtcBackgroundTaskLog', 'backgroundTaskLogId', 'beginDate, endDate, task, label, status, message,operator,args, libraryUnitId');
    }

    public function insertBackgroundTaskLog()
    {
        return $this->autoInsert();
    }

    public function searchBackgroundTaskLog($object = false)
    {
        $this->clear();

        $this->beginDateS = trim($this->beginDateS);
        $this->endDateS = trim($this->endDateS);
        $this->beginDateFinalS = trim($this->beginDateFinalS);
        $this->endDateFinalS = trim($this->endDateFinalS);

        $this->setColumns($this->columns);
        $this->setTables($this->tables);

        $filters = array(
            'backgroundTaskLogId' => 'equals',
            'task' => 'ilike',
            'label' => 'ilike',
            'status' => 'equals',
            'message' => 'ilike',
            'operator' => 'ilike',
            'args' => 'ilike',
            'libraryUnitId' => 'equal'
        );

        $args = $this->addFilters($filters);

        $timeType = 'timestamp';

        //caso venha somente horário em algum deles, converte para tempo/time
        if (strlen($this->beginDateS) == 8 || strlen($this->beginDateFinalS) == 8 || strlen($this->endDateS) == 8 || strlen($this->endDateFinalS) == 8)
        {
            $timeType = 'time';
        }

        if ($this->beginDateS && $this->beginDateFinalS)
        {
            $this->setWhere("beginDate::{$timeType} between ?::{$timeType} And ?::{$timeType}");
            $args[] = $this->beginDateS;
            $args[] = $this->beginDateFinalS;
        }
        else if ($this->beginDateS)
        {
            $this->setWhere("beginDate::{$timeType} > ?::{$timeType}");
            $args[] = $this->beginDateS;
        }
        else if ($this->beginDateFinalS)
        {
            $this->setWhere("beginDate::{$timeType} < ?::{$timeType}");
            $args[] = $this->beginDateFinalS;
        }



        if ($this->endDateS && $this->endDateFinalS)
        {
            $this->setWhere("endDate::{$timeType} between ?::{$timeType} And ?::{$timeType}");
            $args[] = $this->endDateS;
            $args[] = $this->endDateFinalS;
        }
        else if ($this->endDateS)
        {
            $this->setWhere("endDate::{$timeType} > ?::{$timeType}");
            $args[] = $this->endDateS;
        }
        else if ($this->endDateFinalS)
        {
            $this->setWhere("endDate::{$timeType} < ?::{$timeType}");
            $args[] = $this->endDateFinalS;
        }
        
        return $this->query($this->select($args), $object);
    }

    public function updateBackgroundTaskLog()
    {
        return $this->autoUpdate();
    }

    public function deleteBackgroundTaskLog($id)
    {
        return $this->autoDelete($id);
    }

    public function getBackgroundTaskLog($id)
    {
        $this->clear();
        return $this->autoGet($id);
    }

}

?>