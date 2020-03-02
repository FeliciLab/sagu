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
 * Class created on 26/11/2013
 * 
 **/

$MIOLO = MIOLO::getInstance();
$module = MIOLO::getCurrentModule();
$MIOLO->usesBusiness($module, 'BusOperationLoan');

class BusinessGnuteca3BusOperationLoanSip extends BusinessGnuteca3BusOperationLoan
{
    public $libraryUnitId;
    public $location;
    public $loanNum;
    public $isRenew;
    public $busOperationRenewSip;
    public $operationDate;
    public $msgs;
    
    public function __construct() 
    {
        parent::__construct();
        $this->busOperationRenewSip = $this->MIOLO->getBusiness($this->module, 'BusOperationRenewSip');
    }
    
    public function addItemNumber($itemNumber, $loanTypeId, $pessoa, $offline = FALSE)
    {   
        $this->fine = null;
        $busRight = $this->MIOLO->getBusiness($this->module, 'BusRight');
        $busLoanType = $this->MIOLO->getBusiness($this->module, 'BusLoanType');

        $addItemNumberMSG['itemNumber'] = $itemNumber;

        $exemplary = $this->busExemplaryControl->getExemplaryControl($itemNumber);

        if ( !$exemplary->itemNumber )
        {
            $this->addError('Este exemplar não existe. Exemplar: ' . $itemNumber . '. Consulte informações no balcão de atendimento.');
            return false;
        }
        
        $busSearchFormat = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        $addItemNumberMSG['searchData'] = $busSearchFormat->getFormatedString($exemplary->controlNumber, MATERIAL_MOVIMENT_SEARCH_FORMAT_ID, 'search');
        
        if ( $offline )
        {
            $exemplary->loanTypeId = ID_LOANTYPE_OFFLINE;
            
        }
        else
        {
            $exemplary->loanTypeId = ID_LOANTYPE_DEFAULTSIPEQUIPAMENT;
        }
        
        
        $loanTypeList = $busLoanType->listLoanType();
        $exemplary->loanTypeDescription = $loanTypeList[$loanTypeId][1];
        $exemplary->searchData = $addItemNumberMSG['searchData'];

        
        $itensLoan = $this->getItemsLoan();     
        $itensLoan = $itensLoan ? $itensLoan : array();
        
        //Checa se o exemplar já está na lista
        if ( array_key_exists($itemNumber, $itensLoan ) )
        {
            $this->addError("Este exemplar já está na lista de empréstimos. Exemplar: ". $itemNumber);
            return false;
        }

        // checa se o exemplar pertence da unidade correta, passada por parametro
        if ( $exemplary->libraryUnitId != $this->libraryUnitId )
        {
            $this->addError(_M('Este material pertence à biblioteca de @1. Consulte informações no balcão de atendimento.', $this->module, $exemplary->libraryName), $addItemNumberMSG);
            return false;
        }

        $person = $this->getPerson();
        
        $busExemplaryStatus = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
        $status = $busExemplaryStatus->getExemplaryStatus($exemplary->exemplaryStatusId, true);
        $loan = $this->busLoan->getLoanOpen($itemNumber); //guarda os empréstimos abertos
        
        //pega empréstimos atuais e compara com politica de renovação, se não puder renovar e tiver empréstimos hoje bloqueia o empréstimo
        //situação que ocorre quando a pessoa não tem permissão de renovação então tenta devolver e retirar o livro novamente no mesmo dia
        $this->busLoan->personId = $person->personId;
        $this->busLoan->itemNumber = $itemNumber;
        $this->busLoan->returnDateS = GDate::now()->getDate(GDate::MASK_DATE_DB);

        $loanToday = $this->busLoan->searchLoan(true);
        
        if ( is_array($loanToday) && is_array($person->policy) )
        {            
            foreach ( $person->policy as $line => $info )
            {
                //Valida se politica de circulação do material atende ao grupo de privilégio/genero do material/vinculo
                if ( $info->privilegeGroupId == $person->privilegeGroupId && $info->materialGenderId == $exemplary->materialGenderId && $info->linkId == $person->linkId )
                {
                    $selectPolicy = $info;
                }
            }
            
            if(is_null($selectPolicy))
            {
                $this->addError(_M('Não há políticas registradas para este material.', $this->module), $addItemNumberMSG);
                return false;
            }
            else
            {
                if ( $selectPolicy->renewalLimit == 0 )
                {
                    $this->addError(_M('Tentativa de renovação de material. Usuário sem limite de renovações.', $this->module), $addItemNumberMSG);
                    return false;
                }
            }
        }
        
        $this->busLoan->personId = '';
        $this->busLoan->itemNumber = '';
        $this->busLoan->loanDate = '';

        $reserveStatusId = array( ID_RESERVESTATUS_REQUESTED, ID_RESERVESTATUS_ANSWERED, ID_RESERVESTATUS_REPORTED, ID_RESERVESTATUS_CONFIRMED );

        $materialGenderId = $exemplary->materialGenderId;
        $materialGender = $exemplary->materialGenderDescription;
        
        if ( $loanTypeId == ID_LOANTYPE_DEFAULTSIPEQUIPAMENT )
        {
            if ( !$materialGenderId )
            {
                $this->addError(_M('Não foi possível identificar o gênero do material. Consulte informações no balcão de atendimento.', $this->module), $addItemNumberMSG);
                return false;
            }

            $right = $busRight->hasRight($exemplary->libraryUnitId, $person->linkId, $materialGenderId, ID_OPERATION_LOAN);
            
            if ( !$right )
            {
                $this->addError(_M('O seu grupo @1 não possui direitos para retirar materiais de gênero @2. Consulte informações no balcão de atendimento.', $this->module, $person->link->description, $materialGender), $addItemNumberMSG);
                return false;
            }

            $fine = $person->fine;
            
            if ( $fine )
            {
                $returnFalse = false;

                foreach ( $fine as $value )
                {
                    //Se a penalidade é válida para todas as bibliotecas o sistema checa as polí­ticas da unidade padrão
                    if ( !$value->libraryUnitId )
                    {
                        $value->libraryUnitId = $this->getLibraryUnit();
                    }

                    $right = $busRight->hasRight($value->libraryUnitId, $person->linkId, $materialGenderId, ID_OPERATION_LOAN_FINE);

                    if ( !$right )
                    {
                        $this->fine[$value->fineId] = $this->busFine->getLoanId($value->fineId);
                        $returnFalse = true;
                    }
                }

                if ( $returnFalse )
                {
                    $this->addError(_M('Desculpe @1, você possui multas em aberto. Você é do grupo @2 e não tem direito de retirar com multas. Consulte informações no balcão de atendimento.', $this->module, $person->personId, $person->link->description), $addItemNumberMSG);
                    return false;
                }
            }
            
            $penalty = $person->penalty;
            if ( $penalty )
            {
                foreach ( $penalty as $value )
                {
                    $right = $busRight->hasRight($value->libraryUnitId, $person->linkId, $materialGenderId, ID_OPERATION_LOAN_PENALTY);
                    if ( !$right )
                    {
                        $this->addError(_M('Desculpe @1, mas você possui penalidades em aberto. Você é do grupo @2 e não tem direito de retirar com penalidades. Consulte informações no balcão de atendimento.', $this->module, $person->personId, $person->link->description), $addItemNumberMSG);
                        return false;
                    }
                }
            }

            $policy = $person->policy;
            if ( $policy )
            {
                foreach ( $policy as $value )
                {
                    if ( $value->delayLoan )
                    {
                        $right = $busRight->hasRight($exemplary->libraryUnitId, $person->linkId, $value->materialGenderId, ID_OPERATION_LOAN_DELAY_LOAN);

                        if ( !$right )
                        {
                            $this->addError(_M('@1, você possui materiais em atraso. O grupo @2 não possui direito a retirar com materiais em atraso. Consulte informações no balcão de atendimento.', $this->module, $person->personId, $person->link->description), $addItemNumberMSG);
                            return false;
                        }
                    }
                }
            }

            if ( !MUtil::getBooleanValue($status->executeLoan) )
            {
                $this->addError(_M('Desculpe, mas os materiais no estado "@1" não podem ser emprestados. Consulte informações no balcão de atendimento.', $this->module, $status->description), $addItemNumberMSG);
                return false;
            }

            $policyFound = false;
            foreach ( $person->policy as $policy )
            {
                if ( $materialGenderId == $policy->materialGenderId )
                {
                    
                    $policyFound = true;
                    //Precisa obter lista de itens emprestados do tipo materialGender
                    
                    // Comentado pois no equipamento não utiliza sessão
                    //$tmpList = $this->getItemsLoan($materialGenderId);
                    
                    $amountTmpMaterial = 1;
                    /* Atualizado para o valor 1, pois realiza apenas um empréstimo por vez
                    if ( is_array($tmpList) )
                    {
                        foreach ( $tmpList as $k => $itensLoan )
                        {
                            //verifica se o empréstimo é do tipo padrão
                            if ( $itensLoan->loanTypeId == ID_LOANTYPE_DEFAULTSIPEQUIPAMENT )
                            {
                                $amountTmpMaterial++;
                            }
                        }
                    }
                     */
                    
                    if ( !$loan || $loan->personId != $person->personId )
                    {

                        if ( ($policy->loanLimit - $policy->loanDefault - $amountTmpMaterial) < 0 )
                        {
                            $this->addError(_M('Desculpe, mas o limite de empréstimo do grupo @1 e gênero @2 foi excedido. Consulte informações no balcão de atendimento.', $this->module, $person->link->description, $materialGender), $addItemNumberMSG);
                            return false;
                        }

                        if ( $person->generalPolicy ) //política geral
                        {
                            //verifica quantidade de materias
                            $total = $this->getItemsLoan(); //pega todos os materiais

                            if ( is_array($total) )
                            {
                                $amountTmpMaterialGeneral = 0;
                                foreach ( $total as $j => $val )
                                {
                                    if ( $val->loanTypeId == ID_LOANTYPE_DEFAULTSIPEQUIPAMENT ) //se não for momentâneo e nem forçado
                                    {
                                        $amountTmpMaterialGeneral++;
                                    }
                                }
                            }

                            //soma os empréstimos da base
                            $loansOpen = $this->busLoan->getLoanOpenByPerson($person->personId); //soma os empréstimo aberto
                            if ( $loansOpen )
                            {
                                foreach ( $loansOpen as $i => $loansDetail )
                                {
                                    if ( $loansDetail->loanTypeId == ID_LOANTYPE_DEFAULTSIPEQUIPAMENT )
                                    {
                                        $amountTmpMaterialGeneral++;  //soma os empréstimos da listagem com os empréstimo da base que são empréstimo padrão
                                    }
                                }
                            }

                            if ( ($person->generalPolicy->loanGeneralLimit - $amountTmpMaterialGeneral) <= 0 )
                            {
                                $this->addError(_M('Desculpe, mas o limite total de empréstimo do grupo @1 foi excedido. Consulte informações no balcão de atendimento.', $this->module, $person->link->description), $addItemNumberMSG);
                                return false;
                            }
                        }
                    }
                }
            }
            
            //Verifica se localizou políticas para o material
            if ( !$policyFound )
            {
                $this->addError(_M('O grupo @1, não possui políticas para materiais do gênero @2. Consulte informações no balcão de atendimento.', $this->module, $person->link->description, $materialGender), $addItemNumberMSG);
                return false;
            }

            if ( $status->isReserveStatus )
            {
                //Verifica se tem reserva atendida e comunicada e se a composição esta confirmada
                $reserveStatusId = array( ID_RESERVESTATUS_ANSWERED, ID_RESERVESTATUS_REPORTED );
                $reserve = $this->busReserve->getReservesOfExemplary($itemNumber, $reserveStatusId, true);

                if ( $reserve )
                {
                    if ( $reserve[0]->personId == $person->personId )
                    {
                        $concludesReserve = true;
                    }
                    else
                    {
                        $this->addError(_M('Este exemplar possui reserva e não pode ser emprestado.', $this->module, "{$person->personId} - {$person->personName}"), $addItemNumberMSG);
                        return false;
                    }
                }
            }
            
            if ( $loan )
            {
                //Emprestado para o titular. Executará renovação
                if ( $loan->personId == $person->personId )
                {
                    
                    if($offline)
                    {
                        //$this->busRenew->renewTypeId = ID_RENEWTYPE_OFFLINE;
                        $this->busOperationRenewSip->setRenewType(ID_RENEWTYPE_OFFLINE);
                    }
                    else
                    {
                        $this->busOperationRenewSip->setRenewType(ID_RENEWTYPE_DEFAULTSIPEQUIPAMENT);
                        //$this->busRenew->renewTypeId = ID_RENEWTYPE_DEFAULTSIPEQUIPAMENT;
                    }
                    
                    $renewLoan = $this->busOperationRenewSip->checkLoan($loan->loanId);
                    
                    if ( !$renewLoan )
                    {
                        $erros = $this->busOperationRenewSip->getErrors();

                        foreach ( $erros as $erro )
                        {
                            $msgErros[] = $erro->message;
                        }
                        
                        $this->addError(_M('Desculpe, mas não é possivel renovar o material: ', $this->module) . implode('. ', $msgErros), $addItemNumberMSG);
                        return false;
                    }
                    
                    $this->busOperationRenewSip->addLoan($renewLoan);
                    //Se não deu erro, concluirá a renovação no finalize()
                    $concludesRenew = true;
                    $this->isRenew = true;
                }
                else
                {
                    $doReturn = true;
                }

                //Verifica se tem reserva solicitada
                $reserveStatusId = array( ID_RESERVESTATUS_REQUESTED );
                $reserve = $this->busReserve->getReservesOfExemplary($itemNumber, $reserveStatusId);

                if ( ($reserve) && ($reserve[0]->personId != $person->personId) )
                {
                    $this->addError(_M('Este exemplar possui uma reserva. Consulte informações no balcão de atendimento.', $this->module), $addItemNumberMSG);
                    return false;
                }
            }

            if ( ($person->policy[0]->loanDate) && ($reserve) )
            {
                $returnForecastDate = $person->policy[0]->loanDate;
            }
            else
            {
                $returnForecastDate = GDate::now();
                $returnForecastDate->addDay($person->policy[0]->loanDays);
                $returnForecastDate = $returnForecastDate->getDate(GDate::MASK_DATE_DB);
            }
        }

        if ( $loanTypeId == ID_LOANTYPE_OFFLINE )
        {
            
            $reserveStatusId = array( ID_RESERVESTATUS_REQUESTED, ID_RESERVESTATUS_ANSWERED, ID_RESERVESTATUS_REPORTED );

            //O exemplar possui empréstimo em aberto
            if ( $loan )
            {
                $this->addError(_M('Desculpe, mas este material já está emprestado. Não será possível retirá-lo.', $this->module));
                return false;
            }
            
            //Grupo do usuario nao pode retirar materiais do gênero
   
            
            //Exemplares de determinado estado não podem ser retirados 
            

            $returnForecastDate = MIOLO::_REQUEST(returnForecastDate);
        }

        $exemplary->status = $status;
        $exemplary->concludesRenew = $concludesRenew;
        $exemplary->doReturn = $doReturn;
        $exemplary->concludesReserve = $concludesReserve;
        //$exemplary->requestedReserve    = $requestedReserve;
        $exemplary->returnForecastDate = $returnForecastDate;

        $person = $this->getPerson();

        $location = $this->getLocation(true);
        
        $ok = $this->_addItemNumber($exemplary);
        
        if ( !$ok )
        {
            $this->addError(_M('Este exemplar já está na lista de empréstimos.', $this->module), $addItemNumberMSG);
            return false;
        }
        return $ok;
    }
    
    
    public function finalize($formData = null, $fazRen)
    {
        
        $module = MIOLO::getCurrentModule();
        $person = $this->getPerson();
        
        //Limpa a variável
        unset($selectedPolicy->forecastDate);

        // verifica se ha itens na lista.
        $items = $this->getItemsLoan();
        
        if ( !is_array($items) )
        {
            $this->addInformation(_M('Não há itens adicionados para finalizar o processo de empréstimo.', $module));
        }

        //cria objeto de recibo
        $receiptObject = new GnutecaReceipt();
        $busLoanType = $this->MIOLO->getBusiness($this->module, 'BusLoanType');
        
        foreach ( $items as $line => $info )
        {            
            $finalizeMSG = null;

            $finalizeMSG['itemNumber'] = $info->itemNumber;
            $finalizeMSG['title'] = $info->title;
            $finalizeMSG['author'] = $info->author;

            $itemNumber = $info->itemNumber;
            $currentStateId = $info->status->exemplaryStatusId;
            $location = $this->locationForMaterialMovementId;
            
            $loanType = $busLoanType->listLoanType();

            $futureStatus = $this->busRulesForMaterialMovement->getFutureStatus($itemNumber, $currentStateId, ID_OPERATION_LOAN, $location);
            
            if ( !$futureStatus )
            {
                $rule = _M('Estado atual: @1, Operação: @2 e Local: @3', $module, $currentStateId . ' -  ' . $info->status->description, ID_OPERATION_LOAN . ' - ' . $loanType[ID_OPERATION_LOAN][1], $this->getLocation() . ' - ' . $this->getLocation(true)->description, $finalizeMSG);
                $this->addError(_M('Não foi possível localizar um estado futuro para o exemplar. @1. Consulte informações no balcão de atendimento.', $module, $rule), $finalizeMSG);
                //$this->deleteItemNumber($itemNumber); //remove o item da lista para não gerar recibo
                continue;
            }
            
            //Marcado como renovação
            if ( $info->concludesRenew )
            {
                
                if($fazRen == false)
                {
                    //ERRO, equipamento não faz renovação
                    $this->addError(_M('Desculpe, mas não é possível renovar material neste terminal.'));
                }
                
                
                $this->busOperationRenewSip->finalize($info->itemNumber);
                $this->loanNum = $this->busOperationRenewSip->loanID;
                
                $this->operationDate = $this->busOperationRenewSip->operationDate;
                $retorno['screenMsg'] = $this->busOperationRenewSip->msgs;
                
                
                $erros = $this->busOperationRenewSip->getErrors();
                //$this->renewId = $busOperationRenew->renewId;
                
                
                //se contiver erros falha a operação
                if ( is_array($erros) )
                {
                    foreach ( $erros as $erro )
                    {
                        $msgErros[] = $erro->message;
                    }

                    $this->addError(_M('Desculpe, mas não é possível renovar o material: ', $this->module) . implode('. ', $msgErros), $finalizeMSG);
                }

                $infos = $this->busOperationRenewSip->getInformations();
                $loanId = $this->busOperationRenewSip->getLoanIdFromItemNumber($info->itemNumber);
                
                if ( $loanId )
                {
                    $renewLoan = $this->busOperationRenewSip->checkLoan($loanId);
                    
                    if ( !$renewLoan )
                    {
                        $erros = $this->busOperationRenewSip->getErrors();

                        foreach ( $erros as $erro )
                        {
                            $msgErros[] = $erro->message;
                        }
                        
                        $this->addError(_M('Desculpe, mas não é possivel renovar o material: ', $this->module) . implode('. ', $msgErros), $addItemNumberMSG);
                        return false;
                    }
                    
                    
                    $returnForecastDate = new GDate($this->busLoan->getReturnForecastDate($loanId));
                    $returnForecastDate = $returnForecastDate->getDate(GDate::MASK_DATE_USER);
                }

                $finalizeMSG['returnForecastDate'] = $returnForecastDate;

                foreach ( $infos as $inf )
                {
                    $this->addInformation($inf->message, $finalizeMSG);
                }

                $this->busOperationRenewSip->clearMessages();
                
                //obtem os recibos da operação de renovação
                $renewReceipt = $this->busOperationRenewSip->receipt->getItens();
                $renewReceipt = $renewReceipt['LoanReceipt'];
                foreach ( $renewReceipt as $line => $receipt )
                {
                    $receiptObject->addItem($receipt);
                }
                
                $this->busOperationRenewSip->receipt->setItens(null);
                
                continue;
            }
            
            
            
            //Devolve o material que está no nome de outro usuário
            if ( $info->doReturn )
            {
                $busOperationReturn = $this->MIOLO->getBusiness($this->module, 'BusOperationReturn');
                $busOperationReturn->clearItemsReturn();
                $busOperationReturn->checkItemNumber($itemNumber);
                $busOperationReturn->addItemNumber($itemNumber);
                $hasreserve = $this->busReserve->hasReserve($itemNumber, array( ID_RESERVESTATUS_REQUESTED ));
                //não chama o generate dos recibos da operação de devolução evitando assim geração de dois recibos
                $finalize = $busOperationReturn->finalize($formData, false);

                if ( !$finalize )
                {
                    $erros = $busOperationReturn->getErrors();

                    foreach ( $erros as $erro )
                    {
                        $this->addError($erro->message);
                    }
                }
                else
                {
                    //obtem os recibos da operação de devolução
                    $returnReceipt = $busOperationReturn->receipt->getItens();
                    $returnReceipt = $returnReceipt['ReturnReceipt'];

                    foreach ( $returnReceipt as $line => $receipt )
                    {
                        $receiptObject->addItem($receipt);
                    }
                }

                $infos = $busOperationReturn->getInformations();

                foreach ( $infos as $inf )
                {
                    $this->addInformation($inf->message, $finalizeMSG);
                }
            }

            $hasreserve = $this->busReserve->hasReserve($itemNumber, array( ID_RESERVESTATUS_ANSWERED, ID_RESERVESTATUS_REQUESTED ));

            if ( ($info->concludesReserve) || ($hasreserve) )
            {
                //Troca o estado da reserva para concluí­da
                //Pega a reserva
                $reserveStatusId = array( ID_RESERVESTATUS_ANSWERED, ID_RESERVESTATUS_REPORTED, ID_RESERVESTATUS_REQUESTED );
                $reserve = $this->busReserve->getReservesOfExemplary($itemNumber, $reserveStatusId, null, null, 'A.reserveId', $this->getPerson()->personId);
                $reserve = $reserve[0];

                if ( $reserve )
                {
                    if ( $reserve->personId == $this->getPerson()->personId )
                    {
                        // Cancela a reserva, se usuário estiver retirando o exemplar que não está confirmado na reserva.
                        if ( $reserve->isConfirmed == DB_FALSE && $reserve->reserveStatusId != ID_RESERVESTATUS_REQUESTED )
                        {
                            $this->busOperationReserve->cancelReserve($reserve->reserveId);
                            $this->addAlert(_M('Uma reserva foi cancelada.', $this->module), $finalizeMSG);
                        }
                        else
                        {
                            // Caso esteja solicitada, confirma o exemplar.
                            if ( $reserve->reserveStatusId == ID_RESERVESTATUS_REQUESTED )
                            {
                                $this->busReserveComposition->reserveId = $reserve->reserveId;
                                $this->busReserveComposition->itemNumber = $reserve->itemNumber;
                                $this->busReserveComposition->isConfirmed = DB_TRUE;
                                $this->busReserveComposition->updateComposition();
                            }

                            //Conclui a reserva
                            $this->busReserve->changeReserveStatus($reserve->reserveId, ID_RESERVESTATUS_CONFIRMED, null); //operator
                            $this->addAlert(_M('Uma reserva foi confirmada.', $this->module), $finalizeMSG);
                        }
                    }
                }
            }

            if ( $info->requestedReserve )
            {
                //TODO Implementar quando houver empréstimo forçado
            }

            $policy = $person->policy;

            if ( is_array($policy) ) //senão pega a anterior pois ainda é valida
            {
                foreach ( $policy as $l => $i )
                {
                    if ( $i->materialGenderId == $info->materialGenderId )
                    {
                        $selectedPolicy = $i;
                    }
                }
            }

            $reserveStatusId = array( ID_RESERVESTATUS_REQUESTED );
            $res = $this->busReserve->getReservesOfExemplary($itemNumber, $reserveStatusId);
            $res = $res[0];

            $selectedForecastDate = $selectedPolicy->forecastDate;

            //se tem reserva pega politica especifica, para determinar data prevista de devolução
            if ( $res )
            {
                $busPolicy = $this->MIOLO->getBusiness('gnuteca3', 'BusPolicy');
                $newPolicy = $busPolicy->getPolicy($person->privilegeGroupId, $person->link->linkId, $info->materialGenderId, true, true);
                $selectedForecastDate = $newPolicy->forecastDate;
            }

            $data = null;
            $data->loanTypeId = 1; //TODO: implementar metodo para setar o valor
            $data->personId = $person->personId;
            $data->linkId = $person->link->linkId;
            $data->itemNumber = $info->itemNumber;
            $data->libraryUnitId = $this->getLibraryUnit();
            $data->loanDate = GDate::now()->getDate(GDate::MASK_TIMESTAMP_USER);
            $data->loanOperator = $this->getOperator();
            $data->loanTypeId = $info->loanTypeId;

            //se o (tipo do empréstimo for FORÇADO e não tiver uma data de previsão de retorno) ou (for um emprestimo momentaneo)
            if ( ($info->loanTypeId == ID_LOANTYPE_OFFLINE && $info->returnForecastDate ) || $info->loanTypeId == ID_LOANTYPE_MOMENTARY )
            {
                if ( $info->returnForecastDate )
                {
                    $forecastDate = $info->returnForecastDate;
                }
            }
            else
            {
                //converte forecastDate para timestampUnix
                $timestampUnix = GDate::construct($selectedForecastDate)->getTimestampUnix();
                //faz a verificação se é feriado ou se a biblioteca esta fechada
                $timestampUnix = $this->busHoliday->checkHolidayDate($timestampUnix, $selectedPolicy->additionalDaysForHolidays, $data->libraryUnitId);
                //converte novamente para dd/mm/yyyy
                $forecastDate = GDate::construct($timestampUnix)->getDate(GDate::MASK_DATE_USER);
            }

            $data->returnForecastDate = $forecastDate;
            $data->renewalAmount = $selectedPolicy->renewalLimit;
            $data->renewalWebAmount = $selectedPolicy->renewalWebLimit;
            $data->renewalWebBonus = $selectedPolicy->renewalWebBonus;
            $data->privilegeGroupId = $selectedPolicy->privilegeGroupId;
            
            $data->policy->penaltyByDelay = $selectedPolicy->penaltyByDelay;

            //tenta localizar um empréstimo duplicado, razões de segurança e integridade
            $locateDuplicateLoan = new BusinessGnuteca3BusLoan();
            //$locateDuplicateLoan->loanTypeIdS = $data->loanTypeId;
            $locateDuplicateLoan->personIdS = $data->personId;
            $locateDuplicateLoan->itemNumberS = $data->itemNumber;
            $locateDuplicateLoan->libraryUnitIdS = $data->libraryUnitId;
            //$locateDuplicateLoan->loanOperatorS = $data->loanOperator;
            //$locateDuplicateLoan->renewalAmountS = $data->renewalAmount;
            //$locateDuplicateLoan->renewalWebAmountS = $data->renewalWebAmount;
            //$locateDuplicateLoan->renewalWebBonusS = $data->renewalWebBonus;
            //$locateDuplicateLoan->beginLoanDateS = date('Y/m/d');
            //$locateDuplicateLoan->endLoanDateS = date('Y/m/d');
            $locateDuplicateLoan->status = 1; //ainda não devolvido
            //busca empréstimos com mesmos dados
            $duplicateLoan = $locateDuplicateLoan->searchLoan(true);
            
            //pega o primeiro retornado, caso tenha acontecido
            $duplicateLoan = $duplicateLoan[0];

            //se for um objeto é porque é um empréstimo duplicado
            if ( is_object($duplicateLoan) )
            {
                $this->addError(_M('Atenção! empréstimo duplicado.', $module), $finalizeMSG);
                return false;
            }

            $finalizeMSG['returnForecastDate'] = $data->returnForecastDate;

            $this->busLoan->setData($data);
            $ok = $this->busLoan->insertLoan();
            
            //Seta as variaveis para serem usadas no webservice
            //$operationDate = $this->busLoan->loanDate;
            


            $this->operationDate = $this->busLoan->loanDate;
            $this->loanNum = $this->busLoan->loanId;
            $this->isRenew = false;

            if ( $ok )
            {
                //ADICIONA ITEM PARA RECIBO DE EMPRESTIMO, define regras de impressão e envio de email
                $receiptObject->addItem(new LoanReceipt($data, true, true));

                $this->addInformation(_M('Sucesso! Exemplar emprestado para o usuário @1 - @2 ', $module, $person->personId, $person->personName), $finalizeMSG);
                $ok = $this->busExemplaryControl->changeStatus($itemNumber, $futureStatus, $data->loanOperator);
            }
            else
            {
                $this->addError(_M('Desculpe, mas não foi possível emprestar o exemplar.', $module), $finalizeMSG);
            }
        }

        //Objeto do recibo já montado para o empréstimo
        $loanReceipt = $receiptObject->generate();
        
        //passa as mensagens do recibo para cá
        if ( is_array($messages = $receiptObject->getMessages()) )
        {
            foreach ( $messages as $line => $message )
            {
               $this->addMessage(null, $message);
            }
        }

        //ao finalizar o processo de devolução limpa o processo de operação que possa ter ficado pindurado
        //if ( $_SESSION['personId'] )
        //{
        //    $this->busPerson->removeOperationProcess($_SESSION['personId']);
        //}

        return true;
    }
    
    
    public function setPerson($patronId, $lib, $loc)
    {
        $this->clearMessages();

        $personId = str_replace('.', '', $patronId); //para evitar erros de sql

        $person = $this->busPerson->getPerson($personId, true, true, true); //pega somente link ativo      
        
        //monta array para mensagens para usuário
        $personMSG['personId'] = $person->personId;
        $personMSG['personName'] = $person->personName;

        if ( !$person->personId )
        {
            return $this->addError(_M('Desculpe, seu código não foi localizado.', $this->module), $personMSG);
        }
        
        $isOperation = $this->busPerson->isOperationProcess($person->personId);

        if ( $isOperation )
        {
            $this->addError(_M('Por favor, aguarde. Outro processo de empréstimo está sendo executando.', $this->module), $personMSG);
            $this->isOperationProcess = TRUE;
            return false;
        }

        $libraryUnitId = $this->getLibraryUnit(); //pega id
        $libraryUnit = $this->getLibraryUnit(true); //pega objeto da sessão
        $fine = $this->busFine->getFineOpenOfAssociation($libraryUnitId, $personId);
        
        $busPenalty = $this->MIOLO->getBusiness($this->module, 'BusPenalty');
        $penalty = $busPenalty->getPenaltyOfAssociation($libraryUnitId, $personId); //TODO não precisa pegar porque já vem no objeto da biblioteca
        $link = $person->bond[0];
        
        if ( !$link )
        {
            return $this->addError(_M('Você não possui vínculo. Consulte informações no balcão de atendimento.', $this->module), $personMSG);
        }

        $access = $this->busPerson->checkAccessLibraryUnit($person->personId, $libraryUnitId);

        if ( !$access )
        {
            return $this->addError(_M('Você não possui acesso à biblioteca @1. Consulte informações no balcão de atendimento.', $this->module, $libraryUnit->libraryName), $personMSG);
        }

        $busPolicy = $this->MIOLO->getBusiness('gnuteca3', 'BusPolicy');
        $policy = $busPolicy->getLibraryUnitPolicy($libraryUnitId, $link->linkId, true, null, $personId);

        if ( !$policy )
        {
            return $this->addError(_M('Não existem políticas para o grupo @1.', $this->module, $link->description), $personMSG);
        }

        $privilegeGroupId = $libraryUnit->privilegeGroupId;
        $generalPolicy = $this->busGeneralPolicy->getGeneralPolicy($privilegeGroupId, $link->linkId, true);
        $loans = $this->busLoan->getLoansOpenOfAssociation($libraryUnitId, $person->personId);

        //Obtem a primeira letra de cada tipo de emprestimo, para que se um tipo de emprestimo mude o nome nao fique hardcode.
        $busLoanType = $this->MIOLO->getBusiness('gnuteca3', 'BusLoanType');
        $abreviatedLoanType[ID_LOANTYPE_DEFAULT] = substr($busLoanType->getLoanType(ID_LOANTYPE_DEFAULT)->description, 0,1);
        $abreviatedLoanType[ID_LOANTYPE_FORCED] = substr($busLoanType->getLoanType(ID_LOANTYPE_FORCED)->description, 0,1);
        $abreviatedLoanType[ID_LOANTYPE_MOMENTARY] = substr($busLoanType->getLoanType(ID_LOANTYPE_MOMENTARY)->description, 0,1);
        
        //calcula empréstimos abertos e atrasados
        for ( $i = 0; $i < count($policy); $i++ )
        {
            $policy[$i]->loanOpenOfAssociation = 0;
            $policy[$i]->loanMomentary = 0;
            $policy[$i]->loanForced = 0;
            $policy[$i]->loanDefault = 0;
            $policy[$i]->delayLoan = 0;

            if ( is_array($loans ) )
            {
                foreach ( $loans as $loan )
                {
                    if ( $policy[$i]->materialGenderId == $this->busExemplaryControl->getMaterialGender($loan->itemNumber) )
                    {
                        $policy[$i]->loanOpenOfAssociation++;

                        //calcula os empréstimo de cada tipo para este genero
                        if ( ($loan->loanTypeId == ID_LOANTYPE_DEFAULT) || ($loan->loanTypeId == ID_LOANTYPE_DEFAULTSIPEQUIPAMENT) )
                        {
                            $policy[$i]->loanDefault++;
                        }
                        else if ( $loan->loanTypeId == ID_LOANTYPE_MOMENTARY )
                        {
                            $policy[$i]->loanMomentary++;
                        }
                        else if ( $loan->loanTypeId == ID_LOANTYPE_FORCED )
                        {
                            $policy[$i]->loanForced++;
                        }
                        
                        $delayLoan = GDate::now()->diffDates(new GDate($loan->returnForecastDate), true)->days;

                        if ( $delayLoan > 0 )
                        {
                            $policy[$i]->delayLoan++;
                        }
                    }
                }

                //Se tiver algum emprestimo forcado ou momentaneo, mostra a informacao complementada.
                if( ($policy[$i]->loanForced > 0 || $policy[$i]->loanMomentary > 0) )
                {
                    $busLoanType = $this->MIOLO->getBusiness('gnuteca3', 'BusLoanType');

                    //Monta descricao mais apurada de que tipos de emprestimos a pessoa tem em aberto.
                    $policy[$i]->loanOpenOfAssociation .= " ({$policy[$i]->loanDefault}".$abreviatedLoanType[ID_LOANTYPE_DEFAULT].", {$policy[$i]->loanForced}".$abreviatedLoanType[ID_LOANTYPE_FORCED].", {$policy[$i]->loanMomentary}".$abreviatedLoanType[ID_LOANTYPE_MOMENTARY].")";                    
                }
            }
        }

        $person->link = $link;
        $person->linkId = $link->linkId;
        $person->generalPolicy = $generalPolicy;
        $person->policy = $policy;
        $person->fine = $fine;
        $person->penalty = $penalty;
        $person->privilegeGroupId = $privilegeGroupId;
        $person->printReceipt = $this->busPersonConfig->getValuePersonConfig($person->personId, 'MARK_PRINT_RECEIPT_LOAN');
        $person->sendReceipt = $this->busPersonConfig->getValuePersonConfig($person->personId, 'MARK_SEND_LOAN_MAIL_RECEIPT');
        $this->person = $person;
        $this->_setPerson($person);
        
        return $person;
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
