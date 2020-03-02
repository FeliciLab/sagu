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
class BusinessGnuteca3BusWorkflowHistory extends GBusiness
{
    public $workflowHistoryId;
    public $workflowInstanceId;
    public $workflowStatusId;
    public $date;
    public $operator;
    public $comment;

    public $workflowHistoryIdS;
    public $workflowInstanceIdS;
    public $workflowStatusIdS;
    public $dateS;
    public $operatorS;
    public $commentS;

    public function __construct( )
    {
        parent::__construct('gtcWorkflowHistory' , 'workflowHistoryId', 'workflowInstanceId,workflowStatusId, date,operator,comment');
    }

    public function insertWorkflowHistory()
    {
        //se for instancia de gdate obtem horário par ao banco
        if ( $this->date instanceof GDate )
        {
            $this->date = $this->date->getDate(GDate::MASK_TIMESTAMP_USER);
        }
        
        $ok = $this->autoInsert();

        //converte par aobjeto novamente
        $this->date = new GDate( $this->date );
        
        return $ok ;
    }

    public function updateWorkflowHistory()
    {
        //se for instancia de gdate obtem horário par ao banco
        if ( $this->date instanceof GDate )
        {
            $this->date = $this->date->getDate(GDate::MASK_TIMESTAMP_USER);
        }

        $ok = $this->autoUpdate();

        //converte par aobjeto novamente
        $this->date = new GDate( $this->date );

        return $ok ;
    }

    public function deleteWorkflowHistory( $workflowHistoryId )
    {
        return $this->autoDelete( $workflowHistoryId );
    }

    public function deleteWorkflowHistoryByInstance( $workflowInstanceId )
    {
        $rs = $this->query( "DELETE FROM gtcWorkflowHistory WHERE workflowInstanceId = {$workflowInstanceId};" );
        return $rs;
    }

    public function searchWorkflowHistory( $toObject  = false )
    {
        $this->setTables( $this->tables . ' H LEFT JOIN gtcWorkflowStatus  S ON ( H.workflowStatusId = S.workflowStatusId)');
        $this->setColumns( 'workflowHistoryId,workflowInstanceId,date,operator,comment,s.workflowStatusId ,workflowId,name as statusName, initial ,transaction' );

        $this->clear();

        $filters = array ('workflowInstanceId' => 'equals',
                          'workflowStatusId' => 'equals',
                          'date' => 'date',
                          'operator' => 'ilike',
                          'comment' => 'ilike'
                          );

        return $this->autoSearch( $filters , $toObject );
    }

    public function getWorkflowHistory ( $workflowHistoryId )
    {
        return $this->autoGet( $workflowHistoryId );
    }

    /**
     * Retorna todo o histórico para uma instancia
     * @param integer $workflowIntanceId
     * @return array of stdClass
     */
    public function getHistory( $workflowIntanceId )
    {
        if ( !$workflowIntanceId )
        {
            return false;
        }
        
        $this->workflowInstanceIdS = $workflowIntanceId;
        return $this->searchWorkflowHistory( true );
    }
}
?>