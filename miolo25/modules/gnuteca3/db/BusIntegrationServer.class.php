<?php

//$MIOLO = MIOLO::getInstance();
//$MIOLO->getClass('gnuteca3', 'GWorkflow');

class BusinessGnuteca3BusIntegrationServer extends GBusiness
{
    public $integrationServerId;
    public $nameServer;
    public $hostServer;
    public $emailServer;
    public $nameClient;
    public $emailClient;
    public $user;
    public $password;
    public $periodicity;
    public $libraryUnitId;
    public $serverWorkflowInstanceId;
    public $clientWorkflowInstanceId;
    
    public $status;
    public $dataSinc;
    
    public $busIntegrationLibrary;
    public $busWorkflowInstance;
    public $busWorkflowHistory;
    public $busIntegrationServerLog;
    
    public $unidadesBiblioteca;
    
    
    function __construct()
    {
        parent::__construct();
        $this->colsNoId = 'nameServer, 
                           hostServer,
                           emailServer, 
                           nameClient, 
                           emailClient, 
                           password,
                           periodicity,
                           "user",
                           serverWorkflowInstanceId,
                           clientWorkflowInstanceId';
        
        $this->id = 'integrationServerId';
        $this->columns  = 'integrationServerId, ' . $this->colsNoId;
        $this->tables   = 'gtcIntegrationServer';
        
        $this->businessLibraryUnit = $this->MIOLO->getBusiness('gnuteca3', 'BusLibraryUnit');
        
        //Utilizado para gravar na gtcIntegrationLibrary
        $this->busIntegrationLibrary = $this->MIOLO->getBusiness('gnuteca3', 'BusIntegrationLibrary');
        
        //Utilizado para pegar o workFlowInstance
        $this->busWorkflowInstance = $this->MIOLO->getBusiness('gnuteca3', 'BusWorkflowInstance');
        
        //Utilizado para pegar o workFlowHistory
        $this->busWorkflowHistory = $this->MIOLO->getBusiness('gnuteca3', 'BusWorkflowHistory');
        
        //Utilizado para pegar o name do workflowStatus
        $this->busWorkflowStatus = $this->MIOLO->getBusiness('gnuteca3', 'BusWorkflowStatus');
        
        //Utilizado para pegar o total de materiais
        $this->busMaterialControl= $this->MIOLO->getBusiness('gnuteca3', 'BusMaterialControl');
        
        //Utilizado para pegar o total de exemplares
        $this->busExemplaryControl = $this->MIOLO->getBusiness('gnuteca3', 'BusExemplaryControl');
        
        //Utilizado para excluir log
        $this->busIntegrationServerLog = $this->MIOLO->getBusiness('gnuteca3', 'BusIntegrationServerLog');

    }
    
