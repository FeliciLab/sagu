<?php

include("GnutecaWebServices.class.php");
$MIOLO->getClass('gnuteca3', 'GWorkflow');

class gnuteca3WebServicesIntegrationServer extends GWebServices 
{
    public $MIOLO;
    public $module;
    public $busMaterialControl;
    public $busIntegrationClient;
    public $busWorkflowInstance;
    public $busFile;
    public $busPreference;
    
    public function __construct() {
        
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        
        $this->busMaterialControl = $this->MIOLO->getBusiness('gnuteca3', 
                                                          'BusMaterialControl');
        
        $this->busIntegrationClient = $this->MIOLO->getBusiness('gnuteca3', 
                                                        'BusIntegrationClient');
        
        $this->busWorkflowInstance = $this->MIOLO->getBusiness('gnuteca3', 
                                                         'BusWorkflowInstance');
        
        $this->busFile = $this->MIOLO->getBusiness('gnuteca3', 'BusFile');
        
        $this->busPreference = $this->MIOLO->getBusiness('gnuteca3', 'BusPreference');
        parent::__construct();
        
    }
    
    /*
     * Webservice para receber requisições de biblioteca virtual
     *
     * Criado por: Tcharles Silva em 04/2014
     */
    public function recebeRequisicao($hostClient, $nameClient, $initialMaterial, 
            $initialExemplary, $emailClient, $periodicity, $serverWorkflowInstance)
    {
        
        //Instancia dados para criar o registro        
        $this->busIntegrationClient->hostClient = $hostClient;
        $this->busIntegrationClient->nameClient = $nameClient;
        $this->busIntegrationClient->initialAmountClientExemplarys = $initialExemplary;
        $this->busIntegrationClient->initialAmountClientMaterials = $initialMaterial;
        $this->busIntegrationClient->emailClient= $emailClient;
        $this->busIntegrationClient->periodicity = $periodicity;
        $this->busIntegrationClient->serverWorkflowInstanceId = $serverWorkflowInstance;
        
        //Insere o Cliente
        $resp = $this->busIntegrationClient->insertIntegrationClient();
        
        //Obtem o numero recem inserido na tabela gtcIntegrationClient
        $tableId = $resp[0][0];

        //criar o WorkFlow com o id, recebido em resp
        $ok = GWorkflow::instance('INTEGRATION', 'gtcIntegrationClient', $tableId);
        
        //Caso inseriu, irá atualizar o valor de clientWorkflowInstanceId
        if($ok)
        {
            //Obter o workflowInstanceid do Cliente
            $this->busWorkflowInstance->tableId = $tableId;
            $this->busWorkflowInstance->tableName = 'gtcIntegrationClient';
            $workflowInstance = $this->busWorkflowInstance->searchWorkflowInstance(true);
            $arr = $workflowInstance[0];

            //Define o valor para a variável
            $this->busIntegrationClient->clientWorkflowInstanceId = $arr->workflowInstanceId;
            
            //Parametro é o numero identificador recem inserido
            $clientId = $tableId;
            
            //Atualiza o ClienteWorkflowInstanceId recem inserido.
            $this->busIntegrationClient->atualizaClientWorkflowInstanceId($clientId);
            
            //Limpa dados que estiverem sem o server preenchido na gtcClient
            $this->busIntegrationClient->deletaInvalidos();
            
            //Retorna o valor para o server.
            return $this->busIntegrationClient->clientWorkflowInstanceId;
        }
    }
    
    /*
     * Webservice criado para salvar arquivo Zip com informações da biblioteca 
     * participante.
     * O arquivo vem codificado em base64.
     *
     * Criado por: Tcharles Silva em 04/2014
     */
    public function receiptZIpMaterial($zipFile, $biblioName)
    {
        $arqDecode = base64_decode($zipFile);
        
        //Identifica o diretório para salvar o arquivo
        $path = $this->busFile->getAbsoluteFilePath('bases_novas');
        
        //Testa para verificar se tem permissão de escrita
        if(is_writable($path))
        {
            $filename = $path.$biblioName.'.zip';
            $var = file_put_contents( $filename, $arqDecode);
        }
        return true;
    }
    
