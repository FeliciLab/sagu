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
*
* This file handles the connection and actions for gtcLibraryUnit table
*
* @author Guilherme Soldateli [guilherme@solis.coop.br]
*
* $version: $Id$
*
* \b Maintainers \n
* Guilherme Soldateli [guilherme@solis.coop.br]
* Eduardo Bonfandini [eduardo@solis.coop.br]
* Jamiel Spezia [jamiel@solis.coop.br]
* Luiz Gregory Filho [luiz@solis.coop.br]
* Moises Heberle [moises@solis.coop.br]
* Sandro Roberto Weisheimer [sandrow@solis.coop.br]
*
* @since
* Class created on 29/07/2008
*
**/
class BusinessGnuteca3BusWorkflowTransition extends GBusiness
{
    public $MIOLO;
    public $module;

    public $busWorkflowStatus;

    public $previousWorkflowStatusId;
    public $nextWorkflowStatusId;
    public $__function; //o function tem underline em função de function ser uma variável do form
    public $__name;

    public $name;
    public $function;

    public $previousWorkflowStatusIdS;
    public $nextWorkflowStatusIdS;
    public $__functionS; //o function tem underline em função de function ser uma variável do form
    public $__nameS;
    public $workflowIdS;

    function __construct()
    {
        parent::__construct();
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->tables = 'gtcWorkflowTransition';
        $this->id = 'previousWorkflowStatusId,nextWorkflowStatusId';
        $this->columnsNoId = 'function,name';
        $this->columns = $this->id .','.$this->columnsNoId;

        $this->busWorkflowStatus = $this->MIOLO->getBusiness('gnuteca3','BusWorkflowStatus');
    }

    function insertWorkflowTransition()
    {
        return $this->autoInsert();
    }

    function updateWorkflowTransition()
    {
        return $this->autoUpdate();
    }

    function deleteWorkflowTransition($previousWorkflowStatusId,$nextWorkflowStatusId)
    {
        return $this->autoDelete($previousWorkflowStatusId,$nextWorkflowStatusId);
    }

    function searchWorkflowTransition( $toObject = false , $removeInitialTransation = false )
    {
        $this->clear();
        $this->function = $this->__functionS; //o function tem underline em função de function ser uma variável do form
        $this->name = $this->__nameS; //o name tem underline em função de function ser uma variável do form

        $this->setColumns('WT.previousWorkflowStatusId,WT.nextWorkflowStatusId,(SELECT distinct(label) from basDomain WHERE key = prevStatus.workflowid LIMIT 1) as workflow,prevStatus.name,nextStatus.name,WT.function,WT.name,nextStatus.transaction as nextTransaction');
        $this->setTables('gtcworkflowtransition WT LEFT JOIN  gtcworkflowstatus prevStatus ON  (prevStatus.workflowstatusid = WT.previousWorkflowStatusId) 
                                                   LEFT JOIN gtcworkflowstatus nextStatus ON (nextStatus.workflowstatusid = WT.nextWorkflowStatusId)');
        
        $filters = array ('previousWorkflowStatusId' => 'equals',
                          'nextWorkflowStatusId' => 'equals',
                          'function' => 'ilike'
                          );

        $args = $this->addFilters($filters);

        if ( $this->name )
        {
            $this->setWhere(' upper(WT.name) like upper(?) ');
            $args[] = '%'.$this->name.'%';
        }

        if ( $this->workflowIdS )
        {
            $this->setWhere( "nextStatus.workflowstatusid IN ( SELECT distinct (workflowstatusid) FROM gtcworkflowstatus where upper(workflowid) = upper(?) ) " );
            $args[] = $this->workflowIdS;
        }
        
        if ( $this->nextWorkflowStatusIdS )
        {
            $this->setWhere( "WT.nextWorkflowStatusId = ?" );
            $args[] = $this->nextWorkflowStatusIdS;
        }
        
        if ( $this->previousWorkflowStatusIdS )
        {
            $this->setWhere( "WT.previousWorkflowStatusId = ?" );
            $args[] = $this->previousWorkflowStatusIdS;
        }

        //para remover transições inicial para inicial
        if ( $removeInitialTransation )
        {
            //$this->setWhere( "( previousWorkflowStatusId != WT.nextWorkflowStatusId AND  nextStatus.initial = 'f')" );
            $this->setWhere( "(nextStatus.initial = 'f')" );
        }

        return $this->query($this->select($args),$toObject);
    }
    
    /**
     * Retorna stdClass da transição com o atributo workflowId junto.
     * 
     * @param integer $previousWorkflowStatusId
     * @param integer $nextWorkflowStatusId
     * @return stdClass
     */
    function getWorkflowTransition ($previousWorkflowStatusId , $nextWorkflowStatusId)
    {
        $result = $this->autoGet($previousWorkflowStatusId, $nextWorkflowStatusId);
        $workflowStatus = $this->busWorkflowStatus->getWorkflowStatus($previousWorkflowStatusId);
        $this->workflowId = $workflowStatus->workflowId;

        return $result;
    }

    /**
     * Obtem os estados futuros para estado passado
     * @param integer $workflowStatusId código do estado do workflow
     * @return array de stdClass
     */
    public function getFutureStatus( $workflowStatusId )
    {
        $this->previousWorkflowStatusId = $workflowStatusId;

        return $this->searchWorkflowTransition( true, true ); //removeInitialTransation
    }
}
?>