<?php
/**
  *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @since
 * Class created on 19/09/2011
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solucoes Livres \n
 * The Gnuteca3 Development Team
 *
 * \b CopyLeft: \n
 * CopyLeft (L) 2010 SOLIS - Cooperativa de Solucoes Livres \n
 *
 * \b License: \n
 * Licensed under GPL (for further details read the COPYING file or http://www.gnu.org/copyleft/gpl.html
 *
 * \b History: \n
 * See history in SVN repository: http://gnuteca.solis.coop.br
 *
 */

class sincronyzeBiblioVirtual extends GTask
{
    public $MIOLO;
    public $module;

    public function __construct($MIOLO, $myTaskId)
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        parent::__construct($MIOLO, $myTaskId);
    }

    public function execute()
    {
        //Instancia as classes
        $busIntegrationServer = $this->MIOLO->getBusiness($this->module, 'BusIntegrationServer');
        $busIntegrationLibrary = $this->MIOLO->getBusiness($this->module, 'BusIntegrationLibrary');
        $busWorkflowInstance = $this->MIOLO->getBusiness($this->module, 'BusWorkflowInstance');
        $busIntegrationServerLog = $this->MIOLO->getBusiness('gnuteca3', 'BusIntegrationServerLog');
        $busMaterialControl = $this->MIOLO->getBusiness('gnuteca3', 'BusMaterialControl');
        $busExemplaryControl = $this->MIOLO->getBusiness('gnuteca3', 'BusExemplaryControl');
        
        //Monta lista de todas as integrações da biblioteca
        $listOfIntegration = $busIntegrationServer->searchIntegrationServer();
        
        foreach ($listOfIntegration as $list)
        {
            //Verifica Status do Workflow, para dar continuidade à sincronização
            $serverDados = $busIntegrationServer->getIntegrationServer($list[0]);
            
            //Monta objeto para passar no webservice de sincronização de workflow
            $obWorkflow[] = $serverDados->integrationServerId;
            $obWorkflow[] = $serverDados->serverWorkflowInstanceId;
            $obWorkflow[] = $serverDados->clientWorkflowInstanceId;
            
            //Obtem a URL que chamará o webservice
            $urlServer = $list[2];
            
            //Verifica se os dois workflowstatus de server e client estão iguais
            $syncronized = $this->sincronizaWebService($urlServer, $obWorkflow);
            
            $obWorkflow = NULL;
            
            if($syncronized)
            {
                //Verifica se esta com o estado de sincronizando
                
                $busWorkflowInstance->workflowinstanceid = $serverDados->serverWorkflowInstanceId;
                $busWorkflowInstance->tableid = $serverDados->integrationServerId;
                
                $dadosWorflow = $busWorkflowInstance->searchWorkflowInstance(TRUE);
                $myDados = $dadosWorflow[0];
                
                //Se o Status for Sincronizando, realiza a ação de geração e envio de acervo.
                if($myDados->workflowStatusId == '200005')
                {
                    //Obtem o serverId
                    $serverId = $list[0];

                    //Procura as Unidades de biblioteca com serverId correspondente
                    $busIntegrationLibrary->integrationServerId = $serverId;
                    $listaLib = $busIntegrationLibrary->searchIntegrationLibrary(TRUE);

                    $arrLib = array();

                    foreach($listaLib as $lib)
                    {
                        $arrLib[] = $lib->libraryUnitId;
                    }

                    //Variável $unidades conterá todas as Unidades que realizam a sincronização
                    $unidades = implode(',', $arrLib);

                    $biblioName = str_replace(' ', '', GString::remAcento($list[1]));;

                    //Com as unidades, podemos gerar os arquivos correspondentes
                    $arquivoZip = $busIntegrationServer->montaArquivo($unidades, $biblioName);

                    if($arquivoZip)
                    {
                        //Codificar arquivo ZIP para base64
                        $arq = base64_encode($arquivoZip);
                        //Se o arquivo está criado, chama webService para enviar ao destino.
                        $valida = $this->enviaZipWebService($list[2], $arq, $biblioName);
                        
                        //Se validar, cria registro de log
                        if($valida)
                        {
                            $totalObras = $busMaterialControl->countTotalObras();
                            $totalExemplares = $busExemplaryControl->countTotalExemplares();
                            
                            $tExemplares = $totalExemplares[0];
                            $tObras = $totalObras[0];
                            
                            $busIntegrationServerLog->integrationServerid = $serverDados->integrationServerId;
                            $busIntegrationServerLog->materialsSynchronized = $tObras;
                            $busIntegrationServerLog->exemplariesSynchronized = $tExemplares;
                            $busIntegrationServerLog->date = GDate::now()->getDate();
                            
                            $busIntegrationServerLog->insertIntegrationServerLog();
                        }
                    }
                }  
            }
            else
            {
                //throw new Exception("O workflow foi atualizado. Execute a tarefa novamente.");
                GPrompt::alert("O workflow foi atualizado. Execute a tarefa novamente.");
            }
        }
        return true;
    }
    
    public function enviaZipWebService($urlServer, $zip, $biblioName)
    {
        $url = $urlServer;
        $class = "gnuteca3WebServicesIntegrationServer";

        $clientOptions["location"] = "$url/webservices.php?module=gnuteca3&action=main&class=$class";
        $clientOptions["uri"] = "$url";
        $clientOptions["encoding"] = "UTF-8";

        $cliente = new SoapClient(NULL, $clientOptions);
        $retorno = $cliente->receiptZIpMaterial($zip, $biblioName);
        return $retorno;
    }
    
    public function sincronizaWebService($urlServer, $objWorkflow)
    {
        $busWorkflowInstance = $this->MIOLO->getBusiness($this->module, 'BusWorkflowInstance');
        
        //Monta e prepara chamada para webservice
        $url = $urlServer;
        $class = "gnuteca3WebServicesIntegrationServer";

        $clientOptions["location"] = "$url/webservices.php?module=gnuteca3&action=main&class=$class";
        $clientOptions["uri"] = "$url";
        $clientOptions["encoding"] = "UTF-8";
        $cliente = new SoapClient(NULL, $clientOptions);
        
        //Monta objeto para pesquisar
        $busWorkflowInstance->workflowinstanceid = $objWorkflow[1];
        $busWorkflowInstance->tableId = $objWorkflow[0];
        
        //Pesquisa pelos dados do workflow, que será comparado.
        $dadosWorflow = $busWorkflowInstance->searchWorkflowInstance(TRUE);
        $myDados = $dadosWorflow[0];
        
        $retornoClient = $cliente->sincronizaIntegrationWorkflow($myDados, $objWorkflow);
        
        if($myDados->workflowStatusId !== $retornoClient->workflowStatusId)
        {
            //Atualiza dados do workflow
            switch($retornoClient->workflowStatusId)
            {
                //Cancelada
                case '2000002':
                    
                    //monta variável de id
                    $WSid = $myDados->workflowInstanceId;
                    //monta variável de tableName
                    $WStName = $myDados->tableName;
                    //monta variável de tableId
                    $WSrId = $myDados->tableId;
                    //monta variável de novo status
                    $WSfStatus = '200002';
                    //monta variável de comentário
                    $WSc = 'Biblioteca integradora encerrou a participação.';
                    GWorkflow::changeStatus($WSid, $WStName, $WSrId, $WSfStatus, $WSc);
                    
                    break;
                
                //Negada
                case '2000004':
                    
                    //monta variável de id
                    $WSid = $myDados->workflowInstanceId;
                    //monta variável de tableName
                    $WStName = $myDados->tableName;
                    //monta variável de tableId
                    $WSrId = $myDados->tableId;
                    //monta variável de novo status
                    $WSfStatus = '200004';
                    //monta variável de comentário
                    $WSc = 'Biblioteca integradora negou a sua participação.';
                    GWorkflow::changeStatus($WSid, $WStName, $WSrId, $WSfStatus, $WSc);
                    
                    break;
                
                //Sincronizando
                case '2000005':
                    
                    //monta variável de id
                    $WSid = $myDados->workflowInstanceId;
                    //monta variável de tableName
                    $WStName = $myDados->tableName;
                    //monta variável de tableId
                    $WSrId = $myDados->tableId;
                    //monta variável de novo status
                    $WSfStatus = '200005';
                    //monta variável de comentário
                    $WSc = 'Biblioteca integradora habilita sincronização dos dados.';
                    GWorkflow::changeStatus($WSid, $WStName, $WSrId, $WSfStatus, $WSc);
                    
                    break;
                
                //Finalizada pela integradora
                case '2000007':
                    
                    //monta variável de id
                    $WSid = $myDados->workflowInstanceId;
                    //monta variável de tableName
                    $WStName = $myDados->tableName;
                    //monta variável de tableId
                    $WSrId = $myDados->tableId;
                    //monta variável de novo status
                    $WSfStatus = '200007';
                    //monta variável de comentário
                    $WSc = 'Biblioteca integradora encerrou a participação.';
                    GWorkflow::changeStatus($WSid, $WStName, $WSrId, $WSfStatus, $WSc);
                    
                    break;
            }
            //Retorna falso, pois nesse momento apenas sincroniza os dados...
            return false;
        }
        //Apenas retorna true, quando os dados do workflow estiverem sincronizados
        return true;
    }
}
?>