    /*
     * Webservice que irá sincronizar os workflow de Server e Cliente
     * 
     * $instanceWorkflowServer conterá:
     * [0] - instanceWorkflowId do Server.
     * [1] - instanceWorkflowId do Cliente.
     * [2] - estado atual do instanceWorkflow
     * 
     * $objWorkflow conterá:
     * [0] - serverId
     * [1] - WorkflowInstanceServerId
     * [2] - WorkflowInstanceClientId
     * 
     * O webservice deverá retornar o mesmo objeto, com o seu estado atual
     * 
     * Criado por Tcharles Silva em 04/2014
     */
    public function sincronizaIntegrationWorkflow($dadosWorkflowServer, $objWorkflow)
    {
        $busWorkflowInstance = $this->MIOLO->getBusiness($this->module, 'BusWorkflowInstance');
        $busIntegrationClient = $this->MIOLO->getBusiness($this->module, 'BusIntegrationClient');
        
        //Monta objeto para pesquisar
        $busWorkflowInstance->workflowInstanceId = $objWorkflow[2];
        
        $dadosWorflowClient = $busWorkflowInstance->searchWorkflowInstance(TRUE);
        $dadosWorflowClient = $dadosWorflowClient[0];
        
        //Verifica se estão no mesmo estado
        if($dadosWorflowClient->workflowStatusId !== $dadosWorkflowServer->workflowStatusId)
        {
            //Caso não estejam, repara o workflowInstance e devolve o mesmo
            switch($dadosWorkflowServer->workflowStatusId)
            {
                //Caso esteja com o Status de Cancelada
                case '200002':
                    //monta variável de id
                    $WCid = $dadosWorflowClient->workflowInstanceId;
                    //monta variável de tableName
                    $WCtName = $dadosWorflowClient->tableName;
                    //monta variável de tableId
                    $WCrId = $dadosWorflowClient->tableId;
                    //monta variável de novo status
                    $WCfStatus = '200002';
                    //monta variável de comentário
                    $WCc = 'Biblioteca participante cancelou.';
                    GWorkflow::changeStatus($WCid, $WCtName, $WCrId, $WCfStatus, $WCc);
                    
                    //Atualiza variável $dadosWorkflowClient
                    $dadosWorflowClient->workflowStatusId = '200002';
                    
                    break;
                
                //Caso esteja com o Status de Finalizada pelo Participante
                case '200006':
                    //monta variável de id
                    $WCid = $dadosWorflowClient->workflowInstanceId;
                    //monta variável de tableName
                    $WCtName = $dadosWorflowClient->tableName;
                    //monta variável de tableId
                    $WCrId = $dadosWorflowClient->tableId;
                    //monta variável de novo status
                    $WCfStatus = '200006';
                    //monta variável de comentário
                    $WCc = 'Biblioteca participante encerrou a sincronização.';
                    GWorkflow::changeStatus($WCid, $WCtName, $WCrId, $WCfStatus, $WCc);
                    
                    //Atualiza variável $dadosWorkflowClient
                    $dadosWorflowClient->workflowStatusId = '200006';
                    
                    break;
            }
        }
        //Retorna o objeto, para ser validado pela biblioteca participante
        return $dadosWorflowClient;
    }
    
    
    /*
     * Posições do array de retorno:
     * [0] - Nome do servidor
     * [1] - E-mail do Servidor
     * [2] - Quantidade de exemplares
     */
    public function infoGnutecaVirtual()
    {
        //Obtem nome do Server
        $serverName = $this->busPreference->getPreference('gnuteca3', 'INTEGRATION_SERVER_NAME', false);
        $obj[] = $serverName;
        
        //Obtem e-mail do admin
        $adminEmail = $this->busPreference->getPreference('gnuteca3', 'EMAIL_ADMIN', false);
        $obj[] = $adminEmail;
        
        //Obtem total de obras
        $totalObras = $this->busMaterialControl->countTotalObras();
        $obj[] = $totalObras[0];
        
        return $obj;
    }
}
?>
