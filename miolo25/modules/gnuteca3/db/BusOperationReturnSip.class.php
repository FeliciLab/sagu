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
 * Classe para empréstimo do Equipamento SIP
 *
 * @author Tcharles Silva [tcharles@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Tcharles Silva [tcharles@solis.coop.br]
 *
 * @since
 * Class created on 04/12/2013
 * 
 **/

$MIOLO = MIOLO::getInstance();
$module = MIOLO::getCurrentModule();
$MIOLO->usesBusiness($module, 'BusOperationReturn');

class BusinessGnuteca3BusOperationReturnSip extends BusinessGnuteca3BusOperationReturn
{
    public $msgs;
    public $operationDate;
    public $personId;
    public $returnId;
    public $fStatus;
    
    public function __construct() 
    {
        parent::__construct();
    }
    
    public function checkItemNumber($itemNumber)
    {
        $MIOLO  = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $this->clearMessages();

        $addItemNumberMSG['itemNumber'] = $itemNumber;

        $exemplary = $this->busExemplaryControl->getExemplaryControl( $itemNumber );

        $this->exemplarys[$itemNumber] = $exemplary; //guarda objeto do exemplar na operação, para não precisa fazer select novamente

        if (!$exemplary->itemNumber)
        {
            $this->addError( _M('Este exemplar não existe.', $this->module) , $addItemNumberMSG);
            return false;
        }

        $libraryUnit = $this->getLibraryUnit(true);//pega objeto na sessão

        if ( $exemplary->libraryUnitId != $this->getLibraryUnit() )
        {
            $this->addError( _M('Esse exemplar não pertence à biblioteca @1. Consulte informações no balcão de atendimento.', $this->module, $libraryUnit->libraryName), $addItemNumberMSG);
            return false;
        }
        
        $loan = $this->busLoan->getLoanOpen($itemNumber);

        if (!$loan)
        {
            $this->addInformation( _M("Não existe empréstimo em aberto para esse material.", $this->module), $addItemNumberMSG );
        }
        else
        {
            //calcula dias em atraso
            $loan->delayDays = $delayLoan = GDate::now()->diffDates(new GDate($loan->returnForecastDate), true)->days;
            
            if ( $loan->loanTypeId == ID_LOANTYPE_MOMENTARY && defined('LOAN_MOMENTARY_PERIOD') && LOAN_MOMENTARY_PERIOD == 'H' )
            {
                $diff = GDate::now()->diffDates( new GDate($loan->returnForecastDate), true );
                $loan->delayHours = $diff->hours + ( $diff->days * 24 ); //soma os dias na diferença, caso tiver
            }
        }

        //define empréstimo no objeto do exemplar, para evitar 2 acessos ao banco
        $this->exemplarys[$itemNumber]->loan = $loan;
        //utilizado na interface e no addItemNumber, posto no objeto para evitar um select repetido;
        $this->exemplarys[$itemNumber]->hasReserve = $this->busReserve->hasReserve( $itemNumber, array(ID_RESERVESTATUS_REQUESTED) );

        return true;
    }
    
    
    public function addItemNumber( $itemNumber, $printReceipt = null, $sendReceipt = null )
    {
    	$MIOLO  = MIOLO::getInstance();
    	$module = MIOLO::getCurrentModule();
    	$busPersonConfig = $MIOLO->getBusiness($module, 'BusPersonConfig');
        $busReturnType = $MIOLO->getBusiness($module, 'BusReturnType');
	$exemplary       = $this->exemplarys[$itemNumber]; //vem do checkItemNumber

        //Pega o valor dos checkBoxs loanReceipt e returnReceipt
        $locationForMaterial    = $this->getLocation( true );
        $personId               = $loan->personId;
        
        
        $person->printReceipt = $this->busPersonConfig->getValuePersonConfig($exemplary->loan->personId, 'MARK_PRINT_RECEIPT_RETURN');
        $person->sendReceipt = $this->busPersonConfig->getValuePersonConfig($exemplary->loan->personId, 'MARK_SEND_RETURN_MAIL_RECEIPT');
        
        //Só pega a preferência USER_CONFIG quando o valor do LocationForMaterialMovement for FALSE e estiver configurado valor do USER_CONFIG como WRITE
        if ( MUtil::getBooleanValue($locationForMaterial->sendReturnReceiptByEmail ) )
        {
            $exemplary->sendReceipt = true;
        }
        else
        {
            $exemplary->sendReceipt = MUtil::getBooleanValue( $busPersonConfig->getValuePersonConfig($exemplary->loan->personId, 'MARK_SEND_RETURN_MAIL_RECEIPT' ) );
        }

        //Obtem informacao se deve ou nao imprimir o recibo para o tipo de retorno.
        $returnTypePrint = $busReturnType->getReturnType($this->getReturnType())->printReturnReceipt;

        //Se o tipo de retorno tem definido se e ou nao para imprimir
        if ( !is_null($returnTypePrint) )
        {
            //Faz valer a configuraçao do tipo de retorno.
            $exemplary->printReceipt = $returnTypePrint;
        }
        else
        {
            //Senao faz valer a configuracao da pessoa.
            $exemplary->printReceipt = MUtil::getBooleanValue($busPersonConfig->getValuePersonConfig($exemplary->loan->personId, 'MARK_PRINT_RECEIPT_RETURN'));            
        }

        $exemplary->searchData   = $this->busSearchFormat->getFormatedString($exemplary->controlNumber, MATERIAL_MOVIMENT_SEARCH_FORMAT_ID, 'search');

        $imgPrint = new MImage('imgPrint', null, GUtil::getImageTheme('print-16x16.png') );
        $imgPrint = $imgPrint->generate();

        $imgEmail = new MImage('imgEmail', null, GUtil::getImageTheme('email-16x16.png') );
        $imgEmail = $imgEmail->generate();

        //se o estado do exemplar for disponível, não informa os dados de impressão e envio, pois não será gerado o recibo.
        if ( $exemplary->exemplaryStatusId != DEFAULT_EXEMPLARY_STATUS_DISPONIVEL )
        {
            
            $exemplary->sendReceiptLabel = MUtil::getBooleanValue( $sendReceipt == 't' ) ? $imgEmail: '';

            if( $sendReceipt == 'i' )
            {
                $exemplary->sendReceiptLabel = MUtil::getBooleanValue($person->sendReceipt)? $imgEmail: '';
            }

            $exemplary->printReceiptLabel = MUtil::getBooleanValue($printReceipt == 't')? $imgPrint: '';

            if( $printReceipt == 'i' )
            {
                $exemplary->printReceiptLabel = MUtil::getBooleanValue($person->printReceipt)? $imgPrint: '';
            }
        }

        $_SESSION['itemsReturn'][$itemNumber] = $exemplary;

        return true;
    }
    
    
    public function finalize( $data = null)
    {
    	$MIOLO = MIOLO::getInstance();
        $this->clearMessages();
        $items = $this->getItemsReturn();

        $this->finalizedItens   = null;
        $this->fine             = null;
        $defaultFineStatus      = null;
        $returnType             = null;

        if ( !is_array( $items) )
        {
            $this->addInformation( _M('Desculpe, mas não há itens para terminar o processo de devolução.', $this->module) );
            return false;
        }

        if ( is_array( $items) )
        {
            //monta relação de itemNumber, para otimizar acesso a banco
            foreach ($items as $line => $info)
            {
            	$itemNumbers[] = $info->itemNumber;
            }

            $null = null;
	        //pega o estado futuro definido para todos exemplares, que possuem agendamentos
	        $futureStatusTMPA = $this->busExemplaryFutureStatusDefined->getStatusDefined( $itemNumbers, $null, true );

	        $futureStatusDefined = null;

	        //trata array para somente ter 1 por itemNumber, e indexar por itemNumber
	        if ( is_array( $futureStatusTMPA ) )
	        {
	            foreach ( $futureStatusTMPA as $line => $statusDefined )
	            {
	            	if ( ! $futureStatusDefined[ $statusDefined->itemNumber ] )
	            	{
	            	   $futureStatusDefined[ $statusDefined->itemNumber ] = $statusDefined;
	            	}
	            }
	        }

	        $exemplaryStatusList = $this->busExemplaryStatus->searchExemplaryStatus(true);

	        //trata array para indexar por exemplaryStatusId
	        if ( is_array( $exemplaryStatusList ) )
	        {
	        	foreach( $exemplaryStatusList as $line => $info )
	        	{
	        		$tmpExemplaryStatus[$info->exemplaryStatusId] = $info;
	        	}
	        }

	        $exemplaryStatusList = $tmpExemplaryStatus;

	        //percorre os items, fazendo o necessário
            foreach ( $items as $line => $info )
            {
            	$itemNumber = $info->itemNumber;
                $currentStatus = $info->exemplaryStatusId;
                $loan = $info->loan;

                $addItemNumberMSG['itemNumber'] = $itemNumber;

                if ( !$loan && !MUtil::getBooleanValue(SUPRESS_RETURN_MESSAGE) )
                {
                    $this->addInformation( _M("Não há empréstimo em aberto para esse material.", $this->module), $addItemNumberMSG );
                }

                //pega o estado futuro definido para o Exemplar, ou seja o agendamento
                $futureStatusTMP = $futureStatusDefined[$itemNumber];
                //pega o objeto do estado
                $statusObj = $exemplaryStatusList[$info->exemplaryStatusId];
                //define no objeto para utilizar em outras operações
                $info->exemplaryStatus = $statusObj;
                //pega valor do campo isreservestatus da tabela gtcExemplaryStatus
                $isReserveStatus = $statusObj->isReserveStatus;
                
                //Deve permitir executar baixa via agendamento de estado do exemplar.
                //(se o estado futuro NAO for de baixa) (E) (isReserveStatus do estado atual for TRUE) ENTAO { desconsidera o agendamento }
                if ( !MUtil::getBooleanValue($tmpExemplaryStatus[$futureStatusTMP->exemplaryStatusId]->isLowStatus) && ($futureStatusTMP && MUtil::getBooleanValue( $isReserveStatus )) )
                {
                    $this->addAlert( _M('Esse material possui agendamento, mas está reservado. Portanto, o agendamento será desconsiderado.'), $addItemNumberMSG);
                    $futureStatusTMP = null;
                }

                $futureStatus = $futureStatusTMP->exemplaryStatusId;

                if ($futureStatus)
                {
                    $this->addAlert( _M('Existe uma troca de estado agendada para este exemplar.', $this->module), $addItemNumberMSG );
                    $appliedStatus = true;
                }
                else
                {
                    if ($loan->loanTypeId == ID_LOANTYPE_MOMENTARY )
                    {
                    	$this->addAlert( _M('Devolução de empréstimo momentâneo.', $this->module), $addItemNumberMSG );
                        $level          = 0;
                        $futureStatus   = $this->busExemplaryStatusHistory->getLastStatus($itemNumber, $level);

                        if (!$futureStatus)
                        {
                            $this->addError( _M('Não foi possível encontrar o estado futuro no histórico do estado do exemplar. Consulte informações no balcão de atendimento.', $this->module, $itemNumber), $addItemNumberMSG );
                            continue;
                        }
                    }
                    else //aqui ele entra caso não tenha um estado futuro definido, busca o novo estado pelas regras
                    {
                        $futureStatus = $this->busRulesForMaterialMovement->getFutureStatus($itemNumber, $currentStatus, ID_OPERATION_RETURN, $this->getLocation() );

                        if (!$futureStatus)
                        {
                            $operationReturnObj = $this->busOperation->getOperation(ID_OPERATION_RETURN);
                            $localObj = $this->getLocation( true );

                            $this->addError( _M('Não foi possível encontrar o estado futuro com a regra @1 - @2 - @3.', $this->module, $statusObj->description, $operationReturnObj->description, $localObj->description), $addItemNumberMSG );
                            continue;
                        }
                        
                        $this->fStatus = $futureStatus;
                    }
                }

                //executa devolução
                if ( $info->loan )
                {
                    $info->operator     = $this->getOperator();
                    $info->returnDate   = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
                    $info->loanId       = $loan->loanId;
                    $info->loanDate     = $loan->loanDate;
                    $info->personId     = $loan->personId;
                    
                    //Atribui a data da devolução para o atributo
                    $this->operationDate = $info->returnDate;

                    $updateLoan = $this->busLoan->returnLoan($loan->loanId, $info->returnDate, $this->getOperator());

                    //insere um registro na minha biblioteca
                    if ( $updateLoan )
                    {
                        $busMyLibrary = $this->MIOLO->getBusiness('gnuteca3', 'BusMyLibrary');
                        $busMyLibrary->personId = $loan->personId;
                        $busMyLibrary->date = GDate::now()->getDate(GDate::MASK_TIMESTAMP_USER);
                        $busMyLibrary->message = stripslashes($busMyLibrary->getReturnMessage($loan->itemNumber));
                        $busMyLibrary->visible = DB_TRUE;
                        $busMyLibrary->insertMyLibrary();
                    }
                    
                    $info->loan->returnDate = $info->returnDate;
                    
                    //só calcula multa e/ou penalidade só se estiver atrasado, em horas ou dias, de acordo com preferência
                    if ( $info->loan->delayHours > 0 || $info->loan->delayDays > 0 )
                    {
                        if ( $info->loan->delayHours > 0 )
                        {
                            $fineValue = $this->busLoan->calculateFineHour( $info->loan->delayHours , $info->materialGenderId, $loan->linkId );
                        }
                        else if ( $info->loan->delayDays > 0 )
                        {
                            //define o materialGenderId no objeto de empréstimos, para evitar select desnecessário
                            $info->loan->materialGenderId = $info->materialGenderId;
                            $fineValue = null; //limpa pois é um foreach
                            $fineValue = $this->busLoan->calculatesFine( null, $info->loan );
                            $penaltyEndDate = $this->busPenalty->calcultesPenalty($info);
                        }
                        
                        //se tiver multa, registra
                        if ( $fineValue > 0 )
                        {
                            $fineData                = new stdClass();
                            $fineData->value         = $fineValue;
                            $fineData->loanId        = $loan->loanId;
                            $fineData->beginDate     = GDate::now()->getDate(GDate::MASK_DATE_DB);
                            $fineData->fineStatusId  = DEFAULT_VALUE_FINE_INITIAL_STATUS;

                            $this->busFine->setData( $fineData );
                            $this->busFine->insertFine();
                            $fineData->fineId = $this->busFine->fineId;

                            //só pega estado caso não esteja definido
                            if ( !$defaultFineStatus )
                            {
                                $defaultFineStatus = $this->busFineStatus->getFineStatus(DEFAULT_VALUE_FINE_INITIAL_STATUS);
                            }

                            $info->fine->fineStatus = $defaultFineStatus; //define descrição
                            $info->fine = $fineData; //seta objeto da multa no exemplar
                            $this->fine[$this->busFine->fineId] = $info; //adiciona exemplar na relação de multas
                            $info->fineValue = $fineValue;

                            $this->addInformation( _M("@3, foi adicionada a multa no valor de @1 para o empréstimo @2.", $this->module, GUtil::moneyFormat( $this->busFine->value ), $this->busFine->loanId, $loan->personName ) . ' ' . _M('Estado da multa: @1', $this->module, $defaultFineStatus->description ), $addItemNumberMSG );
                        }

                        if ( $penaltyEndDate instanceof GDate )
                        {
                            $penalty = new stdClass();
                            $penalty->personId = $info->loan->personId;
                            $penalty->libraryUnitId = $info->libraryUnitId;
                            $penalty->observation = _M('Penalidade gerada pelo sistema em decorrência do atraso do empréstimo @1',$this->module,$info->loanId);
                            $penalty->internalObservation = _M('Penalidade gerada pelo sistema em decorrência do atraso do empréstimo @1',$this->module,$info->loanId);
                            $penalty->penaltyDate = GDate::now()->getDate(GDate::MASK_DATE_DB); // Data em que a penalidade entrou em vigor
                            $penalty->penaltyEndDate = $penaltyEndDate->getDate(GDate::MASK_DATE_DB); //Data de término da penalidade
                            $penalty->operator = GOperator::getOperatorId();

                            $this->busPenalty->setData($penalty);
                            $this->busPenalty->insertPenalty();
                            $this->addInformation( _M("Foi adicionada penalidade por atraso até @1 para o empréstimo @2. Pessoa: @3.", $this->module, $penaltyEndDate->getDate(GDate::MASK_DATE_USER), $info->loanId, $loan->personName ), $addItemNumberMSG);
                        }

                    }
                }
                //}

                $exemplaryStatus = $exemplaryStatusList[$futureStatus]; //pega objeto do estado futuro
                $exemplaryStatus->meetReserve = $this->busReserve->hasReserve($itemNumber, $exemplaryStatus->exemplaryStatusId);
                
                //verifica se é um estado que atende reserva
                $meetReserve = FALSE;
                
                    if (MUtil::getBooleanValue($exemplaryStatus->meetReserve) == TRUE && $info->hasReserve && $appliedStatus != true)
                    {
                        $this->busOperationReserve = $this->MIOLO->getBusiness($this->module, 'BusOperationReserve');
                            //e se tem reserva

                        //EXECUTA O PROCESSO DE RESERVA ATENDIDA
                        $this->busOperationReserve->clearItemsReserve();
                        $libraryUnit = $this->getLibraryUnit(true);
                        $this->busOperationReserve->setLibraryUnit( $libraryUnit );

                        $finalize = $this->busOperationReserve->meetReserve($itemNumber, $info);

                        if ($finalize)
                        {
                            if ( MUtil::getBooleanValue($data->communicateReserves) )
                            {
                                    //altera status para comunicada a reserva atendida
                                    $communicate = $this->busOperationReserve->communicateReserve($itemNumber, $libraryUnit);
                            }

                            //Define que atendeu a reserva, para nao alterar o estado para disponivel ou outro mais adiante
                                $meetReserve  = TRUE;

                            //Obtem o estado futuro do atendimento da reserva
                                $futureStatus = $this->busRulesForMaterialMovement->getFutureStatus($itemNumber, $currentStatus, ID_OPERATION_MEET_RESERVE, 1);
                        }
                        else
                        {
                            $erros = $this->busOperationReserve->getErrors();

                            foreach ( $erros as $erro )
                            {
                                $this->addError($erro->message, $addItemNumberMSG);
                            }

                            if($exemplaryStatus->exemplaryStatusId !=DEFAULT_EXEMPLARY_STATUS_RESERVADO)
                            {
                                $this->addError(_M('Desculpe, mas não é possível fazer a reserva.', $this->module), $addItemNumberMSG);
                            }
                        }

                        $infos = $this->busOperationReserve->getInformations();

                        foreach ($infos as $inf)
                        {
                            $this->addInformation($inf->message, $addItemNumberMSG);
                        }
                    }
                    else if ( MUtil::getBooleanValue($exemplaryStatus->meetReserve) == TRUE && $appliedStatus == true )
                    {
                        $isLowStatus = MUtil::getBooleanValue( $exemplaryStatus->isLowStatus );
                        
                        if (!$isLowStatus)
                        {
                            if($exemplaryStatus->exemplaryStatusId != 5 or $exemplaryStatus->exemplaryStatusId != 6)
                            {
                                $this->addAlert(_M('Não foi possível atender a reserva, pois o material possui uma alteração de estado agendada.') , $addItemNumberMSG);
                            }
                        }
                    }

                //Se existe uma troca de estado agendada
                if ($appliedStatus)
                {
                    $isLowStatus = MUtil::getBooleanValue( $exemplaryStatus->isLowStatus );
                    
                    if ($isLowStatus)
                    {
                    	$futureStatusDefined = $futureStatusDefined[$itemNumber];
                    	$d           = GDate::now();
                    	$lowDate     = $d->getDate(GDate::MASK_DATE_DB);
                    	$observation = $futureStatusDefined->observation;
                    }
                    else
                    {
                        $lowDate     = NULL;
                        $observation = NULL;
                    }

                    //atualiza baixa
                    $this->busExemplaryControl->updateLow($itemNumber, $lowDate, $observation, TRUE, $info );
                    //define como aplicado
                    $this->busExemplaryFutureStatusDefined->setApplied($itemNumber, $futureStatusTMP->exemplaryFutureStatusDefinedId);
                }

                if ( $futureStatusTMP )
                {
                    // CHECA SE ESTE AGENDAMENTO É ORIUNDO DE UMA REQUISIÃÇÃO E TOMA AS DEVIDAS PROVIDENCIAS
                    $this->confirmRequestChangeStatusExemplary($itemNumber, $futureStatusTMP->exemplaryFutureStatusDefinedId );
                }
                
                // antes de trocar para o estado da regra, deve verificar se atendeu reserva e trocar para o estado de reserva
                if (!$meetReserve && ( $currentStatus != $futureStatus ) )//se for o mesmo estado não precisa atualizar
                {
                    $this->busExemplaryControl->changeStatus( $itemNumber, $futureStatus, $this->getOperator() , $info );
                    //atualiza no exemplar
                    $info->exemplaryStatus = $exemplaryStatusList[$futureStatus];
                }
                
                if ( $isLowStatus )
                {
                    $args = new stdClass();
                    $exemplary = new stdClass();

                    $exemplary->itemNumber = $itemNumber;
                    //Se tiver uma observacao cadastrada no agendamento de alteracao de estado quer dizer que deve enviar e-mail para pessoa se tiver a reserva cancelada.
                    $args->observationSendMail = empty($futureStatusDefined->cancelReserveEmailObservation) ? null:true;
                    $args->observationMail = $futureStatusDefined->cancelReserveEmailObservation;

                    $busOperationReserve = $this->MIOLO->getBusiness($this->module, 'BusOperationReserve');
                    $busOperationReserve->cancelReserveByItemNumber($exemplary, $args);                        
                }
                
                
                //destaca em vermelho todos estados diferentes de disponível
                $futureStatusObj = $exemplaryStatusList[$futureStatus];
                if ( $futureStatusObj->exemplaryStatusId != DEFAULT_EXEMPLARY_STATUS_DISPONIVEL )
                {
                    $futureStatusColor = $futureStatusObj->description;
                }
                else
                {
                    $futureStatusColor = $futureStatusObj->description;
                }
                
                if ($loan->personId)
                {
                    $msg = _M('Sucesso @1! O exemplar foi devolvido e passado para o estado @2.', $this->module, $loan->personId, $futureStatusColor);
                    $this->addInformation( $msg, $addItemNumberMSG );
                    $info->fineValue = $fineValue;
                    
                    $this->finalizedItens[] = $info; //relação de itens finalizados para o recibo
                    
                    $this->personId = $loan->personId;
                }
                else
                {
                    $this->addInformation( _M('Sucesso! O exemplar foi devolvido e passado para o estado @1.', $this->module, $futureStatusColor), $addItemNumberMSG );
                }

                //se tiver tipo de retorno, atualiza a contagem
                if ( $returnTypeId = $this->getReturnType() )
                {
                    $date = GDate::now();
                    $this->busReturnRegister->returnTypeId = $returnTypeId;
                    $this->busReturnRegister->itemNumber   = $itemNumber;
                    $this->busReturnRegister->date         = $date->getDate(GDate::MASK_TIMESTAMP_DB);
                    $this->busReturnRegister->operator     = $this->getOperator();
                    $this->busReturnRegister->insertReturnRegister();
                    
                    $this->returnId = $this->busReturnRegister->returnRegisterId;

                    //pega somente se for necessário e guarda para não precisar pegar novamente
                    if ( !$returnType )
                    {
                        $returnType = $this->busReturnType->getReturnType( $this->getReturnType() );
                    }

                    $this->addInformation( _M('O tipo de retorno foi registrado como @1.', $this->module, $returnType->description), $addItemNumberMSG );
                }
            }
        }
        
        
        //Adicionado na 3.8 para vir o recibo da devolução pelo webservice
        $makeReceipt = $data->returnReceipt || $data->printReceipt ;
        
        //$itens = $this->getFinalizedItens();
        $itens = $this->finalizedItens;

    	//if ( $makeReceipt && count($itens) )
        
        if ( count($itens) )
    	{
            $this->receipt = new GnutecaReceipt();

            foreach ( $itens as $line => $receipt )
            {
                if ( $data->sendReceipt == GnutecaReceipt::RECEIPT_USER_CONFIG )
                {
                    $sendReceipt = MUtil::getBooleanValue($receipt->sendReceipt);
                }
                else
                {
                    $sendReceipt = MUtil::getBooleanValue($data->sendReceipt);
                }

                if ( $data->printReceipt == GnutecaReceipt::RECEIPT_USER_CONFIG )
                {
                    $printReceipt = MUtil::getBooleanValue($receipt->printReceipt);
                }
                else
                {
                    $printReceipt = MUtil::getBooleanValue($data->printReceipt);
                }
                
                $this->receipt->addItem( new ReturnReceipt( $receipt, TRUE, TRUE ) );
            }

            $receipts = $this->receipt->generate();

            //passa as mensagens do recibo para operação de devolução
            if ( is_array( $messages = $this->receipt->getMessages() ) )
            {
                foreach ($messages as $line => $message )
                {
                    $this->addMessage( null, $message);
                }
            }
    	}

        //ao finalizar o processo de devolução limpa o processo de operação que possa ter ficado pindurado
        if ( $_SESSION['personId'] )
        {
            $this->busPerson->removeOperationProcess( $_SESSION['personId'] );
        }
        return true;
    }
    

    function addError($message )
    {
        $this->msgs[] = '[ERRO] - ' . $message;
    }
    
    function addInformation($message )
    {
        $this->msgs[] = '[Informação] - ' . $message;
    }
    
    function addAlert($message )
    {
        $this->msgs[] = '[Aviso] - ' . $message;
    }
    
    function getMessages()
    {
        return implode('CR+LF', $this->msgs);
    }
    
}
?>
