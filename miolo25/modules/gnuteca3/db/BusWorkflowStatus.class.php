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
class BusinessGnuteca3BusWorkflowStatus extends GBusiness
{
    public $MIOLO;
    public $module;

    public $workflowStatusId;
    public $workflowId;
    public $__name;
    public $initial;
    public $__transaction;

    public $name;
    public $transaction;

    public $workflowStatusIdS;
    public $workflowIdS;
    public $__nameS;
    public $initialS;
    public $__transactionS;
    
    public $busOperatorGroup;

    
    function __construct()
    {
        parent::__construct();
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busOperatorGroup = $this->MIOLO->getBusiness( $this->module, 'BusOperatorGroup');
        $this->tables = 'gtcWorkflowStatus';
        $this->id = 'workflowStatusId';
        $this->columnsNoId = 'workflowId,name,initial,transaction';
        $this->columns = $this->id .','.$this->columnsNoId;
    }

    public function insertWorkflowStatus()
    {
        return $this->autoInsert();
    }

    public function updateWorkflowStatus()
    {
        return $this->autoUpdate();
    }

    public function deleteWorkflowStatus($workflowStatusId)
    {
        return $this->autoDelete($workflowStatusId);
    }

    public function searchWorkflowStatus($toObject = false)
    {
        $this->clear();
        
        $this->name = $this->__nameS; //o function tem underline em função de function ser uma variável do form
        $this->transaction = $this->__transactionS; //o function tem underline em função de function ser uma variável do form

        $filters = array ('workflowStatusId' => 'equals',
                          'workflowId' => 'equals',
                          'name' => 'ilike',
                          'initial' => 'equals',
                          'transaction' => 'equals');
        
        $workflowStatus =  $this->autoSearch($filters,$toObject);
        
        $transactions = BusinessGnuteca3BusOperatorGroup::listTransactions();
        
        // Percorre o resultado para adicionar a descrição da transação.
        if ( is_array($workflowStatus) )
        {
            foreach ( $workflowStatus as $i => $status )
            {
                if ( !$toObject )
                {
                    if ( strlen($workflowStatus[$i][4]) )
                    {
                        $workflowStatus[$i][5] = $transactions[$workflowStatus[$i][4]];
                    }
                }
                else
                {
                    if ( strlen($workflowStatus[$i]->transaction) )
                    {
                        $workflowStatus[$i]->transactionDescription = $transactions[$workflowStatus[$i]->transaction];
                    }
                }
            }
        }
        
        return $workflowStatus;
    }

    public function listWorkflowStatus( $workflowId )
    {
        $this->columns = 'workflowstatusid,name';
        $this->workflowIdS = $workflowId;
        
        return $this->searchWorkflowStatus( false );
    }

    public function getWorkflowStatus ($workflowStatusId)
    {
        return $this->autoGet($workflowStatusId);
    }

    /**
     * Retorna o estado inicial para um código de workfloe
     * @param integer $workflowId
     * @return stdClass
     */
    public function getInitialStatus( $workflowId )
    {
        $this->workflowIdS = $workflowId;
        $this->initialS = DB_TRUE;
        $status = $this->searchWorkflowStatus(true);

        if ( count( $status ) > 1 )
        {
            throw new Exception( _M( 'Existe mais de um estado inicial para o workflow @1.','gnuteca3' , $workflowId) );
        }

        return $status[0];
    }

    
}
?>