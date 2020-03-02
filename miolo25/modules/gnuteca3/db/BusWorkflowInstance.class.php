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
* @author Eduardo Bonfandini [eduardo@solis.coop.br]
*
* $version: $Id$
*
* \b Maintainers \n
* Guilherme Soldateli [guilherme@solis.coop.br]
* Eduardo Bonfandini [eduardo@solis.coop.br]
* Jamiel Spezia [jamiel@solis.coop.br]
*
* @since
* Class created on 29/07/2008
*
**/
class BusinessGnuteca3BusWorkflowInstance extends GBusiness
{
    /**
     * Código da instancia
     * 
     * @var integer
     */
    public $workflowInstanceId;
    /**
     * Código de workflow
     *
     * @var string
     */
    public $workflowStatusId;
    /**
     * Data de criação do registro
     * @var GDate
     */
    public $date;
    /**
     * Nome da tabela relacionada
     * @var string
     */
    public $tableName;
    /**
     * Código da tabela relacionada
     *
     * @var string
     */
    public $tableId;

    public function __construct( )
    {
        parent::__construct('gtcWorkflowInstance' , 'workflowInstanceId', 'workflowStatusId,date,tableName,tableId');
    }

    /**
     * Faz inserção da instancia registrando o histórico.
     *
     * @param string $operator operador para histórico
     * @param comment $comment comentário para histórico
     * @return boolean
     */
    public function insertWorkflowInstance( $operator , $comment )
    {
        //se for instancia de gdate obtem horário par ao banco
        if ( $this->date instanceof GDate )
        {
            $this->date = $this->date->getDate(GDate::MASK_TIMESTAMP_USER);
        }
        
        $ok = $this->autoInsert();

        //caso a atualização tenha ocorrido corretamente, registra histórico
        if ( $ok )
        {
            $busWorkflowHistory = $this->MIOLO->getBusiness( $this->module, 'BusWorkflowHistory' );
            $busWorkflowHistory =new BusinessGnuteca3BusWorkflowHistory();
            $busWorkflowHistory->workflowInstanceId = $this->workflowInstanceId;
            $busWorkflowHistory->date = GDate::now();
            $busWorkflowHistory->operator = $operator ? $ok : GOperator::getOperatorId(); //caso não passe obtem operador logado
            $busWorkflowHistory->comment = $comment;
            $busWorkflowHistory->workflowStatusId = $this->workflowStatusId;
            $busWorkflowHistory->insertWorkflowHistory();
        }

        //converte par aobjeto novamente
        $this->date = new GDate( $this->date );
        
        return $ok ;
    }

    /**
     * Faz atualização da instancia registrando o histórico.
     *
     * @param string $operator operador para histórico
     * @param comment $comment comentário para histórico
     * @return boolean
     */
    public function updateWorkflowInstance( $operator , $comment )
    {
        //se for instancia de gdate obtem horário par ao banco
        if ( $this->date instanceof GDate )
        {
            $this->date = $this->date->getDate(GDate::MASK_TIMESTAMP_USER);
        }

        $ok = $this->autoUpdate();

        //caso a atualização tenha ocorrido corretamente, registra histórico
        if ( $ok )
        {
            $busWorkflowHistory = $this->MIOLO->getBusiness( $this->module, 'BusWorkflowHistory' );
            $busWorkflowHistory = new BusinessGnuteca3BusWorkflowHistory();
            $busWorkflowHistory->workflowInstanceId = $this->workflowInstanceId;
            $busWorkflowHistory->date = GDate::now();
            $busWorkflowHistory->operator = $operator ? $ok : GOperator::getOperatorId(); //caso não passe obtem operador logado
            $busWorkflowHistory->comment = $comment;
            $busWorkflowHistory->workflowStatusId = $this->workflowStatusId;
            $busWorkflowHistory->insertWorkflowHistory();
        }

        //converte par aobjeto novamente
        $this->date = new GDate( $this->date );

        return $ok ;
    }

    public function deleteWorkflowInstance( $workflowInstanceId )
    {
        $busWorkflowHistory = $this->MIOLO->getBusiness( $this->module, 'BusWorkflowHistory' );
        $busWorkflowHistory = new BusinessGnuteca3BusWorkflowHistory();
        $busWorkflowHistory->deleteWorkflowHistoryByInstance( $workflowInstanceId );
        
        return $this->autoDelete( $workflowInstanceId );
    }

    public function searchWorkflowInstance( $toObject  = false )
    {
        $this->clear();
        $filters = array ('workflowInstanceId' => 'equals',
                          'workflowStatusId' => 'equals',
                          'date' => 'ilike',
                          'tableName' => 'ilike',
                          'tableId' => 'equal'
                          );

        return $this->autoSearch( $filters , $toObject );
    }

    public function getWorkflowInstance ( $workflowInstanceId )
    {
        return $this->autoGet( workflowInstanceId );
    }

    /**
     * Retorna stdClass com as informações para o instancia atual.
     *
     * @param integer $workflowId
     * @param string $tableName
     * @param string $tableId
     * @return stdClass
     */
    public function getCurrentWorkflowInstance( $workflowId, $tableName, $tableId )
    {
        $this->setTables('gtcWorkflowInstance i LEFT JOIN gtcWorkflowStatus s ON ( s.workflowstatusid = i.workflowstatusid )');
        $this->setColumns("workflowInstanceId,date,tableName,tableId,workflowId, s.workflowStatusId as workflowStatusId,name as statusName,initial,transaction");
        $this->setWhere('workflowId = ? ');
        $args[] = $workflowId;
        $this->setWhere('tableName = ? ');
        $args[] = $tableName;
        $this->setWhere('tableId = ? ');
        $args[] = $tableId;
        $query = $this->query( $this->select( $args ), true);
        return $query[0];
    }
}
?>