    public function getIntegrationServer($IntegrationServerId)
    {
        
        $data = array($IntegrationServerId);
        $this->clear();
        $this->setColumns('A.integrationServerId,
                           A.nameServer, 
                           A.hostServer,
                           A.emailServer, 
                           A.nameClient, 
                           A.emailClient,
                           A."user",
                           A.password,
                           A.periodicity,
                           A.serverWorkflowInstanceId,
                           A.clientWorkflowInstanceId,
                           B.libraryUnitId');
        
        $this->setTables('      gtcIntegrationServer A
                         INNER JOIN
                                gtcIntegrationLibrary B
                         USING
                                (integrationServerId)
                         ');
        
        $this->setWhere('A.IntegrationServerId = ?');
        
        $sql = $this->select($data);
        
        $rs  = $this->query($sql, true);

        $this->setData($rs[0]);
        
        return $this;
    }

    public function searchIntegrationServer()
    {
        $this->clear();

        if ( $v = $this->nameServer )
        {
            $this->setWhere('A.nameServer = ?');
            $data[] = $v;
        }
        
        if ( $v = $this->hostServer )
        {
            $this->setWhere('A.hostServer = ?');
            $data[] = $v;
        }
        
        if($v = $this->status)
        {
            $this->setWhere('B.workflowstatusid = ?');
            $data[] = $v;
        }
        
        if($v = $this->dataSinc)
        {
            $this->setWhere('B.date = ?');
            $data[] = $v;
        }
        
        $this->setColumns('A.integrationServerId,
                           A.nameServer, 
                           A.hostServer,
                           B.workflowstatusid,
                           B.comment,
                           B.date
                           ');
        
       $this->setTables('
                            gtcIntegrationServer A
                 INNER JOIN gtcworkflowhistory B
                            ON (A.serverWorkflowInstanceId = B.workflowinstanceid)
                        ');

        $this->setOrderBy('integrationServerId');
        $sql = $this->select($data);
        
        //Variavel que conterá o objeto integrationServerId
        $rs  = $this->query($sql);
        
        //Objeto que terá todos os status
        $respStatus = $this->busWorkflowStatus->searchWorkflowStatus();
        
        //Para cada objeto
        foreach($rs as $rr)
        {
            //Verifica para cada status de workflow
            foreach ($respStatus as $rSS)
            {
                //Quando o Status do objeto for o mesmo do que o do corrente, 
                //atribui ao objeto para ser utilizado na pesquisa
                if($rr[3] == $rSS[0])
                {
                    $rr[] = $rSS[2];
                    $arrayComp[] = $rr;
                }
            }
        }
        return $arrayComp;
    }

    public function insertIntegrationServer()
    {
        //Define a URL de quem esta chamando a requisição, caso não tenha o parâmetro, não irá solicitar.
        if(strlen(INTEGRATION_MY_URL) > 0)
        {
            //Obter os dados do formulário
            $dados = parent::getData();

            //Instancia objeto data, com os dados do formulário
            $data = array($this->nameServer,
                          $this->hostServer,
                          $this->emailServer,
                          $this->nameClient,
                          $this->emailClient,
                          $this->user,
                          $this->password,
                          $this->periodicity,
                          $this->unidadesBiblioteca
                          );

            $this->clear();

            $this->setColumns('nameServer, 
                               hostServer,
                               emailServer, 
                               nameClient, 
                               emailClient,
                               "user",
                               password,
                               periodicity
                               ');

            $this->setTables($this->tables);  

            $sql = $this->insert($data) . ' RETURNING *';

            $rs  = $this->query($sql);
            
            $this->setData($rs[0]);
            $this->integrationServerId = $rs[0][0];

            //Pega as informações das unidades do Repetitive Field
            $unidades = $dados->unidadesBiblioteca;
            
            //Inserir dados na tabela gtcIntegrationLibrary
            if($rs)
            {
                //Para cada unidade do repetitive, registra na tabela gtcIntegrationLibrary
                foreach($unidades as $uni)
                {
                    //Passa como parametro o integrationServerId e o libraryUnitId
                    $this->busIntegrationLibrary->insertIntegrationLibrary($this->integrationServerId, $uni->libraryUnitId);
                }
            }
            //Abre requisição ao cliente, e obtem o clientWorkflowInstanceId
            $res = $this->abreRequisicaoAoCliente();
            
            return $rs;
        }
        else
        {
            throw new Exception("Você não tem a preferência INTEGRATION_MY_URL definida.");
            return false;
        }
    }
    
    /*
     * Recebe $libraryUnitIds como strng '1, 2, 3', referenciando o id da unidade de biblioteca
     */
    public function montaArquivo($libraryUnitIds, $biblioName)
    {
        //Define tempo para execução do script
        set_time_limit(120);
        
        //Lista das unidades deve ser separada por virgula.
        $unidade = $libraryUnitIds;
        
        //Testar se o diretório existe
        if(!is_dir(GNUTECA_DIR_BIBLIOVIRTUAL))
        {
            mkdir(GNUTECA_DIR_BIBLIOVIRTUAL);
        }
        
        //verifica se existe arquivo gtcMaterial
        if(!file_exists(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcMaterial.csv"))
        {
            $fp = fopen(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcMaterial.csv", "w+");
            chmod(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcMaterial.csv", 0777);
            fclose($fp);
        }
        
        //verifica se existe arquivo gtcMaterial
        if(!file_exists(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcMaterialControl.csv"))
        {
            $fp = fopen(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcMaterialControl.csv", "w+");
            chmod(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcMaterialControl.csv", 0777);
            fclose($fp);
        }
        
        //verifica se existe arquivo gtcMaterial
        if(!file_exists(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcExemplaryControl.csv"))
        {
            $fp = fopen(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcExemplaryControl.csv", "w+");
            chmod(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcExemplaryControl.csv", 0777);
            fclose($fp);
        }
        
        //verifica se existe arquivo gtcLibraryUnit
        if(!file_exists(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcLibraryUnit.csv"))
        {
            $fp = fopen(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcLibraryUnit.csv", "w+");
            chmod(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcLibraryUnit.csv", 0777);
            fclose($fp);
        }
        
        //Monta o arquivo gtcMaterial
        if(is_writable(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcMaterial.csv"))
        {
            
            //Sql para obter todos os números de controle referentes a uma unidade de biblioteca  
            
            $sql = "COPY (
                            SELECT * from gtcMaterial
                            WHERE controlnumber 
                            IN
                            (
                                SELECT DISTINCT(controlnumber) 
                                FROM gtcexemplarycontrol 
                                WHERE libraryunitid in ($unidade)
                            )
                         )
                         TO '" . GNUTECA_DIR_BIBLIOVIRTUAL . "gtcMaterial.csv' WITH CSV DELIMITER '|'";

            $rs = $this->query($sql);
            
        }else
        {
            throw new Exception("Problema com permissões no arquivo: " . GNUTECA_DIR_BIBLIOVIRTUAL. "gtcMaterial.csv");
            return false;
        }
        
        //Monta o arquivo gtcMaterialControl
        if(is_writable(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcMaterialControl.csv"))
        {
            //Sql para obter todos os números de controle referentes a uma unidade de biblioteca
            $sql = "COPY (
                            SELECT * from gtcMaterialControl
                            WHERE controlnumber 
                            IN
                            (
                                SELECT DISTINCT(controlnumber) 
                                FROM gtcexemplarycontrol 
                                WHERE libraryunitid in ($unidade)
                            )
                         )
                         TO '" . GNUTECA_DIR_BIBLIOVIRTUAL . "gtcMaterialControl.csv' WITH CSV DELIMITER '|'";

            $rs = $this->query($sql);
            
        }else
        {
            throw new Exception("Problema com permissões no arquivo: " . GNUTECA_DIR_BIBLIOVIRTUAL. "gtcMaterialControl.csv");
            return false;
        }
        
        //Monta o arquivo gtcExemplaryControl
        if(is_writable(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcExemplaryControl.csv"))
        {
            //Sql para obter todos os números de controle referentes a uma unidade de biblioteca
            $sql = "COPY (
                            SELECT * from gtcExemplaryControl
                            WHERE controlnumber 
                            IN
                            (
                                SELECT DISTINCT(controlnumber) 
                                FROM gtcexemplarycontrol 
                                WHERE libraryunitid in ($unidade)
                            )
                         )
                         TO '" . GNUTECA_DIR_BIBLIOVIRTUAL . "gtcExemplaryControl.csv' WITH CSV DELIMITER '|'";

            $rs = $this->query($sql);
            
        }else
        {
            throw new Exception("Problema com permissões no arquivo: " . GNUTECA_DIR_BIBLIOVIRTUAL. "gtcExemplaryControl.csv");
            return false;
        }
        
        //Monta o arquivo gtcLibraryUnit
        if(is_writable(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcLibraryUnit.csv"))
        {
            
            //Sql para obter todos os números de controle referentes a uma unidade de biblioteca  
            
            $sql = "COPY (
                            SELECT * from gtcLibraryUnit
                            WHERE libraryUnitId 
                            IN
                            (
                                SELECT DISTINCT(libraryUnitId) 
                                FROM gtcexemplarycontrol 
                                WHERE libraryunitid in ($unidade)
                            )
                         )
                         TO '" . GNUTECA_DIR_BIBLIOVIRTUAL . "gtcLibraryUnit.csv' WITH CSV DELIMITER '|'";

            $rs = $this->query($sql);
            
        }else
        {
            throw new Exception("Problema com permissões no arquivo: " . GNUTECA_DIR_BIBLIOVIRTUAL. "gtcLibraryUnit.csv");
            return false;
        }
        
        
        $zip = new ZipArchive();
        if($zip->open(GNUTECA_DIR_BIBLIOVIRTUAL."$biblioName.zip", ZipArchive::CREATE) === true)
        {
            $zip->addFile(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcMaterial.csv", "gtcMaterial.csv");
            $zip->addFile(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcMaterialControl.csv", "gtcMaterialControl.csv");
            $zip->addFile(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcExemplaryControl.csv", "gtcExemplaryControl.csv");
            $zip->addFile(GNUTECA_DIR_BIBLIOVIRTUAL. "gtcLibraryUnit.csv", "gtcLibraryUnit.csv");
            $zip->close();
        }
        
        //Aqui o arquivo já esta disponível para o envio: GNUTECA_DIR_BIBLIOVIRTUAL/material.zip
        $zipArq = file_get_contents(GNUTECA_DIR_BIBLIOVIRTUAL."$biblioName.zip");
        
        return $zipArq;
    }
    
    public function abreRequisicaoAoCliente()
    {
        $url = $this->hostServer;
        $class = "gnuteca3WebServicesIntegrationServer";

        $clientOptions["location"] = "$url/webservices.php?module=gnuteca3&action=main&class=$class";
        $clientOptions["uri"] = "$url";
        $clientOptions["encoding"] = "UTF-8";

        $cliente = new SoapClient(NULL, $clientOptions);
        
        $hostClient= INTEGRATION_MY_URL;
            
        //Define o nome de quem esta chamando a requisição
        $nameClient= $this->nameClient;

        //Quantidade inicial de materiais
        $totalMateriais = $this->busMaterialControl->countTotalObras();
        $initialMaterial = $totalMateriais[0];

        //Quantidade inicial de exemplares
        $totalExemplares = $this->busExemplaryControl->countTotalExemplares();
        $initialExemplary = $totalExemplares[0];
        
        //e-mail do cliente
        $emailClient= $this->emailClient;
            
        //Periodicidade para sincronização
        $periodicity= $this->periodicity;

        //Numero de instancia na base, referente ao serverworkflowinstance
        $serverWorkflowInstance = $this->serverWorkflowInstanceId;
            
        $result[] = $cliente->recebeRequisicao($hostClient, $nameClient, $initialMaterial, $initialExemplary, $emailClient, $periodicity, $serverWorkflowInstance);
        
        return $result;
    }
    
    public function getWorkflowInstanceByTableId()
    {
        //Obter o workflowInstanceid
        $this->busWorkflowInstance->tableId = $this->integrationServerId;
        $this->busWorkflowInstance->tableName = 'gtcIntegrationServer';
        $workflowInstance = $this->busWorkflowInstance->searchWorkflowInstance(true);
        $arr = $workflowInstance[0];
        
        //Define o valor para a variável
        $this->serverWorkflowInstanceId = $arr->workflowInstanceId;
    }
    
    public function completeInsert()
    {
        $data = array(
            $this->nameServer,
            $this->hostServer,
            $this->emailServer,
            $this->nameClient,
            $this->emailClient,
            $this->user,
            $this->password,
            $this->periodicity,
            $this->serverWorkflowInstanceId
        );
        
        $this->clear();
        $this->setColumns('nameServer, 
                           hostServer,
                           emailServer, 
                           nameClient, 
                           emailClient, 
                           "user",
                           password,
                           periodicity,
                           serverWorkflowInstanceId');
        
        $this->setTables($this->tables);
        
        $data[] = $this->integrationServerId;
        $this->setWhere('integrationServerId = ?');

        $sql = $this->update($data);
        
        $rs  = $this->execute($sql);

        return $rs;
    }
    
    public function updateIntegrationServer()
    {
        //------ Update dos campos, fora a Repetitive
        //
        $data = array(
            $this->integrationServerId,
            $this->nameServer,
            $this->hostServer,
            $this->emailServer,
            $this->nameClient,
            $this->emailClient,
            $this->user,
            $this->password,
            $this->periodicity
        );
        
        $this->clear();
        $this->setColumns('nameServer, 
                           hostServer,
                           emailServer, 
                           nameClient, 
                           emailClient, 
                           "user",
                           password,
                           periodicity');
        
        $this->setTables($this->tables);
        
        $this->setWhere('integrationServerId = ?');

        $sql = $this->update($data);
        
        $rs  = $this->execute($sql);

        return $rs;
    }

    public function atualizaClientInstanceId($cliInstanceId)
    {
        $data = array(
                        $this->clientWorkflowInstanceId = $cliInstanceId,
                        $this->integrationServerId
                      );
        
        $this->clear();
        
        $this->setColumns('clientWorkflowInstanceId');
        
        $this->setTables('gtcIntegrationServer');
        
        $data[] = $this->integrationServerId;
        $this->setWhere('integrationServerId = ?');

        $sql = $this->update($data);
        
        $rs  = $this->execute($sql);

        return $rs;
    }

    public function deleteIntegrationServer($integrationServerId)
    {   
        //Deletar o registro de log, se existir
        $this->busIntegrationServerLog->integrationServerId = $integrationServerId;
        $result = $this->busIntegrationServerLog->searchIntegrationServerLog(TRUE);

        //Deleta todos os registros do log
        if($result)
        {
            foreach($result as $res)
            {
                $this->busIntegrationServerLog->deleteIntegrationServerLog($res->integrationServerLogId);
            }
        }
        
        //Deleta todos os registros do workflow
        $this->busWorkflowInstance->tableId = $integrationServerId;
        $resultWInstance = $this->busWorkflowInstance->searchWorkflowInstance(TRUE);
        
        if($resultWInstance)
        {
            foreach($resultWInstance as $re2)
            {
                if($re2->tableName == 'gtcIntegrationServer')
                {
                    //Aqui vai excluir primeiro todos os workflowHistory
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
        
        //Deleta todos os registros de library
        $this->busIntegrationLibrary->integrationServerId = $integrationServerId;
        $respLib = $this->busIntegrationLibrary->searchIntegrationLibrary(TRUE);
        
        if($respLib)
        {
            foreach($respLib as $rL)
            {
                $this->busIntegrationLibrary->deleteIntegrationLibrary($rL->integrationLibraryId);
            }
        }
        
        //Antes de deletar a instancia do workflow, precisamos deletar a integrationServer
        //Deleta o integrationServer
        $this->clear();
        $tables  = 'gtcIntegrationServer';
        $where   = 'integrationServerId = ?';
        $data = array($integrationServerId);
        
        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
        $sql = $this->delete($data);
        $rs  = $this->execute($sql); 
        
        //Agora deletamos a instanceWorkflow :)
        if($rs)
        {
            foreach($resultWInstance as $re2)
            {
                if($re2->tableName == 'gtcIntegrationServer')
                {
                    $this->busWorkflowInstance->deleteWorkflowInstance($re2->workflowInstanceId);
                }
            }
        }
        return $rs;
    }
}
?>
