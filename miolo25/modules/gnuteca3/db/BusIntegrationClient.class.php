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
 * @author Lucas Gerhardt [lucas_gerhardt@solis.com.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 14/04/2014
 *
 * */

class BusinessGnuteca3BusIntegrationClient extends GBusiness
{

    public $colsNoId;
    public $fullColumns;
    public $tables;
    public $MIOLO;
    public $module;
    
    public $integrationClientId;
    public $hostClient;
    public $nameClient;
    public $initialAmountClientMaterials;
    public $initialAmountClientExemplarys;
    public $countMaterials1;
    public $countMaterials2;
    public $countExemplaries1;
    public $countExemplaries2;
    public $emailClient;
    public $periodicity;
    public $serverWorkflowInstanceId;
    public $clientWorkflowInstanceId;
    
    public $status;
    
    public $busWorkflowInstance;
    public $busWorkflowHistory;
    public $busWorkflowStatus;
    public $busIntegrationClientLog;
    
    public function __construct()
    {
        parent::__construct();
        $this->MIOLO = MIOLO::getInstance();
        $this->tables = 'gtcIntegrationClient';
        $this->colsNoId = 'hostClient,
                           nameClient,
                           emailClient,
                           initialAmountClientMaterials,
                           initialAmountClientExemplarys,
                           periodicity,
                           serverWorkflowInstanceId,
                           clientWorkflowInstanceId';

        $this->fullColumns = 'integrationClientId, ' . $this->colsNoId;
        
    }
    
    public function getIntegrationClient($integrationClientId)
    {
        $data = array($integrationClientId);
        
        $this->clear();
        $this->setColumns('integrationClientId,
                           nameClient,
                           hostClient,
                           initialAmountClientMaterials,
                           initialAmountClientExemplarys,
                           emailClient,
                           periodicity');
        $this->setTables($this->tables);
        $this->setWhere('integrationClientId = ?');
        $sql = $this->select($data);
        $rs = $this->query($sql, TRUE);
        
        //Atribui periodicidade para formatar campo
        $getPeriod = $rs[0]->periodicity;
        
        $rs[0]->periodicity = $this->periodValue($getPeriod);
        
        $this->setData($rs[0]);
        
        return $this;
    }
    
    public function deletaInvalidos()
    {
        $sql = "DELETE FROM gtcintegrationclient where serverworkflowinstanceid is null;";
        $rs = $this->query($sql);
        return $rs;
    }
    
    public function periodValue($period)
    {
        switch($period)
        {
            case 1:
                $resp = "Sem ciclo";
                break;
            case 2:
                $resp = "Anual";
                break;
            case 3:
                $resp = "Mensal";
                break;
            case 4:
                $resp = "Semanal";
                break;
            case 5:
                $resp = "Diário";
                break;
        }
        return $resp;
    }
    
    public function insertIntegrationClient()
    {
        //Instancia objeto data, com os dados do formulário
        $data = array($this->hostClient,
                      $this->nameClient,
                      $this->emailClient,
                      $this->initialAmountClientMaterials,
                      $this->initialAmountClientExemplarys,
                      $this->periodicity,
                      $this->serverWorkflowInstanceId
                      );

        $this->clear();
        
        $this->setColumns('hostClient, 
                           nameClient,
                           emailClient,
                           initialAmountClientMaterials, 
                           initialAmountClientExemplarys,
                           periodicity,
                           serverWorkflowInstanceId
                           ');
        
        $this->setTables($this->tables);  
        
        $sql = $this->insert($data) . ' RETURNING *';
        
        $rs  = $this->query($sql);
        
        return $rs;
    }
    
    
    /*
     * Atualiza o parametro clientWorkflowInstanceId
     * Criado por Tcharles Silva em 04/2014
     */
    public function atualizaClientWorkflowInstanceId($cliInstanceId)
    {
        $data = array(
                        $this->clientWorkflowInstanceId,
                        $this->integrationClientId = $cliInstanceId
                      );
        

        $this->clear();
        
        $this->setColumns('clientWorkflowInstanceId');
        
        $this->setTables('gtcIntegrationClient');
        
        $data[] = $this->integrationClientId;
        $this->setWhere('integrationClientId = ?');

        $sql = $this->update($data);
        
        $rs  = $this->execute($sql);

        return $rs;
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     * */
    public function searchIntegrationClient($returnAsObject = false)
    {
        $busWorkflowInstance = $this->MIOLO->getBusiness($this->module, 'BusWorkflowInstance');
        $busWorkflowStatus = $this->MIOLO->getBusiness($this->module, 'BusWorkflowStatus');
        
        $this->clear();
        
        if ($v = $this->integrationClientId)
        {
            $this->setWhere('A.integrationClientId = ?');
            $data[] = $v;
        }

        if ($v = $this->hostClient)
        {
            $this->setWhere('lower(A.hostClient) LIKE lower(?)');
            $data[] = $v;
        }

        if ($v = $this->nameClient)
        {
            $this->setWhere('lower(unaccent(A.nameClient)) LIKE lower(?)');
            $data[] = $v;
        }
        
        if ($v = $this->emailClient)
        {
            $this->setWhere('lower(A.emailClient) LIKE lower(?)');
            $data[] = $v;
        }
        

        if ($v = $this->countMaterials1)
        {
            $this->setWhere('A.initialAmountClientMaterials >= ?');
            $data[] = $v;
        }
        
        if ($v = $this->countMaterials2)
        {
            $this->setWhere('A.initialAmountClientMaterials <= ?');
            $data[] = $v;
        }
        
        if ($v = $this->countExemplaries1)
        {
            $this->setWhere('A.initialAmountClientExemplarys >= ?');
            $data[] = $v;
        }
        
        if ($v = $this->countExemplaries2)
        {
            $this->setWhere('A.initialAmountClientExemplarys <= ?');
            $data[] = $v;
        }

        if ($v = $this->periodicity)
        {
            $this->setWhere('A.periodicity = ?');
            $data[] = $v;
        }

        if ($v = $this->serverWorkflowInstanceId)
        {
            $this->setWhere('A.serverWorkflowInstanceId = ?');
            $data[] = $v;
        }

        if ($v = $this->clientWorkflowInstanceId)
        {
            $this->setWhere('A.clientWorkflowInstanceId = ?');
            $data[] = $v;
        }
        
        if ($v = $this->status)
        {
            $this->setWhere("B.workflowstatusid = ?");
            $data[] = $v;
        }
        
        $this->setColumns($this->fullColumns);
        $this->setTables("
                            gtcIntegrationClient A
                 INNER JOIN gtcworkflowhistory B
                            ON (A.clientWorkflowInstanceId = B.workflowinstanceid)
                        ");
        
        $this->setOrderBy('integrationClientId');
        $sql = $this->select($data);
        
        $rs =  $this->query($sql, $returnAsObject);
        
        foreach($rs as $rr)
        {
            $vartt = $this->periodValue($rr[6]);
            $rr[6] = $vartt;
            $retorno[] = $rr;
        }
        
        foreach ($retorno as $ret)
        {
            $busWorkflowInstance->workflowInstanceId = $ret[8];
            $wInstance = $busWorkflowInstance->searchWorkflowInstance(TRUE);
            
            $busWorkflowStatus->workflowStatusId = $wInstance[0]->workflowStatusId;
            $wStatus = $busWorkflowStatus->searchWorkflowStatus(TRUE);
            
            $ret[] = $wStatus[0]->name;
            
            $retObj[] = $ret;
        }
        
        return $retObj;
    }
    
    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     * */
    public function updateIntegrationClient()
    {
        $columns = 'nameClient,
                    hostClient,
                    initialAmountClientMaterials,
                    initialAmountClientExemplarys,
                    emailClient';
        $this->clear();
        $this->setColumns($columns);
        $this->setTables($this->tables);
        $this->setWhere('integrationClientId = ?');
        
        $sql = $this->update($this->associateData($columns . ', integrationClientId'));
        $rs = $this->execute($sql);
        
        return $rs;
    }
    
    public function getStatusWorkflow($status)
    {
        $this->clear();
        $this->setColumns('*');
        $this->setTables('gtcWorkflowStatus');
        $this->setWhere('workflowId = ?');
        
        $sql = $this->select($status);
        $rs = $this->query($sql);
        
        foreach ($rs as $i => $result)
        {
            $rs[$i] = array($result[0], $result[2]);
        }
        return $rs;
    }
    
    public function getSynchronizations()
    {
        $integrationClientId = MIOLO::_REQUEST('integrationClientId');
        $this->clear();
        $this->setColumns('integrationClientLogId,
                           date,
                           materialsSyncronized,
                           exemplariesSinchronized');
        $this->setTables('gtcIntegrationClientLog');
        $this->setWhere('integrationClientId = ?');
        
        $sql = $this->select($integrationClientId);
        $rs = $this->query($sql);
        
        return $rs;
    }
    
    public function getInitialAmountClientMaterials($integrationClientId)
    {
        $this->clear();
        $this->setColumns('initialAmountClientMaterials');
        $this->setTables('gtcIntegrationClient');
        $this->setWhere('integrationClientId = ?');
        
        $sql = $this->select($integrationClientId);
        $rs = $this->query($sql);

        return $rs[0][0];
    }    
    
    public function getInitialAmountClientExemplarys($integrationClientId)
    {
        $this->clear();
        $this->setColumns('initialAmountClientExemplarys');
        $this->setTables('gtcIntegrationClient');
        $this->setWhere('integrationClientId = ?');
        
        $sql = $this->select($integrationClientId);
        $rs = $this->query($sql);
        
        return $rs[0][0];
    }
    
    public function deleteIntegrationClient($integrationClientId)
    {
        $this->busIntegrationClientLog = $this->MIOLO->getBusiness($this->module, 'BusIntegrationClientLog');
        $this->busWorkflowInstance = $this->MIOLO->getBusiness($this->module, 'BusWorkflowInstance');
        $this->busWorkflowHistory = $this->MIOLO->getBusiness($this->module, 'BusWorkflowHistory');
        
        //Deletar o registro de log
        $this->busIntegrationClientLog->integrationClientid = $integrationClientId;
        $result = $this->busIntegrationClientLog->searchIntegrationClientLog(TRUE);
        
        //Deleta todos os registros do log
        foreach($result as $res)
        {
            $this->busIntegrationClientLog->deleteIntegrationClientLog($res->integrationClientLogId);
        }
        
        //Deleta todos os registros do workflow
        $this->busWorkflowInstance->tableId = $integrationClientId;
        $resultWInstance = $this->busWorkflowInstance->searchWorkflowInstance(TRUE);
        
        if($resultWInstance)
        {
            foreach($resultWInstance as $re2)
            {
                if($re2->tableName == 'gtcIntegrationClient')
                {
                    $this->busWorkflowHistory->workflowInstanceId = $re2->workflowInstanceId;
                    
                    $wHistory = $this->busWorkflowHistory->searchWorkflowHistory(TRUE);
                    
                    foreach($wHistory as $wH)
                    {
                        //Fazendo a limpa nos history
                        $this->busWorkflowHistory->deleteWorkflowHistoryByInstance($re2->workflowInstanceId);
                    }
                }
            }
        }
        $this->clear();
        $tables  = 'gtcIntegrationServer';
        $where   = 'integrationClientId = ?';
        $data = array($integrationClientId);

        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
        $sql = $this->delete($data);

        $rs  = $this->execute($sql);
        
        if($rs)
        {
            foreach($resultWInstance as $re2)
            {
                if($re2->tableName == 'gtcIntegrationClient')
                {
                    $ab = $this->busWorkflowInstance->deleteWorkflowInstance($re2->workflowInstanceId);
                }
            }
        }
        return $rs;
    }
}
?>
