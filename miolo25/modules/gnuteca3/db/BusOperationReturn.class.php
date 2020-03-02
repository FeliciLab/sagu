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
 * Class to manage to return operation of gnuteca.
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 28/07/2008
 *
 **/
$MIOLO = MIOLO::getInstance();
$MIOLO->uses('/classes/receipt/GnutecaReceipt.class.php', 'gnuteca3');

class BusinessGnuteca3BusOperationReturn extends GMessages
{
    protected $operator;
    protected $locationForMaterialMovementId;
    protected $libraryUnitId;
    protected $returnTypeId;
    protected $checkIsDelayAndHasReserve;
    protected $items;
    protected $console = false;

    private   $finalizedItens   = null;
    private   $fine             = null;
    public $receipt          = null;//objeto que guarda todos os recibos gerados nesta operação

    public $busExemplaryControl;
    public $busExemplaryFutureStatusDefined;
    public $busExemplaryStatusHistory;
    public $busExemplaryStatus;
    public $busLocationForMaterialMovement;
    public $busLibraryUnit;
    public $busLoan;
    public $busReserve;
    public $busReturnRegister;
    public $busReturnType;
    public $busFine;
    public $busFineStatus;
    public $busOperationReserve;
    public $busOperation;
    public $busPerson;
    public $MIOLO;
    public $module;
    public $busMaterial;
    public $busSearchFormat;
    public $busPenalty;
    public $busPersonConfig;
    private   $exemplarys; //guarda exemplares a serem adicionados, DADO TEMPORÁRIO NÃO UTILIZE ISTO PARA PEGAR RELAÇÃO


