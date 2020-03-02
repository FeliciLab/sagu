<?php
/**
 * <--- Copyright 2005-2013 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 *
 * Este arquivo é parte do programa Gnuteca.
 *
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
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
 * Classe WebService para GnutecaAutomação
 *
 * @author Tcharles Silva [tcharles@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Tcharles Silva [tcharles@solis.coop.br]
 * 
 * 
 *
 * @since
 * Class created on 04/11/2013
 * 
 **/

include("GnutecaWebServices.class.php");
$MIOLO->getClass('gnuteca3', 'GSession');
$MIOLO->getClass('gnuteca3', 'GSipLog');
$MIOLO->getClass('gnuteca3', 'GSipCirculation');

class gnuteca3WebServicesAutomacao extends GWebServices 
{
    public $busSipEquipament;
    public $busSipEquipamentStatusHistory;
    public $busSipEquipamentBinRules;
    public $busSessionOperation;
    public $busSession;
    public $busOperationLoanSip;
    public $busAuthenticate;
    public $busPerson;
    public $busMaterial;
    public $busExemplaryControl;
    public $busMaterialControl;
    public $busSearchFormat;
    public $busOperationLoan;
    public $busOperationRenew;
    public $busReserve;
    public $busRenew;
    public $busReserveComposition;
    public $busLoan;
    public $busReturnRegister;
    public $busReturnType;
    public $busFine;
    public $busPolicy;
    public $busBond;
    public $busRight;
    public $busPenalty;
    public $busLocationForMaterialMovement;
    public $busPersonConfig;
    public $busLibPerson;
    
    
    public function __construct() {
        parent::__construct();
        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        //Instancia os atributos com o objeto Bus$Parametro respectivamente
        $this->busSipEquipament = $MIOLO->getBusiness($module, 'BusSipEquipament');
        $this->busSipEquipamentStatusHistory = $MIOLO->getBusiness($module, 'BusSipEquipamentStatusHistory');
        $this->busSipCirculationStatus = $MIOLO->getBusiness($module, 'BusSipCirculationStatus');
        $this->busExemplaryStatusHistory = $MIOLO->getBusiness($module, 'BusExemplaryStatusHistory');
        $this->busExemplaryControl = $MIOLO->getBusiness($module, 'BusExemplaryControl');
        $this->busMaterial = $MIOLO->getBusiness($module, 'BusMaterial');
        $this->busMaterialControl = $MIOLO->getBusiness($module, 'BusMaterialControl');
        $this->busSearchFormat = $MIOLO->getBusiness($module, 'BusSearchFormat');
        $this->busSessionOperation = $MIOLO->getBusiness($module, 'BusSessionOperation');
        $this->busSession = $MIOLO->getBusiness($module, 'BusSession');
        $this->busOperationLoan = $MIOLO->getBusiness($module, 'BusOperationLoan');
        $this->busOperationLoanSip = $MIOLO->getBusiness($module, 'BusOperationLoanSip');
        $this->busOperationRenewSip = $MIOLO->getBusiness($module, 'BusOperationRenewSip');
        $this->busAuthenticate = $MIOLO->getBusiness($module, 'BusAuthenticate');
        $this->busPerson = $MIOLO->getBusiness($module, 'BusPerson');
        $this->busLoan = $MIOLO->getBusiness($module, 'BusLoan');
        $this->busRenew = $MIOLO->getBusiness($module, 'BusRenew');
        $this->busReturnRegister = $MIOLO->getBusiness($module, 'BusReturnRegister');
        $this->busReturnType = $MIOLO->getBusiness($module, 'BusReturnType');
        $this->busFine = $MIOLO->getBusiness($module, 'BusFine');
        $this->busReserve = $MIOLO->getBusiness($module, 'BusReserve');
        $this->busReserveComposition = $MIOLO->getBusiness($module, 'BusReserveComposition');
        $this->busPolicy = $MIOLO->getBusiness($module, 'BusPolicy');
        $this->busBond = $MIOLO->getBusiness($module, 'BusBond');
        $this->busRight = $MIOLO->getBusiness($module, 'BusRight');
        $this->busPenalty = $MIOLO->getBusiness($module, 'BusPenalty');
        $this->busLocationForMaterialMovement = $MIOLO->getBusiness($module, 'BusLocationForMaterialMovement');
        $this->busPersonConfig = $MIOLO->getBusiness($module, 'BusPersonConfig');
        $this->busLibPerson = $MIOLO->getBusiness($module, 'BusLibPerson');
        $this->busSipEquipamentBinRules = $MIOLO->getBusiness($module, 'BusSipEquipamentBinRules');
    }


    /*
     * Esqueleto do LOGIN, retorna booleano caso dados sejam válidos
     * Parametros:
     * 
     * String termID :: id do terminal
     * String pwd :: senha do terminal
     * String location :: localização, campo opcional
     * 
     * Descrição:
     *      Utiliza o método authenticate da BusSipEquipament para se authentificar
     *      Retorno: booleano se conseguiu authentificar
     * 
     * Criado por: Tcharles Silva
     * Em: 21/11/2013
     * Ultima Atualização por: 
     * Em: 
     * Motivo: 
     */
    public function login($termID, $pwd, $location = null) 
    {
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[LOGIN] -- START WEBSERVICE");
            GSipLog::insertSipLog("[LOGIN] -- START PARAMETROS");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("termID : $termID");
            GSipLog::insertSipLog("pwd : $pwd");
            GSipLog::insertSipLog("location : $location");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("[LOGIN] -- END PARAMETROS");
        }
        
        $ok = 'N';
        $startTime = microtime(true);
        
        $retorno = $this->busSipEquipament->authenticate($termID, $pwd);
        
        if($retorno)
        {
            $ok = 'Y';
        }
        