    public function __CONSTRUCT()
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busLocationForMaterialMovement = $this->MIOLO->getBusiness($this->module, 'BusLocationForMaterialMovement');
        $this->busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busLoan = $this->MIOLO->getBusiness($this->module, 'BusLoan');
        $this->busReserve = $this->MIOLO->getBusiness($this->module, 'BusReserve');
        $this->busReturnRegister = $this->MIOLO->getBusiness($this->module, 'BusReturnRegister');
        $this->busReturnType = $this->MIOLO->getBusiness($this->module, 'BusReturnType');
        $this->busFine = $this->MIOLO->getBusiness($this->module, 'BusFine');
        $this->busFineStatus = $this->MIOLO->getBusiness($this->module, 'BusFineStatus');
        $this->busExemplaryFutureStatusDefined  = $this->MIOLO->getBusiness($this->module, 'BusExemplaryFutureStatusDefined');
        $this->busExemplaryStatusHistory = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatusHistory');
        $this->busRulesForMaterialMovement = $this->MIOLO->getBusiness($this->module, 'BusRulesForMaterialMovement');
        $this->busExemplaryStatus = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
        $this->busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        $this->busOperation = $this->MIOLO->getBusiness($this->module, 'BusOperation');
        $this->busPerson = $this->MIOLO->getBusiness($this->module, 'BusPerson');
        $this->busSearchFormat = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        $this->busPenalty = $this->MIOLO->getBusiness($this->module, 'BusPenalty');
        $this->busPersonConfig = $this->MIOLO->getBusiness($this->module, 'BusPersonConfig');
    }


    /**
     * Define o local da devolução. A função verificará se o id passado existe.
     *
     * @param $locationForMaterialMovementId o id do local a ser setado
     *
     */
    public function setLocation($locationForMaterialMovementId)
    {
        $location = $this->busLocationForMaterialMovement->getLocationForMaterialMovement($locationForMaterialMovementId, true);

        if ($location->locationForMaterialMovementId)
        {
            $this->locationForMaterialMovementId 		= $locationForMaterialMovementId;
            $this->locationForMaterialMovement 			= $location;
            $_SESSION['locationForMaterialMovementId'] 	= $locationForMaterialMovementId;
            $_SESSION['locationForMaterialMovement']   	= $location;
            return true;
        }
        else
        {
            return false;
        }
    }


    /**
     * Return the seted location for material movement
     *
     * @param boolean $object if is to return object or not
     * @return the required data
     */
    public function getLocation($object = FALSE)
    {
        if ( !$object)
        {
            $location =  $this->locationForMaterialMovementId;

            if ( !$location )
            {
                $location = $_SESSION['locationForMaterialMovementId'];
            }
        }
        else
        {
            $location =  $this->locationForMaterialMovement;

            if ( !$location )
            {
                $location = $_SESSION['locationForMaterialMovement'];
            }
        }
        return $location;
    }


   	/**
   	 * Define the Library Unit of this operation (and verify if exists)
   	 *
   	 * @param integer $libraryUnitId the id of the library unit
   	 * @return boolean if is seted or not
   	 */
    public function setLibraryUnit($libraryUnitId)
    {
        $libraryUnit = $this->busLibraryUnit->getLibraryUnit( $libraryUnitId, true);

        if ($libraryUnit->libraryUnitId)
        {
            $this->libraryUnitId        = $libraryUnitId;
            $this->libraryUnit          = $libraryUnit;
            $_SESSION['libraryUnit2']    = $libraryUnit;
            $_SESSION['libraryUnitId2']  = $libraryUnitId;
            return true;
        }
        else
        {
            return false;
        }
    }


    public function getLibraryUnit($object=FALSE)
    {
        if ( !$object)
        {
            $libraryUnit = $this->libraryUnitId;

            if ( !$libraryUnit)
            {
                $libraryUnit = $_SESSION['libraryUnitId2'];
            }
        }
        else
        {
            $libraryUnit = $this->libraryUnit;

            if ( !$libraryUnit )
            {
            	$libraryUnit = $_SESSION['libraryUnit2'];
            }
        }
        return $libraryUnit;
    }


    public function setReturnType($returnTypeId)
    {
    	if ($returnTypeId)
    	{
            $this->returnTypeId = $returnTypeId;
            $_SESSION['returnTypeId'] = $returnTypeId;
            return true;
    	}
    	else
    	{
    		return false;
    	}
    }


    public function getReturnType()
    {
    	if ($this->returnTypeId)
    	{
    		return $this->returnTypeId;
    	}
    	return $_SESSION['returnTypeId'];
    }


    public function setCheckIsDelayAndHasReserve($check)
    {
        $this->checkIsDelayAndHasReserve = $check;
    }


    public function getCheckIsDelayAndHasReserve()
    {
        return $this->checkIsDelayAndHasReserve;
    }


    public function setOperator($operator)
    {
        $this->operator = $operator;
        return true;
    }


    public function getOperator()
    {
    	$operator = $this->operator;
    	if (!$operator )
    	{
    		$operator = GOperator::getOperatorId();
    	}

        return $operator;
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
            $this->addError( _M('Exemplar não existe.', $this->module) , $addItemNumberMSG);
            return false;
        }

        $libraryUnit = $this->getLibraryUnit(true);//pega objeto na sessão

        if ( $exemplary->libraryUnitId != $this->getLibraryUnit() )
        {
            $this->addError( _M('Exemplar não pertence a biblioteca @1.', $this->module, $libraryUnit->libraryName), $addItemNumberMSG);
            return false;
        }

        $loan = $this->busLoan->getLoanOpen($itemNumber);

        if (!$loan)
        {
            $this->addInformation( _M("Não há empréstimo em aberto.", $this->module), $addItemNumberMSG );
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

    /**
     * Após a checagem do exemplar alguns dados são adicionados a um array, para evitar de buscá-los novamente.
     *
     * Essa funçao retorna este dados temporário, para o mesmo fim.
     *
     * @param string $itemNumber
     * @return stdClass
     */
    public function getTemporaryExemplar( $itemNumber )
    {
        return $this->exemplarys[$itemNumber];
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


	public function deleteItemNumber($itemNumber)
    {
    	unset( $_SESSION['itemsReturn'][ $itemNumber ] );
    }

    public function getItemsReturn()
    {
    	return $_SESSION['itemsReturn'];
    }

    public function clearItemsReturn()
    {
    	unset($_SESSION['returnTypeId']);
    	unset($_SESSION['itemsReturn']);
    }


    /**
     * Finaliza operação devolução
     *
     * @param object stdClass miolo request object
     * @return unknown
     */
    public function finalize( $data = null , $generateReceipt = true )
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
            $this->addInformation( _M('Não há itens para finalizar o processo de devolução.', $this->module) );
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
                    $this->addInformation( _M("Não há empréstimos em aberto.", $this->module), $addItemNumberMSG );
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
                    $this->addAlert( _M('Material possui <b>agendamento</b>, mas esta reservado, então o agendamento é <b>desconsiderado</b>.'), $addItemNumberMSG);
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
                            $this->addError( _M('Não foi possível encontrar o estado futuro no histórico do estado do exemplar.', $this->module, $itemNumber), $addItemNumberMSG );
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

                            $this->addError( _M('Não foi possível encontrar o estado futuro com a regra <b>@1 - @2 - @3</b>.', $this->module, $statusObj->description, $operationReturnObj->description, $localObj->description), $addItemNumberMSG );
                            continue;
                        }
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

                            $this->addInformation( _M("Foi adicionada a multa no valor de <b>@1</b> para o empréstimo <b>@2</b>. Pessoa: <b>@3</b>.", $this->module, GUtil::moneyFormat( $this->busFine->value ), $this->busFine->loanId, $loan->personName ) . ' ' . _M('Estado da multa: <b>@1</b>', $this->module, $defaultFineStatus->description ), $addItemNumberMSG );
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
                            $this->addInformation( _M("Foi adicionada penalidade por atraso até <b>@1</b> para o empréstimo <b>@2</b>. Pessoa: <b>@3</b>.", $this->module, $penaltyEndDate->getDate(GDate::MASK_DATE_USER), $info->loanId, $loan->personName ), $addItemNumberMSG);
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
                                $this->addError(_M('Não é possível fazer a reserva.', $this->module), $addItemNumberMSG);
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
                    $futureStatusColor = "<FONT COLOR = RED>".$futureStatusObj->description."</FONT COLOR>";
                }
                else
                {
                    $futureStatusColor = $futureStatusObj->description;
                }
                
                if ($loan->personId)
                {
                    $msg = _M('O exemplar foi devolvido da pessoa <b>@1</b> e passado para o estado <b>@2</b>.', $this->module, $loan->personId, $futureStatusColor);
                    $this->addInformation( $msg, $addItemNumberMSG );
                    $info->fineValue = $fineValue;

                    $this->finalizedItens[] = $info; //relação de itens finalizados para o recibo
                }
                else
                {
                    $this->addInformation( _M('O exemplar foi devolvido e passado para o estado <b>@1</b>.', $this->module, $futureStatusColor), $addItemNumberMSG );
                }

                
                //Caso tenha o sistema de integração com RFID, tenta ativar o bit
                if(RFID_INTEGRATION == DB_TRUE)
                {
                    $r = RFID::addBitAgainstTheft();
                    
                    if(!is_array($r))
                    {
                        $fezOp = true;
                    }
                    else
                    {
                        $fezOp = false;
                        
                        foreach ($r as $rr)
                        {
                            $msgBit = $rr;
                        }
                    }

                    if($fezOp)
                    {
                        $this->addInformation("Ativado o bit <b>anti-furto</b> do exemplar.");
                    }else
                    {
                        $this->addError("Não foi possível ativar o <b>anti-furto</b>! Após solucionado o problema, vá até: <b>Circulação de material</b>-><b>Empréstimo</b>. <br> Consultando pelo <b>número do exemplar</b>, <b>ative manualmente</b> o anti-furto.");
                        $this->addError($msgBit);
                    }
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

                    //pega somente se for necessário e guarda para não precisar pegar novamente
                    if ( !$returnType )
                    {
                        $returnType = $this->busReturnType->getReturnType( $this->getReturnType() );
                    }

                    $this->addInformation( _M('O tipo de retorno foi registrado como <b>@1</b>.', $this->module, $returnType->description), $addItemNumberMSG );
                }
            }
        }

        $makeReceipt = $data->returnReceipt || $data->printReceipt ;

        $itens = $this->getFinalizedItens();

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

                $this->receipt->addItem( new ReturnReceipt( $receipt, $sendReceipt, $printReceipt ) );
            }

            if ( $generateReceipt )
            {
                $receipts = $this->receipt->generate();
            }

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



    /**
     * Este metodo é responsavel por confirmar as requisições de alteração de estado de material
     *
     */
    protected function confirmRequestChangeStatusExemplary($itemNumber, $exemplaryFutureStatusDefinedId = null)
    {
        if(is_null($exemplaryFutureStatusDefinedId))
        {
            return;
        }

        $busRequest             = $this->MIOLO->getBusiness($this->module, "BusRequestChangeExemplaryStatus"            );
        $busOperationRequest    = $this->MIOLO->getBusiness($this->module, "BusOperationRequestChangeExemplaryStatus"   );
        $busRequestComposition  = $this->MIOLO->getBusiness($this->module, "BusRequestChangeExemplaryStatusComposition" );

        $requstsIds = $busRequestComposition->getRequestIdFromFutureStatus($exemplaryFutureStatusDefinedId);

        if(!$requstsIds)
        {
            return ;
        }

        foreach ($requstsIds as $values)
        {
            $rid = $values[0];

            $aproveJustOne = $busRequest->checkAproveJustOne($rid);

            // VERIFICA SE È APROVE JUST ONE E SE JA TEM ALGUM ITEM APROVADO > CONTINUA NO PROXIMO REQUISIÇÂO
            if($aproveJustOne && $busRequestComposition->checkOneApproved($rid))
            {
                continue;
            }

            if($busRequestComposition->appliedItemNumberForRequest($rid, $itemNumber))
            {
                // ALTERA ESTADO PARA CONFIRMADA
                $busRequest->requestChangeExemplaryStatusStatusId = REQUEST_CHANGE_EXEMPLARY_STATUS_CONFIRMED;
                $busRequest->requestChangeExemplaryStatusId = $rid;
                $busRequest->changeStatusRequestChangeExemplaryStatus($this->getOperator());

                if($aproveJustOne)
                {
                    //$busRequestComposition->applyOthers($rid, $itemNumber);

                    // BUSCA OS OUTRO EXEMPLARES DA COMPOSIÇÂO
                    $others = $busRequestComposition->getOthers($rid, $itemNumber);
                    if($others)
                    {
                        foreach ($others as $valuesOther)
                        {
                            // DESAPROVA OS OUTROS EXEMPLARES DA COMPOSIÇÂO
                            $busOperationRequest->clean();
                            $busOperationRequest->getRequest($rid);
                            $busOperationRequest->disapproveCompositionForRequest($rid, $valuesOther[0]);
                        }
                    }
                    continue;
                }
            }
        }
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    function getFinalizedItens()
    {
        return $this->finalizedItens;
    }


    /**
     * retorna as multas
     *
     * @return simple array
     */
    function getFine()
    {
        return $this->fine;
    }

}
?>