        $endTime = microtime(true);
        $time = $endTime - $startTime;
        
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[LOGIN] -- RESPOSTA : [" . $ok . "]");
            GSipLog::insertSipLog("[LOGIN] -- TEMPO DE RESPOSTA: $time");
            GSipLog::insertSipLog("[LOGIN] -- END WEBSERVICE");
            GSipLog::insertSipLog("...");
        }
        
        return $ok;
    }
    
    
    /*
     * Esqueleto do STATUS, retorna uma String
     * Parametros:
     * 
     * String termID :: id do terminal
     * Integer status :: Pode ser: [0]: OK, [1]: SEM PAPEL, [2]: DESLIGANDO
     * Integer maxPtrWidth :: Tamanho de largura? ----> Ainda não implementado <<----
     * String protocol :: Opcional, não utilizamos
     * 
     * Descrição: WebService de STATUS irá retornar os dados do equipamento
     * Também irá gravar um registro do equipamento que o acessou, com o status atual do mesmo.
     * Registro pode ser visto na tabela gtcSipEquipamentLog
     * 
     * Criado por: Tcharles Silva
     * Em: 21/11/2013
     * Ultima Atualização por: 
     * Em: 
     * Motivo:
     */
    public function status($termID, $status, $maxPtrWidth, $protocol) 
    {
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[STATUS] -- START WEBSERVICE");
            GSipLog::insertSipLog("[STATUS] -- START PARAMETROS");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("termID : $termID");
            GSipLog::insertSipLog("status : $status");
            GSipLog::insertSipLog("maxPtrWidth : $maxPtrWidth");
            GSipLog::insertSipLog("protocol : $protocol");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("[STATUS] -- END PARAMETROS");
        }
        
        $startTime = microtime(true);
        /* OBS:
         * Ainda não foi implementado a utilização do $maxPtrWidth.
         * Futuramente deverá ser implementado.
         */
        
        if(($status >= 0 ) && ($status < 3))
        {
            //Chama o mpetodo checkStatus para verificar o Status
            $retorno = $this->busSipEquipament->checkStatus($termID);

            //Verifica se esta em alguma biblioteca, caso nulo, não encontrou o $termID
            if($retorno['libraryName'])
            {
                //Seta os dados para inserir
                $this->busSipEquipamentStatusHistory->sipEquipamentId = $termID;
                $this->busSipEquipamentStatusHistory->status = $status;
                $this->busSipEquipamentStatusHistory->insertSipEquipamentStatusHistory();

                //Verifica se a ScreenMsg esta vazia
                if(is_null($retorno['screenMsg']))
                {
                    $retorno['screenMsg'] = 'Status retornado com sucesso.';
                }
                
                //Verifica se a printMsg esta vazia
                if(is_null($retorno['printMsg']))
                {
                    $retorno['printMsg'] = 'null';
                }
                
                //Pepara o retorno na variável $backInfo, usando o delimitador SIP_DELIMITER
                $backInfo = implode(SIP_DELIMITER, $retorno);
            }
            else
            {
                $msg = "Não foi encontrado informações deste terminal.";
                $invalido = true;
            }
        }else
        {
            $msg = "Atenção! Esse status não é válido.";
            $invalido = true;
        }
        
        if($invalido)
        {
            //Checagem para ecvitar campos em branco
            if(empty($retorno['timeoutPeriod']))
            {
                $retorno['timeoutPeriod'] = '999';
            }
            if(empty($retorno['retriesAllowed']))
            {
                $retorno['retriesAllowed'] = '999';
            }
            if(empty($retorno['institutionId']))
            {
                $retorno['institutionId'] = ' ';
            }
            if(empty($retorno['libraryName']))
            {
                $retorno['libraryName'] = 'null';
            }
            if(empty($retorno['terminalLocation']))
            {
                $retorno['terminalLocation'] = 'null';
            }
            
            if(is_null($retorno['screenMsg']))
            {
                if(is_null($msg))
                {
                    $retorno['screenMsg'] = 'null';
                }
                else
                {
                    $retorno['screenMsg'] = $msg;
                }
            }
            
            if(is_null($retorno['printMsg']))
            {
                $retorno['printMsg'] = 'null';
            }
            
            $backInfo = implode(SIP_DELIMITER, $retorno);
        }
        
        /*
         * Validação da mensagem de retorno.
         * Cada campo não pode ter mais do que 258 caracteres
         * Implementado por: Tcharles S.
         * Em : 10/07/2014
         */
        $validacao = explode("||", $backInfo);
        
        foreach($validacao as $valid)
        {
            $validos[] = GSipCirculation::setSipSize($valid);
        }
        
        $backInfo = implode(SIP_DELIMITER, $validos);
        
        $endTime = microtime(true);
        $time = $endTime - $startTime;
        
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[STATUS] -- RESPOSTA : [" . $backInfo . "]");
            GSipLog::insertSipLog("[STATUS] -- TEMPO DE RESPOSTA: $time");
            GSipLog::insertSipLog("[STATUS] -- END WEBSERVICE");
            GSipLog::insertSipLog("...");
        }
        
        return $backInfo;
    }
    
    
    /*
     * Esqueleto endPatronSession, retorna uma String
     * Parametros:
     * 
     * String dateTime :: Hora atual
     * String instID :: Id da instituição (LibraryUnitId)
     * String patronID :: Id do cartão e/ou personId
     * String termID :: Id do terminal (sipEquipamentId)
     * String termPwd :: Senha do terminal
     * String patronPwd :: Senha do usuário
     * 
     * Descrição:
     *      Webservice para encerrar sessão de usuário.
     * 
     *      [IMPORTANTE] Este webservice NÃO É UTILIZADO PELOS EQUIPAMENTOS DA BIBLIOTHECA.
     * 
     * Criado por: Tcharles Silva
     * Em: 12/2013
     */
    public function endPatronSession($dateTime, $instID, $patronID, $termID, $termPwd, $patronPwd)
    {   
        
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[ENDPATRONSESSION] -- START WEBSERVICE");
            GSipLog::insertSipLog("[ENDPATRONSESSION] -- START PARAMETROS");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("dateTime : $dateTime");
            GSipLog::insertSipLog("instID : $instID");
            GSipLog::insertSipLog("patronID : $patronID");
            GSipLog::insertSipLog("termID : $termID");
            GSipLog::insertSipLog("termPwd : $termPwd");
            GSipLog::insertSipLog("patronPwd : ". md5($patronPwd));
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("[ENDPATRONSESSION] -- END PARAMETROS");
        }
        
                
        /* Para obter a identificação da pessoa, primeiramente procura pelo personId
         * Caso não encontre o personId, irá validar pelo login */
        $personInf = $this->busPerson->getPerson($patronID, TRUE);

        if($personInf)
        {
            $personId = $personInf->personId;
        }
        else
        {
            /* Alteração para funcionalidade de uso de cartão - Univates 06/2014
             * 
             * O personId aqui, é o código do cartão.
             * O código do cartão na basPerson é o campo login.
             * Deve-se procurar pela pessoa cujo login seja personId (código do cartão).
             * Após obter a pessoa, obter o personId da mesma, e dar continuidade no algoritmo
             */
        
            $idPessoa = $this->busPerson->getPersonIdByLogin($patronID);

            //Agora sim, personId tem o valor do código correto
            $personId = $idPessoa[0][0];
        }
        
        $startTime = microtime(true);
        
        $dadosSip = $this->busSipEquipament->getSipEquipament($termID);
        $locSip = $dadosSip->locationformaterialmovementid;
        
        //Arrays onde ficarão os IDs de empréstimo, devolução e renovação de cada sessão
        $loanIds = Array();
        $returnIds = Array();
        $renewIds = Array();
        
        $loanIds2 = Array();
        $returnIds2 = Array();
        $renewIds2 = Array();
        
        $salva = Gsession::getOpenSession($termID);        
        
        if($salva)
        {
            $this->busSessionOperation->sessionId = $salva->sessionId;
            $this->busSessionOperation->isClosed = DB_FALSE;
            $save = $this->busSessionOperation->searchSessionOperation(TRUE);
                    
            foreach ($save as $op)
            {
                $loanIds2[] = $op->loanId;
                $returnIds2[] = $op->returnRegisterId;
                $renewIds2[] = $op->renewId;

                if($op->operation == 1)  //Verifica se foi Empréstimo
                {
                    $loanIds[] = $op->loanId;
                }
                if($op->operation == 2) //Verifica se foi Devolução
                { 
                    $returnIds[] = $op->returnRegisterId;
                }
                if($op->operation == 3) //Verifica se é Renovação
                {
                    $renewIds[] = $op->renewId;
                }
            }
        }
        
        /*
         * Os numeros dos LoanIds, returnIds e renewIds estão salvos abaixo.
         * 
         * Em cima deles que serão gerados os recibos...
         * 
         * Necessário implementar os recibos.
         */
        
        //Instancia o recibo
        $receiptObject = new GnutecaReceipt();
        $defineReceiptConfigurations = false;
        
        //Define configurações da pessoa
        $pLoanRecibo = $this->busPersonConfig->getValuePersonConfig($personId, "MARK_PRINT_RECEIPT_LOAN");
        $pLoanEmail = $this->busPersonConfig->getValuePersonConfig($personId, "MARK_SEND_LOAN_MAIL_RECEIPT");
        $pReturnRecibo = $this->busPersonConfig->getValuePersonConfig($personId, "MARK_PRINT_RECEIPT_RETURN");
        $pReturnEmail = $this->busPersonConfig->getValuePersonConfig($personId, "MARK_SEND_RETURN_MAIL_RECEIPT");
        
        //Inicio do recibo de empréstimo
        foreach($loanIds as $loan)
        {
            $data = $this->busLoan->getLoan($loan, TRUE);
            $data->policy = $this->busPolicy->getPolicy($data->privilegeGroupId, $data->linkId, "1", TRUE);
            
            if ( ! $defineReceiptConfigurations )
            {
                //Faz a verfifição do local
                $location  = $this->busLocationForMaterialMovement->getLocationForMaterialMovement($locSip, TRUE);
                
                //Testa se no local, há configuração para imprimir recibo
                if( $location->sendLoanReceiptByEmail == 't')
                {
                    $sendReceipt = true;
                    
                    if($pLoanRecibo == 't')
                    {
                        $printReceipt = true;
                    }else
                    {
                        $printReceipt = false;
                    }
                } 
                else
                {
                    // Caso não tenha, verifica as configurações do usuário
                    //Verificação de recibo
                    if($pLoanRecibo == 't'){
                        $sendReceipt = true;
                    }else{
                        $sendReceipt = false;
                    }
                    
                    //Verificação de e-mail
                    if($pLoanEmail == 't')
                    {
                        $printReceipt = true;
                    }else
                    {
                        $printReceipt = false;
                    }
                }
                $defineReceiptConfigurations =  true;
            }
            //Adiciona o item
            $receiptObject->addItem(new LoanReceipt($data, $sendReceipt, $printReceipt));
        }
        
        //Inicio do recibo de devolução
        foreach($returnIds as $return)
        {
            //Definir o data
            $data = $this->busReturnRegister->getReturnRegister($return, TRUE);
            
            //Informações do exemplar
            $dados = $this->busExemplaryControl->getExemplaryControl($data->itemNumber);
            
            //Informações do Loan
            $this->busLoan->personId = $personId;
            $this->busLoan->itemNumber = $data->itemNumber;
            $this->busLoan->loanoperator = $data->operator;
            $this->busLoan->returnDate = $data->date;
            
            $dadosLoan = $this->busLoan->searchLoan(TRUE);
            
            foreach($dadosLoan as $d)
            {
                if($d->returnDate == $data->date)
                {
                    //Informações do loan na variavel $d
                    $dadosR = $d;
                    
                    $dadosR->data = $dadosR->LoanDate;
                    
                    //campo privilegeGroupId
                    $priv = $this->busLoan->getLoan($d->loanId, True);
                    $dadosR->privilegeGroupId = $priv->privilegeGroupId;
                    
                    //campo personName
                    $this->busPerson->personIdS = $personId;
                    $var = $this->busPerson->searchPerson(TRUE);
                    $dadosR->personName = $var[0]->personName;
                    
                    //campo email
                    $dadosR->email = $var[0]->email;
                }
            }
            
            //Coloca Informações do Loan no local
            $dados->loan = $dadosR;
            
            //campo searchData
            $dados->searchData = $searchData = $this->busSearchFormat->getFormatedString($dados->controlNumber, MATERIAL_MOVIMENT_SEARCH_FORMAT_ID, 'search');
            
            //campo operator
            $dados->operator = $data->operator;
            
            //campo returnDate
            $dados->returnDate = $data->date;
            
            //campo loanId
            $dados->loanId = $dadosR->loanId;
            
            //campo loanDate
            $dados->loanDate = $dadosR->LoanDate;
            
            //campo personId
            $dados->personId = $dadosR->personId;
            
            //Objeto Fine
            $this->busFine->loanId = $dadosR->loanId;
            $this->busFine->itemNumber = $data->itemNumber;
            $this->busFine->personId = $dadosR->personId;
            $this->busFine->libraryUnitId = $dadosR->libraryUnitId;
            $dadosF = $this->busFine->searchFine(TRUE);
            
            if($dadosF)
            {
                foreach($dadosF as $df)
                {
                    if($df->loanId == $dadosR->loanId)
                    {
                        //Define o valor de multa paga
                        $fine->value = $df->value;
                        
                        //Define lonaId
                        $fine->loanId = $df->loanId;
                        
                        //Define BeginDate
                        $fine->beginDate = $df->beginDate;
                        
                        //Define FineStatusId
                        $reF = $this->busFine->getFine($df->fineId, TRUE);
                        $fine->fineStatusId = $reF->fineStatusId;
                        
                        //Define FineId
                        $fine->fineId = $df->fineId;

                        //se tiver multa, irá habilitar no objeto
                        $haveM = true;
                    }
                }
            }
            //Se tiver multas
            if($haveM)
            {
                $dados->fine = $fine;
                $dados->date = $data->date;
                $dados->fineValue = $fine->value;
            }else
            {
                //campo date
                $dados->date = $data->date;

                //campo fineValue
                $dados->fineValue = '';
            }
            
            
            if ( ! $defineReceiptConfigurations )
            {
                //Faz a verfifição do local
                $location  = $this->busLocationForMaterialMovement->getLocationForMaterialMovement($locSip, TRUE);
                
                //Testa se no local, há configuração para imprimir recibo
                if( $location->sendReturnReceiptByEmail == 't')
                {
                    $sendReceipt = true;
                    
                    
                    if($pReturnRecibo == 't')
                    {
                        $printReceipt = true;
                    }else
                    {
                        $printReceipt = false;
                    }
                    
                } 
                else
                {
                    //Verificar o Tipo de Devolução
                    $rType = $this->busReturnType->getReturnType($data->returnTypeId);
                    if($rType->sendmailreturnreceipt == 't')
                    {
                        $sendReceipt = true;
                        $printReceipt = true;
                    
                    }else
                    {
                        // Caso não tenha, verifica as configurações do usuário
                        //Verificação de recibo
                        if($pReturnRecibo == 't'){
                            $sendReceipt = true;
                        }else{
                            $sendReceipt = false;
                        }

                        //Verificação de e-mail
                        if($pReturnEmail == 't')
                        {
                            $printReceipt = true;
                        }else
                        {
                            $printReceipt = false;
                        }
                    }
                }
                $defineReceiptConfigurations =  true;
            }
            //Adiciona o item
            $receiptObject->addItem(new ReturnReceipt($dados, $sendReceipt, $printReceipt));
        }
        
        //Inicio do recibo de renovação
        foreach($renewIds as $renew)
        {
            $dadosR = $this->busRenew->getRenew($renew);
            $data = $this->busLoan->getLoan($dadosR->loanId, TRUE);
            $data->policy = $this->busPolicy->getPolicy($data->privilegeGroupId, $data->linkId, "1", TRUE);

            if ( ! $defineReceiptConfigurations )
            {
                //Faz a verfifição do local
                $location  = $this->busLocationForMaterialMovement->getLocationForMaterialMovement($locSip, TRUE);
                
                //Testa se no local, há configuração para imprimir recibo
                if( $location->sendLoanReceiptByEmail == 't')
                {
                    $sendReceipt = true;
                    
                    if($pLoanRecibo == 't')
                    {
                        $printReceipt = true;
                    }else
                    {
                        $printReceipt = false;
                    }
                } 
                else
                {
                    // Caso não tenha, verifica as configurações do usuário

                    //Verificação de recibo
                    if($pLoanRecibo == 't'){
                        $sendReceipt = true;
                    }else{
                        $sendReceipt = false;
                    }
                    
                    //Verificação de e-mail
                    if($pLoanEmail == 't')
                    {
                        $printReceipt = true;
                    }else
                    {
                        $printReceipt = false;
                    }
                }
                $defineReceiptConfigurations =  true;
            }
            //Adiciona o item
            $receiptObject->addItem($r = new LoanReceipt($data, $sendReceipt, $printReceipt));
        }
        
        $receipt = $receiptObject->generate();
        $receipt = str_replace("\r\n", '#', $receipt);
        
        $var = GSession::closeSession($termID);
        $data = GDate::getYYYYMMDDHHMMSS($dateTime);
        
        if($var)
        {
            $retorno['endSession'] = 'Y';
            $msg = "Sessão encerrada.";
            
            $print = $receipt;
            
        }else
        {
            $retorno['endSession'] = 'N';
            $msg = "Não foi possível finalizar a sessão.";
        }
        
        if(empty($print))
        {
            $print = 'null';
        }
        
        $retorno['dateTime'] = $data;
        $retorno['instID'] = $instID;
        $retorno['screenMsg'] = $msg;
        $retorno['printMsg'] = $print;
        
        $backInfo = implode(SIP_DELIMITER, $retorno);
        
        /*
         * Validação da mensagem de retorno.
         * Cada campo não pode ter mais do que 258 caracteres
         * Implementado por: Tcharles S.
         * Em : 10/07/2014
         */
        $validacao = explode("||", $backInfo);
        
        foreach($validacao as $valid)
        {
            $validos[] = GSipCirculation::setSipSize($valid);
        }
        
        $backInfo = implode(SIP_DELIMITER, $validos);
        
        $endTime = microtime(true);
        $time = $endTime - $startTime;
        
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[ENDPATRONSESSION] -- RESPOSTA : [" . $backInfo . "]");
            GSipLog::insertSipLog("[ENDPATRONSESSION] -- TEMPO DE RESPOSTA: $time");
            GSipLog::insertSipLog("[ENDPATRONSESSION] -- END WEBSERVICE");
            GSipLog::insertSipLog("...");
        }
        
        return $backInfo;
    }

    
    
    /*
     * Esqueleto CHECKOUT ( Empréstimo ), retorna uma String
     * Parametros:
     * 
     * Boolean renewalPolicy, 
     * Boolean noBlock,
     * String dateTime, 
     * String nbDueDate, 
     * String instID, 
     * String patronID, 
     * String itemID,
     * String termID,
     * String termPwd,
     * String itemProp, 
     * String patronPwd,  
     * Boolean cancel
     * 
     * Descrição:
     *      Método que irá realizar o empréstimo.
     * 
     * Criado por: Tcharles Silva
     * Em: 27/11/2013
     * Ultima Atualização por: 
     * Em: 
     * Motivo: 
     */
    public function checkout($renewalPolicy, $noBlock, $dateTime, $nbDueDate, $instID, $patronID, 
                              $itemID, $termID, $termPwd, $itemProp, $patronPwd, $cancel) 
    {
        
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[CHECKOUT] -- START WEBSERVICE");
            GSipLog::insertSipLog("[CHECKOUT] -- START PARAMETROS");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("renewalPolicy : $renewalPolicy");
            GSipLog::insertSipLog("noBlock : $noBlock ");
            GSipLog::insertSipLog("dateTime : $dateTime");
            GSipLog::insertSipLog("nbDueDate : $nbDueDate");
            GSipLog::insertSipLog("instID : $instID");
            GSipLog::insertSipLog("patronID : $patronID");
            GSipLog::insertSipLog("itemID : $itemID");
            GSipLog::insertSipLog("termID : $termID");
            GSipLog::insertSipLog("termPwd : $termPwd");
            GSipLog::insertSipLog("itemProp : $itemProp");
            GSipLog::insertSipLog("patronPwd : ". md5($patronPwd));
            GSipLog::insertSipLog("cancel : $cancel");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("[CHECKOUT] -- END PARAMETROS");
        }
        
        //Inicia cronômetro
        $startTime = microtime(true);
        
        //Verifica se o material existe
        $materialExist = $this->busExemplaryControl->getExemplaryControl($itemID);
        
        //Se o material existir, vai adiante
        if($materialExist)
        {
            /* Para obter a identificação da pessoa, primeiramente procura pelo personId
             * Caso não encontre o personId, irá validar pelo login */
            $personInf = $this->busPerson->getPerson($patronID, TRUE);

            if($personInf)
            {
                $personId = $personInf->personId;
            }
            else
            {
                /* Alteração para funcionalidade de uso de cartão - Univates 06/2014
                 * 
                 * O personId aqui, é o código do cartão.
                 * O código do cartão na basPerson é o campo login.
                 * Deve-se procurar pela pessoa cujo login seja personId (código do cartão).
                 * Após obter a pessoa, obter o personId da mesma, e dar continuidade no algoritmo
                 */

                $idPessoa = $this->busPerson->getPersonIdByLogin($patronID);

                //Agora sim, personId tem o valor do código correto
                $personId = $idPessoa[0][0];
            }
            
            //Verifica se o usuario já não está realizando empréstimo
            $libPerson = $this->busLibPerson->getLibPerson($personId);
            
            //Se não tiver, continua o algoritmo
            if(!$this->busPerson->isOperationProcess($personId))
            {
                //Faz renovacao
                $fazRen = $renewalPolicy;

                $titulo = $this->busMaterial->getMaterialTitleByItemNumber($itemID);
                if(empty($titulo))
                {
                    $titulo = 'Sem titulo.';
                }

                //Define padrão DefaultSip de empréstimo
                if ( $noBlock == '1' )
                {
                    $offline = TRUE;
                    $loanTypeId = ID_RENEWTYPE_OFFLINE;
                }
                else
                {
                    $offline = FALSE;
                    $loanTypeId = ID_LOANTYPE_DEFAULTSIPEQUIPAMENT;
                }
                

                /* PADRÕES DE EMPRÉSTIMOS
                   Utilizado em: $padraoLoan 
                     1 | Empréstimo
                     2 | Devolução
                     3 | Renovação
                */
                $padraoLoan = '1';
                $retorno = GSipCirculation::doLoan($padraoLoan, $termID, $personId, $itemID, $loanTypeId, NULL, $fazRen, $offline);

                if(empty($itemProp))
                {
                    $itemProp = 'null';
                }
                
            }
            else
            {
                $retorno[screenMsg] = "Tente novamente mais tarde.";
            }
        }
        
        if($retorno[ok] == 'Y')
        {
            //Decorrer do código aqui.... Caso tenha concluido a operação
            $ret["ok"] = 'Y';
            $ret["renewalOk"] = $retorno[renewalOk];
            $ret["desensitize"] = "Y";
            $ret["dateTime"] = $retorno[dateTime];
            $ret["instID"] = $retorno[instID];
            
            //Retornando o código do cartão digitado ao invés do número de identificação da pessoa
            //$ret["patronID"] = $retorno[patronID];
            $ret["patronID"] = $patronID;
            
            $ret["itemID"] = $retorno[itemID];
            
            if(empty($retorno[titleID]))
            {
                $retorno[titleID] = ' ';
            }
            
            $ret["titleID"] = $titulo;
            $ret["dueDate"] = $retorno[dueDate];
            $ret["securityInhibit"] = 'N';
            $ret["itemProp"] = $itemProp;

            if(is_null($retorno[screenMsg]))
            {
                if($retorno[renewalOk] == 'N')
                {
                    $ret["screenMsg"] = "Sucesso! Empréstimo concluído.";
                }
                else
                {
                    $ret["screenMsg"] = "Sucesso! Renovação concluída.";
                }
            }else
            {
                $ret['screenMsg'] = $retorno[screenMsg];
            }
            
            $ret["printMsg"] = "null";
        }
        else //CASO OCORRA A FALHA
        {   
            $ret["ok"] = 'N';
            $ret["renewalOk"] = "N";
            $ret["desensitize"] = "N";
            
            //Colocar a data no formato abaixo
            $v4 = GDate::now()->getDate(GDate::MASK_DATE_STRING);
            
            $ret["dateTime"] = $v4;
            $ret["instID"] = $instID;
            $ret["patronID"] = $patronID;
            $ret["itemID"] = $itemID;
            
            if(empty($retorno[titleID]))
            {
                $retorno[titleID] = 'Sem titulo.';
            }
            
            $ret["titleID"] = $retorno[titleID];
            $ret["dueDate"] = $v4;
            $ret["securityInhibit"] = 'N';
            $ret["itemProp"] = $itemProp;
            
            if(is_null($retorno[screenMsg]))
            {
                if(is_null($materialExist))
                {
                    $ret["screenMsg"] = "Atenção! Esse material não existe!";
                }
                else
                {
                    $ret["screenMsg"] = "Ocorreu um problema no empréstimo. Consulte informações no balcão de atendimento.";
                }
                
            }else
            {
                $ret['screenMsg'] = $retorno[screenMsg];
            }
            
            $ret["printMsg"] = "null";
        }
        
        //Monta a string de retorno com as informações        
        $backInfo = implode(SIP_DELIMITER, $ret);
        
        /*
         * Validação da mensagem de retorno.
         * Cada campo não pode ter mais do que 258 caracteres
         * Implementado por: Tcharles S.
         * Em : 10/07/2014
         */
        $validacao = explode("||", $backInfo);
        
        foreach($validacao as $valid)
        {
            $validos[] = GSipCirculation::setSipSize($valid);
        }
        
        $backInfo = implode(SIP_DELIMITER, $validos);
        
        $endTime = microtime(true);
        $time = $endTime - $startTime;
        
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[CHECKOUT] -- RESPOSTA : [" . $backInfo . "]");
            GSipLog::insertSipLog("[CHECKOUT] -- TEMPO DE RESPOSTA: $time");
            GSipLog::insertSipLog("[CHECKOUT] -- END WEBSERVICE");
            GSipLog::insertSipLog("...");
        }
        
        return $backInfo;
    }

    
    
    /*
     * Esqueleto CHECKIN, retorna uma String
     * Parametros:
     * 
     * Boolean noBlock,
     * String dateTime, 
     * String returnDate, 
     * String currentLocation, 
     * String instID, 
     * String itemID, 
     * String termID, 
     * String termPwd,
     * String itemProp, 
     * Boolean cancel
     * 
     * Descrição:
     *      Método que realiza a devolução.
     *      
     *      [IMPORTANTE}
     *          Regra definida pela Univates:
     *              O equipamento deve obter toda e qualquer devolução de material.
     *              Ou seja, mesmo que a operação não foi concluída, o equipamento
     *          não irá devolver o item.
     * 
     * Criado por: Tcharles Silva
     * Em: 04/12/2013
     * Ultima Atualização por:  Tcharles Silva
     * Em: 04/12/2013
     * Motivo: 
     *      Correções.
     */
    public function checkin($noBlock, $dateTime, $returnDate, $currentLocation, $instID, 
                             $itemID, $termID, $termPwd, $itemProp, $cancel)
    {
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[CHECKIN] -- START WEBSERVICE");
            GSipLog::insertSipLog("[CHECKIN] -- START PARAMETROS");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("noBlock: $noBlock");
            GSipLog::insertSipLog("dateTime: $dateTime");
            GSipLog::insertSipLog("returnDate: $returnDate");
            GSipLog::insertSipLog("currentLocation: $currentLocation");
            GSipLog::insertSipLog("instID: $instID");
            GSipLog::insertSipLog("itemID: $itemID");
            GSipLog::insertSipLog("termID: $termID");
            GSipLog::insertSipLog("termPwd: $termPwd");
            GSipLog::insertSipLog("itemProp: $itemProp");
            GSipLog::insertSipLog("cancel: $cancel");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("[CHECKIN] -- END PARAMETROS");
        }
        
        //Inicia cronômetro
        $startTime = microtime(true);
        
        // Testa se é devolução offline.
        if ( $noBlock == '1' )
        {
            $offline = TRUE;
        }
        else
        {
           $offline = FALSE; 
        }

        //Chama metodo doReturn com o sipId, e o ItemNumber do livro.
        $retorno = GSipCirculation::doReturn($termID, $itemID, $offline);
        
        
        if(empty($itemProp))
        {
            $itemProp = 'null';
        }
        
        if($retorno[ok] == '1')
        {
            $ret['ok'] = 'Y';
            $ret['resensitize'] = $retorno[resensitize];
            $ret['alert'] = $retorno[alert];
            $ret['dateTime'] = $retorno[dateTime];
            $ret['instID'] = $retorno[instID];
            $ret['itemID'] = $retorno[itemID];
            $ret['permanentLoc'] = '0';
            $ret['titleID'] = $retorno[titleID];
            $ret['sortBin'] = $retorno[sortBin];
            
            //Retornando código do cartão, ao inves do número da pessoa
            //$ret['patronID'] = $retorno[patronID];
            $ret['patronID'] = $patronID;

            
            $ret['itemProp'] = $itemProp;

            if(is_null($retorno[screenMsg])){
                $ret['screenMsg'] = 'Sucesso! Exemplar devolvido: '. $itemID;
            }else{
                $msg = implode('#', $retorno[screenMsg]);
                $ret['screenMsg'] = $msg;
            }
            
            $ret['printMsg'] = 'null';
        }
        else //CASO OCORRA A FALHA
        {
            /* Alteração momentânea caso ocorra uma falha na devoluçaõ
             * Valor setado para 'Y' no campo OK, à pedidos para teste de operação das maquinas.
             * $ret['ok'] = 'N';
             */
            
            $ret['ok'] = 'Y';
            $ret['resensitize'] = 'N';
            $ret['alert'] = 'Y';
            $ret['dateTime'] = GDate::getYYYYMMDDHHMMSS($dateTime);
            $ret['instID'] = $instID;
            $ret['itemID'] = $itemID;
            $ret['permanentLoc'] = '0';
            
            //Definindo título para a resposta
            $titulo = $this->busMaterial->getMaterialTitleByItemNumber($itemID);
            if($titulo)
            {
                $ret['titleID'] = $titulo;
            }
            else
            {
                $ret['titleID'] = 'Sem título;';
            }
            
            //0. Obtem informações desse terminal
                $termInformation = $this->busSipEquipament->getSipEquipament($termID);
            
                //Seta o valor do binDefault
                $padraoBin = $termInformation->binDefault;
                
                //1. Pega estado do material
                $estadoDoMaterial = $this->busExemplaryControl->getExemplaryStatus($itemID);
                
                if($estadoDoMaterial)
                {
                    //2. Obtem os Bins cadastrados para esse terminal
                    $this->busSipEquipamentBinRules->sipEquipamentId = $termID;
                          
                    $bin = $this->busSipEquipamentBinRules->searchSipEquipamentBinRules(TRUE);
                            
                    //Verifica se tem bin cadastrado
                    if(empty($bin))
                    {
                        //Se tiver vazio, seta como binPadrão
                        $sortBin = $padraoBin;
                    }else
                    {
                        //Para cada bin encontrado
                        foreach($bin as $b)
                        {
                            //Verifica se o estado desse material, é o mesmo estado do bin
                            if($estadoDoMaterial == $b->exemplaryStatusId)
                            {
                                //Se for, seta o valor
                                $sortBin = $b->bin;
                            }
                        }
                        //Caso ainda tiver vazio, seta o bin padrão
                        if(empty($sortBin))
                        {
                            $sortBin = $padraoBin;
                        }
                    }
                }else
                {
                    //1. Caso não tenha estado, manda no bin padrão
                    $sortBin = $padraoBin;
                }
                
            $ret['sortBin'] = $sortBin;
            
            $ret['patronID'] = 'null';
            
            $ret['itemProp'] = $itemProp;

            if(is_null($retorno[screenMsg])){
                $ret['screenMsg'] = 'Desculpe, mas não foi possível fazer a devolução do exemplar: '. $itemID . '. Consulte informações no balcão de atendimento.';
            }else{
                $msg = implode('#', $retorno[screenMsg]);
                $ret['screenMsg'] = $msg;
            }
            
            $ret['printMsg'] = 'null';
        }
        
        //Monta a string de retorno com as informações        
        $backInfo = implode(SIP_DELIMITER, $ret);
        
        /*
         * Validação da mensagem de retorno.
         * Cada campo não pode ter mais do que 258 caracteres
         * Implementado por: Tcharles S.
         * Em : 10/07/2014
         */
        $validacao = explode("||", $backInfo);
        
        foreach($validacao as $valid)
        {
            $validos[] = GSipCirculation::setSipSize($valid);
        }
        
        $backInfo = implode(SIP_DELIMITER, $validos);
        
        $endTime = microtime(true);
        $time = $endTime - $startTime;
        
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[CHECKIN] -- RESPOSTA : [" . $backInfo . "]");
            GSipLog::insertSipLog("[CHECKIN] -- TEMPO DE RESPOSTA: $time");
            GSipLog::insertSipLog("[CHECKIN] -- END WEBSERVICE");
            GSipLog::insertSipLog("...");
        }
        
        return $backInfo;
    }

    
    /*
     * Esqueleto itemInformation, retorna uma String
     * Parametros:
     * 
     * String dateTime, 
     * String instID, 
     * String itemID,
     * String termID, 
     * String termPwd
     * 
     * Descrição: Função criada para retornar as informações de um exemplar
     *      
     * 
     * Criado por: Lucas Rodrigo Gerhardt
     * Em: 25/11/2013
     * Ultima Atualização por: Tcharles Silva.
     * Em: 16/07/2015
     * Motivo: 
     *      Campo dueDate deve retornar a data prevista de devolução da gtcLoan.
     * 
     */
    public function itemInformation($dateTime, $instID, $itemID, $termID, $termPwd) 
    {
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[ITEMINFORMATION] -- START WEBSERVICE");
            GSipLog::insertSipLog("[ITEMINFORMATION] -- START PARAMETROS");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("dateTime : $dateTime");
            GSipLog::insertSipLog("instID : $instID");
            GSipLog::insertSipLog("itemID : $itemID");
            GSipLog::insertSipLog("termID : $termID");
            GSipLog::insertSipLog("termPwd : $termPwd");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("[ITEMINFORMATION] -- END PARAMETROS");
        }
        
        $startTime = microtime(true);
        
        try
        {
            //Procura pelo estado do exemplar
            $eStatusId = $this->busExemplaryControl->getExemplaryStatus($itemID);
            
            if($eStatusId)
            {
                //Obtem a informação da tabela gtcSipCirculationStatus
                $sipCirId = $this->busSipCirculationStatus->getSipCirculationStatusId($eStatusId);
            }
            
            //Caso reconheça o estado do SIP, referente à tabela
            if($sipCirId)
            {
                //Atribui valor de relacionamento Estado do Exemplar X gtcSipCirculationStatus
                $retorno['circulationStatus'] = $sipCirId;
            }
            else
            {
                //Setado como 1, caso não exista na tabela de referência gtcSipCirculationStatus
                $retorno['circulationStatus'] = '1';
            }
            
            // Setado como zero, pois é referente à 'other'.
            $retorno['securityMarker'] = 0;
            
            // Data atual.
            $retorno['dateTime'] = GDate::now()->getDate(GDate::MASK_DATE_STRING);

            /* O campo dueDate é opcional.
             * A data que deve ser mostrada, é a data prevista de devolução, no registro da gtcLoan
             * 
             * Caso o exemplar esteja disponível, iremos colocar a data com o dia de hoje.
             */
            
            // Obtem informação do estado do material
            $isMaterial = $this->busExemplaryControl->getExemplaryControl($itemID);
            
            if($isMaterial)
            {
                //Indica que o material existe na biblioteca
                $isReal = true;
                
                // Se o estado do material for emprestado
                if($isMaterial->exemplaryStatusId == DEFAULT_EXEMPLARY_STATUS_EMPRESTADO)
                {
                    // Obter o loanId corrente deste material
                    $registroLoan = $this->busLoan->getLoanOpen($itemID);
                    
                    // Com o registroloan, obtem o returnForecastDate
                    $dataRetorno = $registroLoan->returnForecastDate;
                    
                    // Campo dueDate será a variavel $date no formato YYYYMMDDHHMMSS
                    $dataR = explode(" ", $dataRetorno);
                    $date = explode("/", $dataR[0]);
                    $dateT = str_replace(":", "", $dataR[1]);
                    $date = $date[2] . $date[1] . $date[0] . $dateT;
                }
                else
                {
                    // O campo dueDate será a data atual, caso o material não esteja emprestado
                    $date = GDate::now()->getDate(GDate::MASK_DATE_STRING);
                }
            }
            else
            {
                //Indica que o material não existe na biblioteca
                $isReal = false;
                // O campo dueDate será a data atual, caso o material não exista
                $date = GDate::now()->getDate(GDate::MASK_DATE_STRING);
            }

            $retorno['dueDate'] = $date;
           
            /*
             * A interpretação que tivemos do recallDate, seria a data em que a biblioteca chamaria o item devolta
             * Identico ao caso do dueDate.
             * Caso o material esteja emprestado, seria o returnForRecastDate
             * Caso o material não esteja emprestado, é a data atual.
             * 
             * Utilizamos a variavel $date do dueDate neste caso
             */
            $retorno['recallDate'] = $date;
            
            //Retorna o itemId passado como parâmetro
            $retorno['itemId'] = $itemID;
            
            //Caso não seja passado itemId, retorna o zero
            if (empty($retorno['itemId']))
            {
                $retorno['itemId'] = '0';
            }
            
            //Informa o título deste material
            $retorno['titleID'] = $this->busMaterial->getMaterialTitleByItemNumber($itemID);
            if (empty($retorno['titleID']))
            {
                $retorno['titleID'] = 'Material sem título.';
            }
            
            //Campo opcional, informa o nome da biblioteca dona deste item
            $retorno['owner'] = $this->busExemplaryStatusHistory->getLibraryOfItemNumber($itemID);
            if (empty($retorno['owner']))
            {
                $retorno['owner'] = 'Sem biblioteca.';
            }
            
            //Campo opcional informando onde esse item é armazenado após o checkin
            $retorno['permanentLoc'] = 'NULL';
            
            //Campo opcional que informa a localização atual deste item na biblioteca
            $retorno['currentLoc'] = 'NULL';
            
            //Campo opcional com informações adicionais relativas ao item
            $retorno['itemProp'] = $retorno['titleID'];
            if (empty($retorno['itemProp']))
            {
                $retorno['itemProp'] = 'NULL';
            }
            
            //Mensagem que aparece na tela
            if($isReal)
            {
                $retorno['screenMsg'] = 'Item conferido com sucesso!';
            }
            else
            {
                //Mensagem retornada caso não encontre o item informado
                $retorno['screenMsg'] = 'Esse material não foi reconhecido!';
            }
            
            $retorno['printMsg'] = 'NULL';
        }
        catch ( Exception $e )
        {
            $retorno['screenMsg'] = $e->getMessage();
        }
        
        $itemInformation = implode(SIP_DELIMITER, $retorno);
        
        /*
         * Validação da mensagem de retorno.
         * Cada campo não pode ter mais do que 258 caracteres
         * Implementado por: Tcharles S.
         * Em : 10/07/2014
         */
        $validacao = explode("||", $itemInformation);
        
        foreach($validacao as $valid)
        {
            $validos[] = GSipCirculation::setSipSize($valid);
        }
        
        $backInfo = implode(SIP_DELIMITER, $validos);
        
        $endTime = microtime(true);
        $time = $endTime - $startTime;
        
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[ITEMINFORMATION] -- RESPOSTA : [" . $backInfo . "]");
            GSipLog::insertSipLog("[ITEMINFORMATION] -- TEMPO DE RESPOSTA: $time");
            GSipLog::insertSipLog("[ITEMINFORMATION] -- END WEBSERVICE");
            GSipLog::insertSipLog("...");
        }
        
        return $backInfo;
    }

    /*
     * Esqueleton renew, retorna uma String
     * Parametros:
     * 
     * Boolean thdAllowed, 
     * Boolean noBlock, 
     * String dateTime, 
     * String nbDueDate, 
     * String instID, 
     * String patronID, 
     * String patronPwd, 
     * String itemID, 
     * String titleID, 
     * String termID, 
     * String termPwd, 
     * String itemProp
     * 
     * Descrição:
     *      Método que realiza a renovação.
     * 
     * Criado por: Tcharles Silva
     * Em: 02/12/2013
     * Ultima Atualização por: Tcharles Silva
     * Em: 21/07/2014
     * Motivo: 
     *      Habilitar usuário realizar renovação sem ter a senha
     */
    public function renew( $thdAllowed, $noBlock, $dateTime, $nbDueDate, $instID, $patronID,
                          $patronPwd, $itemID, $titleID, $termID, $termPwd, $itemProp ) 
    {
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[RENEW] -- START WEBSERVICE");
            GSipLog::insertSipLog("[RENEW] -- START PARAMETROS");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("thdAllowed : $thdAllowed");
            GSipLog::insertSipLog("noBlock : $noBlock");
            GSipLog::insertSipLog("dateTime : $dateTime");
            GSipLog::insertSipLog("nbDueDate : $nbDueDate");
            GSipLog::insertSipLog("instID : $instID");
            GSipLog::insertSipLog("patronID : $patronID");
            GSipLog::insertSipLog("patronPwd : ". md5($patronPwd));
            GSipLog::insertSipLog("itemID : $itemID");
            GSipLog::insertSipLog("titleID : $titleID");
            GSipLog::insertSipLog("termID : $termID");
            GSipLog::insertSipLog("termPwd : $termPwd");
            GSipLog::insertSipLog("itemProp : $itemProp");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("[RENEW] -- END PARAMETROS");
        }        
        
                
        /* Para obter a identificação da pessoa, primeiramente procura pelo personId
         * Caso não encontre o personId, irá validar pelo login */
        $personInf = $this->busPerson->getPerson($patronID, TRUE);

        if($personInf)
        {
            $personId = $personInf->personId;
        }
        else
        {
            /* Alteração para funcionalidade de uso de cartão - Univates 06/2014
             * 
             * O personId aqui, é o código do cartão.
             * O código do cartão na basPerson é o campo login.
             * Deve-se procurar pela pessoa cujo login seja personId (código do cartão).
             * Após obter a pessoa, obter o personId da mesma, e dar continuidade no algoritmo
             */
        
            $idPessoa = $this->busPerson->getPersonIdByLogin($patronID);

            //Agora sim, personId tem o valor do código correto
            $personId = $idPessoa[0][0];
        }
        
        
        $startTime = microtime(true);
        
        $titulo = $this->busMaterial->getMaterialTitleByItemNumber($itemID);
        if(empty($titulo))
        {
            $titulo = ' ';
        }
        
        /* - Método doLoan que realiza empréstimo/renovação --
        GSipCirculation::doLoan($padraoLoan, $termId, $personId, $itemNumber, $loanTypeId )
         - padraoLoan = '1'
         - termId = ""
         - personId = ""
         - itemNumber = ""
         - loanTypeId = "4"
         */
        
        //Define padrão DefaultSip de empréstimo
        $loanTypeId = ID_LOANTYPE_DEFAULTSIPEQUIPAMENT;
        
        $retorno = GSipCirculation::doLoan('1', $termID, $personId, $itemID, $loanTypeId);
        
        if($retorno[ok] == 'Y')
        {
            //Decorrer do código aqui.... Caso tenha concluido a operação
            $ret["ok"] = 'Y';
            $ret["renewalOk"] = $retorno[renewalOk];
            $ret["desensitize"] = "Y";
            $ret["dateTime"] = $retorno[dateTime];
            $ret["instID"] = $retorno[instID];
            
            //Retornando número do cartão ao invés do número de identificação da pessoa
            //$ret["patronID"] = $retorno[patronID];
            $ret["patronID"] = $patronID;
            
            $ret["itemID"] = $retorno[itemID];
            $ret["titleID"] = $titulo;
            $ret["dueDate"] = $retorno[dueDate];
            $ret["securityInhibit"] = "N";
            
            if(empty($itemProp))
            {
                $itemProp = 'null';
            }
            $ret["itemProp"] = $itemProp;

            if(is_null($retorno[screenMsg]))
            {
                if($retorno[renewalOk] == 'Y')
                {
                    $ret["screenMsg"] = "Sucesso! Renovação do item " . $itemID . " concluída.";
                }
                else
                {
                    $ret["screenMsg"] = "Sucesso! Empréstimo concluído.";
                }
            }else
            {
                $ret['screenMsg'] = $retorno[screenMsg];
            }
            
            $ret["printMsg"] = "null";
        }
        else //CASO OCORRA A FALHA
        {
            $ret["ok"] = 'N';
            $ret["renewalOk"] = "N";
            $ret["desensitize"] = "N";
            //Colocar a data no formato abaixo
            $v4 = GDate::now()->getDate(GDate::MASK_DATE_STRING);
            if(empty($v4))
            {
                $v4 = '00000000000000';
            }
            $ret["dateTime"] = $v4;
            
            if(empty($instID))
            {
                $instID = '0';
            }
            $ret["instID"] = $instID;
            
            if(empty($patronID))
            {
                $patronID = $patronID;
            }
            $ret["patronID"] = $patronID;
            
            if(empty($itemID))
            {
                $itemID = '0';
            }
            $ret["itemID"] = $itemID;
            
            if(empty($retorno[titleID]))
            {
                $retorno[titleID] = '0';
            }
            $ret["titleID"] = $titulo;
            
            if(empty($retorno[dueDate]))
            {
                $dataRet = GDate::now()->getDate(GDate::MASK_DATE_STRING);
                $retorno[dueDate] = $dataRet;
            }
            $ret["dueDate"] = $retorno[dueDate];
            $ret["securityInhibit"] = "N";
            
            if(empty($itemProp))
            {
                $itemProp = 'null';
            }
            $ret["itemProp"] = $itemProp;

            if(empty($retorno[screenMsg]))
            {
                $retorno[screenMsg] = 'Desculpe, mas a renovação não foi concluída. Consulte mais informações no balcão de atendimento.';
            }
            $ret['screenMsg'] = $retorno[screenMsg];
            
            $ret["printMsg"] = "null";
        }
        
        //Monta a string de retorno com as informações        
        $backInfo = implode(SIP_DELIMITER, $ret);
        
        /*
         * Validação da mensagem de retorno.
         * Cada campo não pode ter mais do que 258 caracteres
         * Implementado por: Tcharles S.
         * Em : 10/07/2014
         */
        $validacao = explode("||", $backInfo);
        
        foreach($validacao as $valid)
        {
            $validos[] = GSipCirculation::setSipSize($valid);
        }
        
        $backInfo = implode(SIP_DELIMITER, $validos);
        
        $endTime = microtime(true);
        $time = $endTime - $startTime;

        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[RETURN] -- RESPOSTA : [" . $backInfo . "]");
            GSipLog::insertSipLog("[RETURN] -- TEMPO DE RESPOSTA: $time");
            GSipLog::insertSipLog("[RETURN] -- END WEBSERVICE");
            GSipLog::insertSipLog("...");
        }
        
        return $backInfo;
    }

    
    
    /*
     * Esqueleto de RENEWALL, retorna uma String
     * Parametros:
     * 
     * String dateTime, 
     * String instID, 
     * String patronID, 
     * String patronPwd, 
     * String termID, 
     * String termPwd
     * 
     * Descrição:
     *      
     * 
     * Criado por: 
     * Em: 
     * Ultima Atualização por: 
     * Em: 
     * Motivo: 
     */
    public function renewAll($dateTime, $instID, $patronID, $patronPwd, $termID, $termPwd) 
    { 
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[RENEWALL] -- START WEBSERVICE");
            GSipLog::insertSipLog("[RENEWALL] -- START PARAMETROS");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("dateTime : $dateTime");
            GSipLog::insertSipLog("instID : $instID");
            GSipLog::insertSipLog("patronID : $patronID");
            GSipLog::insertSipLog("patronPwd : " . md5($patronPwd));
            GSipLog::insertSipLog("termID : $termID");
            GSipLog::insertSipLog("termPwd : $termPwd");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("[RENEWALL] -- END PARAMETROS");
        }
        
        //Inicia cronômetro
        $startTime = microtime(true);
        
                
        /* Para obter a identificação da pessoa, primeiramente procura pelo personId
         * Caso não encontre o personId, irá validar pelo login */
        $personInf = $this->busPerson->getPerson($patronID, TRUE);

        if($personInf)
        {
            $personId = $personInf->personId;
        }
        else
        {
            /* Alteração para funcionalidade de uso de cartão - Univates 06/2014
             * 
             * O personId aqui, é o código do cartão.
             * O código do cartão na basPerson é o campo login.
             * Deve-se procurar pela pessoa cujo login seja personId (código do cartão).
             * Após obter a pessoa, obter o personId da mesma, e dar continuidade no algoritmo
             */
        
            $idPessoa = $this->busPerson->getPersonIdByLogin($patronID);

            //Agora sim, personId tem o valor do código correto
            $personId = $idPessoa[0][0];
        }
        
        //Formata a data
        $data = GDate::getYYYYMMDDHHMMSS($dateTime);
        
        //Define o personId para o busLoan
        $this->busLoan->personId = $personId;
        
        $this->busLoan->orderByLibraryUnit = TRUE;
        $var = $this->busLoan->getLoansOpen();
        
        //Mensagens de screen
        $sMsg = array();
        
        //Contadores
        $renewalCount = 0;
        $unRenewalCount = 0;
        $numOp = 0;
        
        $renDone = array();
        $renNotDone = array();
        
        ////////////////////////////////////////////////////
        //Recebe informações do equipamento
        $dadosSip = $this->busSipEquipament->getSipEquipament($termID);

        //Setar libraryUnitId e Setar Location
        $this->busOperationLoanSip->setLibraryUnit($dadosSip->libraryUnitId);
        $this->busOperationLoanSip->setLocation($dadosSip->locationformaterialmovementid);
        
        //Setando pessoa para otimizar a renovação de todos os itens
        $pessoa = $this->busOperationLoanSip->setPerson($personId, $dadosSip->libraryUnitId, $dadosSip->locationformaterialmovementid);
        
        ///////////////////////////////////////////////

        foreach($var as $loan)
        {
            $emp[$numOp] = GSipCirculation::doLoan('1', $termID, $personId, $loan->itemNumber, ID_RENEWTYPE_DEFAULTSIPEQUIPAMENT, $pessoa);
            
            if($emp[$numOp][ok] == 'Y'){
                
                $renewalCount++;
                $renDone[] = $loan->itemNumber;
                
                if(!$emp[$numOp][screenMsg]){
                    $sMsg[] = "Exemplar " . $loan->itemNumber . " [Renovado]";
                }
                else{
                    $sMsg[] = $emp[$numOp][screenMsg];
                }
            }
            else
            {
                if(!$emp[$numOp][screenMsg]){
                    $sMsg[] = "Exemplar " . $loan->itemNumber . " [Falha]";
                }
                else{
                    $sMsg[] = $emp[$numOp][screenMsg];
                }
                
                $unRenewalCount++;
                $renNotDone[] = $loan->itemNumber;
            }
            $numOp++;
        }
        
        //Verifica se todas as operações foram renovadas
        if($numOp == 0)
        {
            $retorno['ok'] = 'N';
            $sMsg[] = "Usuário não possui empréstimos.";
        }
        else{
            if($numOp == $renewalCount){
                $retorno['ok'] = 'Y';
            }else{
                $retorno['ok'] = 'N';
            }
        }

        $retorno['renewalCount'] = $renewalCount;
        $retorno['unRenewalCount'] = $unRenewalCount;
        $retorno['dateTime'] = $data;
        $retorno['instID'] = $instID;
        
        //Verifica se foi feita alguma renovação
        if(count($renDone) < 1)
        {
            $retorno['renewedItens'] = 'null';
        }
        else
        {
            $retorno['renewedItens'] = implode(',', $renDone);
        }
        
        //Verifica se NAO foi feita renovação
        if(count($renNotDone) < 1)
        {
            $retorno['unRenewedItens'] = 'null';
        }
        else
        {
            $retorno['unRenewedItens'] = implode(',', $renNotDone);
        }

        $retorno['screenMsg'] = implode('#', $sMsg);
        $retorno['printMsg'] = 'null';
        
        $backInfo = implode(SIP_DELIMITER, $retorno);
        
        /*
         * Validação da mensagem de retorno.
         * Cada campo não pode ter mais do que 258 caracteres
         * Implementado por: Tcharles S.
         * Em : 10/07/2014
         */
        $validacao = explode("||", $backInfo);
        
        foreach($validacao as $valid)
        {
            $validos[] = GSipCirculation::setSipSize($valid);
        }
        
        $backInfo = implode(SIP_DELIMITER, $validos);
        
        $endTime = microtime(true);
        $time = $endTime - $startTime;
        
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[RENEWALL] -- RESPOSTA : [" . $backInfo . "]");
            GSipLog::insertSipLog("[RENEWALL] -- TEMPO DE RESPOSTA: $time");
            GSipLog::insertSipLog("[RENEWALL] -- END WEBSERVICE");
            GSipLog::insertSipLog("...");
        }
        
        return $backInfo;
    }
    
    /*
     * Esqueleto de PATRONINFO, retorno String
     * Parametros:
     * 
     * String dateTime, 
     * String summary, 
     * String instID, 
     * String patronID, 
     * String patronPwd, 
     * String termID, 
     * String termPwd, 
     * int startItem, 
     * int endItem
     * 
     * Descrição:
     *      
     * 
     * Criado por: 
     * Em: 
     * Ultima Atualização por: 
     * Em: 
     * Motivo: 
     */
    public function patronInfo($language, $dateTime, $summary, $instID, $patronID, $patronPwd, $termID, $termPwd, $startItem, $endItem ) 
    {        
        
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[PATRONINFO] -- START WEBSERVICE");
            GSipLog::insertSipLog("[PATRONINFO] -- START PARAMETROS");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("language : $language");
            GSipLog::insertSipLog("dateTime : $dateTime");
            GSipLog::insertSipLog("summary : $summary");
            GSipLog::insertSipLog("instID : $instID");
            GSipLog::insertSipLog("patronID : $patronID");
            GSipLog::insertSipLog("patronPwd : " . md5($patronPwd));
            GSipLog::insertSipLog("termID : $termID");
            GSipLog::insertSipLog("termPwd : $termPwd");
            GSipLog::insertSipLog("startItem : $startItem");
            GSipLog::insertSipLog("endItem : $endItem");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("[PATRONINFO] -- END PARAMETROS");
        }
        
        //Inicia cronômetro
        $startTime = microtime(true);
        
        //Variavel para informações do usuários
        $patronStatus = Array();
        
        //Acesso sem senha, por padrão, é falso.
        $acessoSemSenha = false;
        
        //Data atual
        $data = GDate::getYYYYMMDDHHMMSS(GDate::Now()->getDate());
        
        //Lista de itens nas condições
        $hItens = Array(); //itens reservados
        $oItens = Array(); //itens atrasados
        $cItens = Array(); //itens emprestados
        $fItens = Array(); //itens com multa
        $rItens = Array(); 
        $uItens = Array();
        
        //Variáveis para contadores
        $holdItensCount = 0; //Reservas Solicitadas
        $overdueItensCount = 0; //Emprestimos atrasados
        $chargedItensCount = 0; //Itens com o usuário
        $fineItensCount = 0; //Multas para o usuario
        $recallItensCount = 0; //Itens que a biblioteca quer devolta
        $uHoldItensCount = 0; //Indisponíveis na biblioteca
        
        //Implementação hardcore, pois o equipamento não está trazendo esta informação.
        $summary = "HOCFRU";
                
        /* Para obter a identificação da pessoa, primeiramente procura pelo personId
         * Caso não encontre o personId, irá validar pelo login */
        $personInf = $this->busPerson->getPerson($patronID, TRUE);

        if($personInf)
        {
            $personId = $personInf->personId;
        }
        else
        {
            /* Alteração para funcionalidade de uso de cartão - Univates 06/2014
             * 
             * O personId aqui, é o código do cartão.
             * O código do cartão na basPerson é o campo login.
             * Deve-se procurar pela pessoa cujo login seja personId (código do cartão).
             * Após obter a pessoa, obter o personId da mesma, e dar continuidade no algoritmo
             */
        
            $idPessoa = $this->busPerson->getPersonIdByLogin($patronID);

            //Agora sim, personId tem o valor do código correto
            $personId = $idPessoa[0][0];
        }
        
        /*
         * Implementação para autenticar usuário, conforme cadastrado no equipamento (formulário)
         * Implementado em: 21/07/2014
         * Por: Tcharles Silva.
         */
        
        //Obtem informações do equipamento
        $eqSip = $this->busSipEquipament->getSipEquipament($termID);
        
        //Se tiver "Autentica usuário apenas com senha ( requiredpassword )", como falso, não autentica usuário
        if(! MUtil::getBooleanValue($eqSip->requiredpassword))
        {
            if($personId)
            {
                $acessoSemSenha = true;
            }
        }
        else
        {
            //Se tiver o campo "Autentica usuário apenas com senha", segue fluxo de autenticação
            $isPerson = $this->busAuthenticate->authenticate($personId, $patronPwd);
        }        
        
        //Verifica se o usuário existe ou é liberado acesso sem senha
        
        if($isPerson || $acessoSemSenha)
        {
            $person = $this->busPerson->getPerson($personId);
            if($person)
            {
                $nome = $person->personName;
                $local = $person->cityName;
                $phone = $person->phone;
                foreach($phone as $ph)
                {
                    $fone[] = $ph->phone;
                }
                $phone = implode(',', $fone);
            }
            
            //Define o grupo de maior prioridade do usuário
            $link = $this->busBond->getActivePersonLink($personId);
            //Realiza pesquisa para obter as politicas
            $policy = $this->busPolicy->getUserPolicy($instID, $personId, $link->activelink, 1);
            //Define os valores
            //$holdItensLimit = $policy[0]->reserveLimit;
            //$chargedItensLimit = $policy[0]->loanLimit;
            
            $chargedItensLimit = 0;
            $holdItensLimit = 0;
            
            foreach($policy as $pol)
            {
                $chargedItensLimit += $pol->loanLimit;
                $holdItensLimit += $pol->reserveLimit;
            }
            
            //Instanciando o objeto busRight para realizar a pesquisa
            $this->busRight->linkId = $link->activelink;
            $this->busRight->operationId = ID_OPERATION_LOAN_DELAY_LOAN;
            
            //Verifica se encontra direito de com materiais atrasados
            $right = $this->busRight->verifyRightSip();
            
            //Se tiver direito de retirar com materiais atrasados, seta o valor de limite de atraso
            if($right)
            {
                //Quantidade de 'atrasados' é equivalente à quantidade de itens que podem ser emprestados
                $overdueItensLimit = $chargedItensLimit;
            }
            else
            {
                $overdueItensLimit = 0;
            }
            
            //Variaveis para serem utilizadas nos métodos Staticos
            $linkPessoa = $link->activelink; //LINK
            
            
            //Valores no retorno Patron Status
            
            //Seta os objetos e seus atributos
            $this->busLoan->personId = $personId;
            $this->busLoan->orderByLibraryUnit = TRUE;
            
            /* Caso alguma das variáveis abaixo, tenha o valor de TRUE
             * O algoritmo irá IGNORAR as verificações de:
             *  1 : Direito de empréstimo negado 
             *  0 : Renovação negado
             */
            $ignoreExcedLimit = MUtil::getBooleanValue($eqSip->psLoanlimit);
            $ignoreExcedOverdue = MUtil::getBooleanValue($eqSip->psOverduelimit);
            $ignoreExcedPenalty = MUtil::getBooleanValue($eqSip->psPenaltylimit);
            $ignoreExcedFine = MUtil::getBooleanValue($eqSip->psFinelimit);
         
            //2- Direito de pedir o item devolta negado
                //Não existe atualmente no Gnuteca
                   
            //4- Cartão dado como perdido
            //Ainda não disponivel no Gnuteca, não será implementado.
            
            /* Verifica se o terminal utilizado não possui liberação para esse patronStatus
             * Caso esteja, não executará a verificação de exceder o limite de empréstimo
             */
            
            if(! GSipCirculation::ignoreVerifyAcessToLoan($termID))
            {
                //5- Estorou limite de empréstimos
                $emp = $this->busLoan->getLoansOpen();
                //Contador para ver numero de emprestimos atuais
                $empN = 0;
                foreach($emp as $c)
                {
                    $empN++;
                }

                //Define que o usuário não pode realizar o empréstimo
                if($chargedItensLimit <= $empN)
                {
                    $patronStatus[] = '0';
                    $patronStatus[] = '1';
                    $patronStatus[] = '5';
                }
            }
            
            /* Verifica se o terminal utilizado não possui liberação para esse patronStatus
             * Caso esteja, não executará a verificação de exceder o limite de atrasos
             */
            
            if(! GSipCirculation::ignoreVerifyAcessToOverdue($termID))
            {
                //6- Estorou limite de atrasos
                //Obtem todos os empréstimos que não tem data final
                $loanAtrasados = $this->busLoan->getDelayedLoanByUser();
                $atrasados = 0;
                
                //Para cada empréstimo atrasado, verifica se a data de retorno é nula
                foreach($loanAtrasados as $lA)
                {
                    if($lA->returnDate == NULL)
                    {
                        
                        //Compara a data atual com a data prevista de devolução, para contabilizar os atrasados
                        $date = new GDate(GDate::now()->getDate());
                        $compare = new GDate($lA->returnForecastDate);
                        
                        //Caso a diferença de dias, for maior do que 1 dia, o material está atrasado
                        $diffOfDates = GDate::now()->diffDates($compare, $date);
                        if($diffOfDates->days > 1)
                        {
                           $atrasados++;
                        }
                    }
                }

                if($overdueItensLimit < $atrasados)
                {
                    $patronStatus[] = '0';
                    $patronStatus[] = '1';
                    $patronStatus[] = '6';
                }
            }
            
            //7- Estorou limite de renovações
                //Usuario nao possui limite de renovações
            
            
            /* Verifica se o terminal utilizado não possui liberação para esse patronStatus
             * Caso esteja, não executará a verificação de exceder o limite de penalidades
             */
            if(! GSipCirculation::ignoreVerifyAcessToPenalty($termID))
            {
                //8- Muitas reclamações de itens devolvidos
                $comRecl = GSipCirculation::getRightWith('p',  $personId, $linkPessoa, ID_OPERATION_LOAN_PENALTY, NULL );
                if($comRecl)
                {
                    $patronStatus[] = '0';
                    $patronStatus[] = '1';
                    $patronStatus[] = '8';
                }
            }
                        
            //9- Estorou limite de itens perdidos
                //Gnuteca não tem essa funcionalidade. Ele trata como sendo uma penalidade.
            

            /* Verifica se o terminal utilizado não possui liberação para esse patronStatus
             * Caso esteja, não executará a verificação de exceder o limite de multas
             */
            if(! GSipCirculation::ignoreVerifyAcessToFine($termID))
            {
                //10- Multas excessivas em circulação (Mostrará apenas as com status : Em aberto
                $comFine = GSipCirculation::getRightWith('f',  $personId, $linkPessoa, ID_OPERATION_LOAN_FINE, $instID);
                if($comFine)
                {
                    $patronStatus[] = '0';
                    $patronStatus[] = '1';
                    $patronStatus[] = '10';
                }
            }

            /* Verifica se o terminal utilizado não possui liberação para esse patronStatus
             * Caso esteja, não executará a verificação de exceder o limite de penalidades
             */
            if(! GSipCirculation::ignoreVerifyAcessToPenalty($termID))
            {
                //11- Penalidades excessivas em circulação
                $comPenal = GSipCirculation::getRightWith('p',  $personId, $linkPessoa, ID_OPERATION_LOAN_PENALTY, NULL);
                if($comPenal)
                {
                    $patronStatus[] = '0';
                    $patronStatus[] = '1';
                    $patronStatus[] = '11';
                }
            }
            
            //12- Devolver para a biblioteca com atraso
            
            
            //13- Muitas faturas
            
            
            $patronStatus = implode(',', $patronStatus);
            
            //Verificar as operações que irá fazerM
            $hold = stripos($summary, 'H');
            $overdue = stripos($summary, 'O');
            $charged = stripos($summary, 'C');
            $fine = stripos($summary, 'F');
            $recall = stripos($summary, 'R');
            $unavailable = stripos($summary, 'U');
            
            //Reservas feitas pelo usuário
            if($hold !== FALSE)
            {
                //Define a pessoa
                $this->busReserve->personId = $person->personId;

                //Pegando as reservas para a variavel
                $reservas = $this->busReserve->searchReserve();
                
                foreach($reservas as $r)
                {
                    //Verifica se o status da reserva é 2-atendida ou 3- comunicada
                    if($r[10] == ID_RESERVESTATUS_ANSWERED || $r[10] == ID_RESERVESTATUS_REPORTED)
                    {
                        //É uma reserva ou atendida, ou comunicada
                        $this->busReserveComposition->reserveId = $r[0];
                        $this->busReserveComposition->reserveIdS = $r[0];
                        
                        // Somente confirmadas
                        $this->busReserveComposition->isConfirmed = TRUE;
                        $this->busReserveComposition->isConfirmedS = TRUE;
                        
                        $listReserves = $this->busReserveComposition->searchReserveComposition(TRUE);
                        
                        foreach($listReserves as $lReserves)
                        {
                            $hItens[] = $lReserves->itemNumber;
                        }
                        $holdItensCount++;
                    }
                }
                $holdItens = implode(',', $hItens);
            }

            //Itens que já passaram da data de entrega
            if($overdue !== FALSE)
            {
                //seta User
                $this->busLoan->personId = $personId;
                //seta Data
                $now = GDate::now();
                $now->setHour('00');
                $now->setMinute('00');
                $now->setSecond('00');
                $now = $now->getDate(GDate::MASK_TIMESTAMP_USER);
                $this->busLoan->returnForecastDate = $now;

                //Busca todos os empréstimos atrasados
                $loans = $this->busLoan->getDelayedLoanByUser();         

                //Se não tiver nenhum material atrasado neste período
                if ( $loans )
                {
                    foreach($loans as $l)
                    {
                        $overdueItensCount++;
                        $oItens[] = $l->itemNumber;
                    }
                }
                $overdueItens = implode(',', $oItens);
            }
            
            //Itens que estão com o usuário
            if($charged !== FALSE)
            {
                $this->busLoan->personId = $personId;
                $this->busLoan->orderByLibraryUnit = TRUE;
                $var = $this->busLoan->getLoansOpen();
                foreach($var as $loan)
                {
                    $chargedItensCount++;
                    $cItens[] = $loan->itemNumber;
                }
                $chargedItens = implode(',', $cItens);
            }
            
            //Itens que serão cobrados multa
            if($fine !== FALSE)
            {
                $this->busFine->personId = $personId;
                $this->busFine->personIdS = $personId;
                
                $fAberta = $this->busFine->searchFine(TRUE);
                
                $array_preferencia = explode(',', SIP_FINESTATUS);
                $valorMulta = 0;
                
                if($fAberta)
                {
                    foreach($fAberta as $f)
                    {                        
                        if( in_array($f->fineStatusId, $array_preferencia) ) //SIP_FINESTATUS (1,3)
                        {                            
                            $loan = $this->busLoan->getLoan($f->loanId, TRUE);
                            $fItens[] = $loan->itemNumber;
                            $fineItensCount++;
                            $valorMulta = $valorMulta + $f->value;
                        }
                    }
                    $fineItens = implode(',', $fItens);
                }
            }
            
            //Calcula a quantidade de renovações dos empréstimos em aberto.
            if($recall !== FALSE)
            {
                $this->busLoan->personId = $personId;
                $this->busLoan->orderByLibraryUnit = TRUE;
                $var = $this->busLoan->getLoansOpen();

                foreach($var as $loan)
                {
                    //Consultar e somar todas as renovações de cada loan
                    $renewItens = $this->busRenew->getRenewsOfLoan($loan->loanId);

                    if ($renewItens)
                    {
                        $rItens[] = $loan->itemNumber;
                        foreach($renewItens as $rI)
                        {
                            $recallItensCount++;
                        }
                    }
                }
                $recallItens = implode(',', $rItens);
            }
            
            //Livros reservados com o status Solicitado
            if($unavailable !== FALSE)
            {
                //Obter e contar todas as reservas que estão como Solicitadas
                $this->busReserve->personId = $person->personId;

                //Pegando as reservas para a variavel
                $reservas = $this->busReserve->searchReserve();
                
                foreach($reservas as $r)
                {
                    //Verifica se o status da reserva é 1- Solicitada
                    if($r[10] == ID_RESERVESTATUS_REQUESTED )
                    {
                        $this->busReserveComposition->isConfirmed = NULL;
                        $this->busReserveComposition->isConfirmedS = NULL;
                        
                        //É uma reserva ou atendida, ou comunicada
                        $this->busReserveComposition->reserveId = $r[0];
                        $this->busReserveComposition->reserveIdS = $r[0];
                        
                        $listReserves = $this->busReserveComposition->searchReserveComposition(TRUE);
                        
                        /*
                         * Se tiver alguma reserva confirmada, responde apenas com a confirmada
                         * Do contrário, devolve apenas um item para essa reserva.
                         */
                        $haveItem = false;
                        
                        foreach($listReserves as $isCm)
                        {
                            //Se não tiver o item ainda
                            if(!$haveItem)
                            {
                                //Caso a reserva for confirmada
                                if(MUtil::getBooleanValue($isCm->isConfirmed))
                                {
                                    //Adiciona item na lista e atualiza controller
                                    $uItens[] = $isCm->itemNumber;
                                    $haveItem = true;
                                }
                            }
                        }
                        
                        //Caso não tenha reserva confirmada
                        if(!$haveItem)
                        {
                            foreach($listReserves as $lReserves)
                            {
                                //Caso não tiver o item na lista ainda
                                if(!$haveItem)
                                {
                                    //Adiciona item e atualiza controller
                                    $uItens[] = $lReserves->itemNumber;
                                    $haveItem = true;
                                }
                            }
                        }
                        //Contabiliza as reservas
                        $uHoldItensCount++;
                    }
                }
                $uHoldItens = implode(',', $uItens);
            }
            
            //Verificações para ver se não estão vazias
            if(empty($patronStatus))
            {
                $patronStatus = ' ';
            }
            if(empty($holdItens))
            {
                $holdItens = 'null';
            }
            if(empty($overdueItens))
            {
                $overdueItens = 'null';
            }
            if(empty($chargedItens))
            {
                $chargedItens = 'null';
            }
            if(empty($fineItens))
            {
                $fineItens = 'null';
            }
            if(empty($recallItens))
            {
                $recallItens = 'null';
            }
            if(empty($uHoldItens))
            {
                $uHoldItens = 'null';
            }
            if(empty($phone))
            {
                $phone = 'null';
            }
            
            //Montagem do retorno
            $retorno['patronStatus'] = $patronStatus;
            $retorno['language'] = '10';
            $retorno['dateTime'] = $data;
            $retorno['holdItensCount'] = $holdItensCount;
            $retorno['overdueItensCount'] = $overdueItensCount;
            $retorno['chargedItensCount'] = $chargedItensCount;
            $retorno['fineItensCount'] = $fineItensCount;
            $retorno['recallItensCount'] = $recallItensCount;
            $retorno['uHoldsItensCount'] = $uHoldItensCount;
            
            $retorno['instID'] = $instID;
            
            //Retornar número do cartão ao invés do número de identificação da pessoa
            //$retorno['patronID'] = $personId;
            $retorno['patronID'] = $patronID;
            
            $retorno['personalName'] = $nome;

            $retorno['holdItensLimit'] = $holdItensLimit;
            $retorno['overdueItensLimit'] = $overdueItensLimit;
            $retorno['chargedItensLimit'] = $chargedItensLimit;
            
            $retorno['validPatron'] = 'Y';
            $retorno['validPatronPwd'] = 'Y';
            
            $retorno['currencyType'] = 'null';
            $retorno['feeAmount'] = str_replace(',', '.', $valorMulta);
            $retorno['feeLimit'] = 'null';
            
            $retorno['holdItens'] = $holdItens;
            $retorno['overdueItens'] = $overdueItens;
            $retorno['chargedItens'] = $chargedItens;
            $retorno['fineItens'] = $fineItens;
            $retorno['recallItens'] = $recallItens;
            $retorno['uHoldItens'] = $uHoldItens;
            $retorno['homeAdress'] = $local;
            $retorno['homePhone'] = $phone;
            $retorno['screenMsg'] = 'Usuário encontrado.';
            $retorno['printMsg'] = 'null';
        }
        else
        {
            $retorno['patronStatus'] = ' ';
            $retorno['language'] = '10';
            $retorno['dateTime'] = $data;
            $retorno['holdItensCount'] = $holdItensCount;
            $retorno['overdueItensCount'] = $overdueItensCount;
            $retorno['chargedItensCount'] = $chargedItensCount;
            $retorno['fineItensCount'] = $fineItensCount;
            $retorno['recallItensCount'] = $recallItensCount;
            $retorno['uHoldsItensCount'] = $uHoldItensCount;
            $retorno['instID'] = $instID;
            
            // Retornar código do cartão ao invés do código da pessoa
            $retorno['patronID'] = $patronID;
            
            $retorno['personalName'] = 'Não encontrado.';
            $retorno['holdItensLimit'] = '0';
            $retorno['overdueItensLimit'] = '0';
            $retorno['chargedItensLimit'] = '0';
            
            if ( strlen($personId) > 0 )
            {
                $retorno['validPatron'] = 'Y';
            }
            else
            {
                $retorno['validPatron'] = 'N';
            }
            $retorno['validPatronPwd'] = 'N';
            
            $retorno['currencyType'] = 'null';
            $retorno['feeAmount'] = 'null';
            $retorno['feeLimit'] = 'null';
            
            if(empty($holdItens))
            {
                $holdItens = 'null';
            }
            $retorno['holdItens'] = $holdItens;
            
            if(empty($overdueItens))
            {
                $overdueItens = "null";
            }
            $retorno['overdueItens'] = $overdueItens;
            
            if(empty($chargedItens)){
                $chargedItens = 'null';
            }
            $retorno['chargedItens'] = $chargedItens;
            
            if(empty($fineItens))
            {
                $fineItens = "null";
            }
            $retorno['fineItens'] = $fineItens;
            
            if(empty($recallItens))
            {
                $recallItens = "null";
            }
            $retorno['recallItens'] = $recallItens;
            
            if(empty($uHoldItens))
            {
                $uHoldItens = "null";
            }
            $retorno['uHoldItens'] = $uHoldItens;
            
            $retorno['homeAdress'] = 'Não encontrado.';
            $retorno['homePhone'] = 'null';
            
            $retorno['screenMsg'] = 'Sem informações desse usuário.';
            $retorno['printMsg'] = 'null';
        }
        
        $backInfo = implode(SIP_DELIMITER, $retorno);
        
        
        /*
         * Validação da mensagem de retorno.
         * Cada campo não pode ter mais do que 258 caracteres
         * Implementado por: Tcharles S.
         * Em : 10/07/2014
         */
        $validacao = explode("||", $backInfo);
        
        foreach($validacao as $valid)
        {
            $validos[] = GSipCirculation::setSipSize($valid);
        }
        
        $backInfo = implode(SIP_DELIMITER, $validos);
        
        $endTime = microtime(true);
        $time = $endTime - $startTime;
        
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[PATRONINFO] -- RESPOSTA : [" . $backInfo . "]");
            GSipLog::insertSipLog("[PATRONINFO] -- TEMPO DE RESPOSTA: $time");
            GSipLog::insertSipLog("[PATRONINFO] -- END WEBSERVICE");
            GSipLog::insertSipLog("...");
        }
        
        return $backInfo;
    }
    
    public function patronStatus($termID, $language, $dateTime, $instID, $patronID, $termPwd, $patronPwd)
    {
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[PATRONSTATUS] -- START WEBSERVICE");
            GSipLog::insertSipLog("[PATRONSTATUS] -- START PARAMETROS");
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("termID : $termID");
            GSipLog::insertSipLog("language : $language");
            GSipLog::insertSipLog("dateTime : $dateTime");
            GSipLog::insertSipLog("instID : $instID");
            GSipLog::insertSipLog("patronID : $patronID");
            GSipLog::insertSipLog("termPwd : $termPwd");
            GSipLog::insertSipLog("patronPwd : " . md5($patronPwd));
            GSipLog::insertSipLog(" ");
            GSipLog::insertSipLog("[PATRONSTATUS] -- END PARAMETROS");
        }
        //Inicia o cronômetro
        $startTime = microtime(true);
        
        
        /* Para obter a identificação da pessoa, primeiramente procura pelo personId
         * Caso não encontre o personId, irá validar pelo login */
        $personInf = $this->busPerson->getPerson($patronID, TRUE);

        if($personInf)
        {
            $personId = $personInf->personId;
        }
        else
        {
            /* Alteração para funcionalidade de uso de cartão - Univates 06/2014
             * 
             * O personId aqui, é o código do cartão.
             * O código do cartão na basPerson é o campo login.
             * Deve-se procurar pela pessoa cujo login seja personId (código do cartão).
             * Após obter a pessoa, obter o personId da mesma, e dar continuidade no algoritmo
             */
        
            $idPessoa = $this->busPerson->getPersonIdByLogin($patronID);

            //Agora sim, personId tem o valor do código correto
            $personId = $idPessoa[0][0];
        }
        
        $isPerson = $this->busAuthenticate->authenticate($personId, $patronPwd);
        $data = GDate::getYYYYMMDDHHMMSS($dateTime); 

        if($isPerson)
        {
            
            $person = $this->busPerson->getPerson($personId);
            
            //Define o grupo de maior prioridade do usuário
            $link = $this->busBond->getActivePersonLink($personId);
            //Realiza pesquisa para obter as politicas
            $policy = $this->busPolicy->getUserPolicy($instID, $personId, $link->activelink);
            //Define os valores
            $chargedItensLimit = $policy[0]->loanLimit;
            
            //Instanciando o objeto busRight para realizar a pesquisa
            $this->busRight->linkId = $link->activelink;
            //Atribui valor 1, pois é refente à livro
            $this->busRight->materialGenderId = 1;
            $this->busRight->operationId = ID_OPERATION_LOAN;
            
            //Verifica se encontra direito de empréstimo ao usuário
            $right = $this->busRight->verifyRightSip();
            
            //Se tiver direito, seta o valor de limite de atraso
            if($right)
            {
                $overdueItensLimit = $policy[0]->loanLimit;
            }
            else
            {
                $overdueItensLimit = 0;
            }
            
            //Variaveis para serem utilizadas nos métodos Staticos
            $linkPessoa = $link->activelink; //LINK
            
            $rightLoan = $comPenal = GSipCirculation::getRightWith('l', $personId, $linkPessoa, ID_OPERATION_LOAN, NULL );

            if(!$rightLoan)
            {
                //Não tem direito a empréstimo
                $patronStatus[] = '0';
                $patronStatus[] = '1';
            }else
            {
                //verificar se tem penalidade
                $comPenal = GSipCirculation::getRightWith('p', $personId, $linkPessoa, ID_OPERATION_LOAN_PENALTY, NULL );
                if($comPenal)
                {
                    $patronStatus[] = '0';
                    $patronStatus[] = '1';
                }else
                {
                    //verificando se tem multa ( verifica apenas com status: aberta )
                    $comMulta = GSipCirculation::getRightWith('f', $personId, $linkPessoa, ID_OPERATION_LOAN_FINE, $instID);
                    if($comMulta)
                    {
                        $patronStatus[] = '0';
                        $patronStatus[] = '1';
                    }
                }
            }

            //2- Direito de pedir o item devolta negado
            //Não existe atualmente no Gnuteca

            //3- Direito de reserva negado
            $fazRen = GSipCirculation::getRightWith('l', $personId, $linkPessoa, ID_OPERATION_LOCAL_RESERVE, NULL);
            if(!$fazRen)
            {
                $patronStatus[] = '3';
            }

            //4- Cartão dado como perdido
            //Ainda não disponivel no Gnuteca, não será implementado.


            //5- Estorou limite de empréstimos
            $this->busLoan->personId = $personId;
            $this->busLoan->orderByLibraryUnit = TRUE;
            $emp = $this->busLoan->getLoansOpen();
            //Contador para ver numero de emprestimos atuais
            $empN = 0;
            foreach($emp as $c)
            {
                $empN++;
            }
            if($chargedItensLimit <= $empN)
            {
                $patronStatus[] = '5';
            }

            //6- Estorou limite de atrasos
            if($overdueItensLimit > $policy[0]->loanLimit)
            {
                $patronStatus[] = '6';
            }

            //7- Estorou limite de renovações
            //Usuario nao possui limite de renovações

            //8- Muitas reclamações de itens devolvidos
            $comRecl = GSipCirculation::getRightWith('p', $personId, $linkPessoa, ID_OPERATION_LOAN_PENALTY, NULL );
            if($comRecl)
            {
                $patronStatus[] = '8';
            }

            //9- Estorou limite de itens perdidos
            //Gnuteca não tem essa funcionalidade

            //10- Multas excessivas em circulação (Mostrará apenas as com status : Aberta
            $comFine = GSipCirculation::getRightWith('f',  $personId, $linkPessoa, ID_OPERATION_LOAN_FINE, $instID);
            if($comFine)
            {
                $patronStatus[] = '10';
            }

            //11- Penalidades excessivas em circulação
            $comPenal = GSipCirculation::getRightWith('p',  $personId, $linkPessoa, ID_OPERATION_LOAN_PENALTY, NULL);
            if($comPenal)
            {
                $patronStatus[] = '11';
            }

            //12- Devolver para a biblioteca com atraso

            //13- Muitas faturas
            $patronStatus = implode('.', $patronStatus);
            
            $retorno['patronStatus'] = $patronStatus;
            $retorno['language'] = $language;
            $retorno['dateTime'] = $data;
            $retorno['instID'] = $instID;
            
            //Retornando identificação do cartão ao invés do código da pessoa
            //$retorno['patronID'] = $personId;
            $retorno['patronID'] = $patronID;
            
            $retorno['personalName'] = $person->personName;
            $retorno['validPatron'] = "Y";
            $retorno['validPatronPwd'] = "Y";
            $retorno['screenMsg'] = "Seja bem-vindo $person->personName";
            $retorno['printMsg'] = "";
            
            $ret = implode(SIP_DELIMITER, $retorno);
        }
        else
        {
            $retorno['patronStatus'] = " ";
            $retorno['language'] = $language;
            $retorno['dateTime'] = $data;
            $retorno['instID'] = $instID;
            
            //Retornando código do cartão ao invés da identificação da pessoa
            //$retorno['patronID'] = $personId;
            $retorno['patronID'] = $patronID;
            
            $retorno['personalName'] = " ";
            $retorno['validPatron'] = "N";
            $retorno['validPatronPwd'] = "N";
            $retorno['screenMsg'] = "Usuário não identificado.";
            $retorno['printMsg'] = "";
            
            $ret = implode(SIP_DELIMITER, $retorno);
        }
        
        /*
         * Validação da mensagem de retorno.
         * Cada campo não pode ter mais do que 258 caracteres
         * Implementado por: Tcharles S.
         * Em : 10/07/2014
         */
        $validacao = explode("||", $ret);
        
        foreach($validacao as $valid)
        {
            $validos[] = GSipCirculation::setSipSize($valid);
        }
        
        $backInfo = implode(SIP_DELIMITER, $validos);
        
        $endTime = microtime(true);
        $time = $endTime - $startTime;
        
        if(USE_SIPLOG == DB_TRUE)
        {
            GSipLog::insertSipLog("[] -- RESPOSTA : [" . $backInfo . "]");
            GSipLog::insertSipLog("[] -- TEMPO DE RESPOSTA: $time");
            GSipLog::insertSipLog("[] -- END WEBSERVICE");
            GSipLog::insertSipLog("...");
        }
        
        return $backInfo;
    }
}
?>