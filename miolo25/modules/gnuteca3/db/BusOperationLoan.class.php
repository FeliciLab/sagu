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
  @ @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 09/10/2008
 *
 * */
set_time_limit(0);

$MIOLO = MIOLO::getInstance();
$MIOLO->uses('/classes/receipt/GnutecaReceipt.class.php', 'gnuteca3');

$MIOLO->getClass('gnuteca3', 'RFID');

class BusinessGnuteca3BusOperationLoan extends GOperation
{
    public $MIOLO;
    public $module;
    public $busAuthenticate;
    public $busBond;
    public $busExemplaryControl;
    public $busEmailControlDelayedLoan;
    public $busFine;
    public $busGeneralPolicy;
    public $busHoliday;
    public $busLibraryUnit;
    public $busLoan;
    public $busLocationForMaterialMovement;
    public $busOperationReturn;
    public $busOperationReserve;
    public $busReserveComposition;
    public $busPerson;
    public $busPersonConfig;
    public $busReserve;
    public $busMaterialGender;
    protected $operator;
    protected $locationForMaterialMovementId;
    protected $location;
    protected $libraryUnitId;
    protected $libraryUnit;
    protected $person;
    protected $console = false;
    protected $itemsNumber;
    protected $doReturn;
    protected $gridData;
    private $sendMail;
    private $fine;
    public $isOperationProcess = FALSE;
    public $session;

    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->itemsNumber = array( );
        $this->person->policy = array( );

        $this->MIOLO->getClass($this->module, 'GMail');
        $this->session = new MSession('operationLoan');

        $this->busAuthenticate = $this->MIOLO->getBusiness($this->module, 'BusAuthenticate');
        $this->busBond = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $this->busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busFine = $this->MIOLO->getBusiness($this->module, 'BusFine');
        $this->busGeneralPolicy = $this->MIOLO->getBusiness($this->module, 'BusGeneralPolicy');
        $this->busHoliday = $this->MIOLO->getBusiness($this->module, 'BusHoliday');
        $this->busLoan = $this->MIOLO->getBusiness($this->module, 'BusLoan');
        $this->busPerson = $this->MIOLO->getBusiness($this->module, 'BusPerson');
        $this->busPersonConfig = $this->MIOLO->getBusiness($this->module, 'BusPersonConfig');
        $this->busReserve = $this->MIOLO->getBusiness($this->module, 'BusReserve');
        $this->busOperationReserve = $this->MIOLO->getBusiness($this->module, 'BusOperationReserve');
        $this->busReserveComposition = $this->MIOLO->getBusiness($this->module, 'BusReserveComposition');

        $this->busLocationForMaterialMovement = $this->MIOLO->getBusiness($this->module, 'BusLocationForMaterialMovement');
        $this->busRulesForMaterialMovement = $this->MIOLO->getBusiness($this->module, 'BusRulesForMaterialMovement');
        $this->busEmailControlDelayedLoan = $this->MIOLO->getBusiness($this->module, 'BusEmailControlDelayedLoan');
        $this->sendMail = new GSendMail();
    }

    public function removeOperationProcess($personId)
    {
        return $this->busPerson->removeOperationProcess($personId);
    }

    /**
     * Define the persons of operation and verify it can do a loan
     *
     * @param integer $personId the id of the person
     * @return boolean true or false if can do loan or not
     */
    public function setPerson($personId)
    {
        $this->clearMessages();

        $personId = str_replace('.', '', $personId); //para evitar erros de sql

        $person = $this->busPerson->getPerson($personId, true, true, true); //pega somente link ativo      
        //monta array para mensagens para usuário
        $personMSG['personId'] = $person->personId;
        $personMSG['personName'] = $person->personName;

        if ( !$person->personId )
        {
            return $this->addError(_M('Pessoa não encontrada', $this->module), $personMSG);
        }
        
        $isOperation = $this->busPerson->isOperationProcess($person->personId);

        if ( $isOperation )
        {
            $this->addError(_M('Operador está executando outro processo de empréstimo.', $this->module), $personMSG);
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
            return $this->addError(_M('Usuário não possui vínculo.', $this->module), $personMSG);
        }

        $access = $this->busPerson->checkAccessLibraryUnit($person->personId, $libraryUnitId);

        if ( !$access )
        {
            return $this->addError(_M('Usuário não tem acesso a biblioteca @1.', $this->module, $libraryUnit->libraryName), $personMSG);
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
                        if ( $loan->loanTypeId == ID_LOANTYPE_DEFAULT || ($loan->loanTypeId == ID_LOANTYPE_DEFAULTSIPEQUIPAMENT)  )
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
        
        return true;
    }



    /**
     * Authtenticate the seted person
     *
     * @param string $password the string with password
     * @param integer $personId if the person is not seted you must inform the personId, otherwise not.
     * @return boolean true if logged
     */
    public function personAuthenticate($password, $personId = NULL)
    {
        $person = $this->getPerson();

        if ( !$personId )
        {
            $person = $this->getPerson();
            $personId = $person->personId;
        }

        //Verifica permissao para operador pular senha, caso seja digitada uma senha, nao entra nesta condicao
        if ( !($password) && (GPerms::checkAccess('gtcMaterialMovementSkipPassword', null, false)) )
        {
            $ok = TRUE;
        }
        else
        {
            $ok = $this->busAuthenticate->authenticate($personId, $password, false);
        }

        if ( $ok )
        {
            //Define a data e hora atual para o campo operationProcess
            $this->busPerson->setOperationProcess($personId);
        }

        return $ok;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $itemNumber
     * @param unknown_type $loanTypeId
     * @return unknown
     */
    public function addItemNumber($itemNumber, $loanTypeId, $printReceipt = null, $sendReceipt = null)
    {
        $this->fine = null;
        $busRight = $this->MIOLO->getBusiness($this->module, 'BusRight');
        $busLoanType = $this->MIOLO->getBusiness($this->module, 'BusLoanType');

        $addItemNumberMSG['itemNumber'] = $itemNumber;

        $exemplary = $this->busExemplaryControl->getExemplaryControl($itemNumber);

        if ( !$exemplary->itemNumber )
        {
            $this->addError(_M('Exemplar não existe.', $this->module), $addItemNumberMSG);
            return false;
        }

        $busSearchFormat = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        $addItemNumberMSG['searchData'] = $busSearchFormat->getFormatedString($exemplary->controlNumber, MATERIAL_MOVIMENT_SEARCH_FORMAT_ID, 'search');
        $exemplary->loanTypeId = $loanTypeId;
        $loanTypeList = $busLoanType->listLoanType();
        $exemplary->loanTypeDescription = $loanTypeList[$loanTypeId][1];
        $exemplary->searchData = $addItemNumberMSG['searchData'];
        $itensLoan = $this->getItemsLoan();
        
        $itensLoan = $itensLoan ? $itensLoan : array();

        //Checa se o exemplar já está na lista
        if ( array_key_exists($itemNumber, $itensLoan ) )
        {
            $this->addError(_M('O exemplar já está na lista de empréstimos.', $this->module), $addItemNumberMSG);
            return false;
        }

        // checa se o exemplar pertence da unidade correta
        $libraryUnit = $this->getLibraryUnit(true);

        if ( $exemplary->libraryUnitId != $this->getLibraryUnit() )
        {
            $this->addError(_M('Exemplar não pertencente a unidade <b>@1</b>, pertence a unidade de <b>@2</b>.', $this->module, $libraryUnit->libraryName, $exemplary->libraryName), $addItemNumberMSG);

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

        if ( $loanTypeId == ID_LOANTYPE_DEFAULT )
        {
            if ( !$materialGenderId )
            {
                $this->addError(_M('Não foi possível identificar o gênero do material.', $this->module), $addItemNumberMSG);
                return false;
            }

            $right = $busRight->hasRight($exemplary->libraryUnitId, $person->linkId, $materialGenderId, ID_OPERATION_LOAN);

            if ( !$right )
            {
                $this->addError(_M('O grupo @1 não possui direitos para retirar materiais de gênero @2.', $this->module, $person->link->description, $materialGender), $addItemNumberMSG);

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
                    $this->addError(_M('O usuário @1 possui multas em aberto e o grupo @2 não tem direito de retirar com multas.', $this->module, $person->personId, $person->link->description), $addItemNumberMSG);
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
                        $this->addError(_M('O usuário @1 possui penalidades em aberto e o grupo @2 não tem direito de retirar com penalidades.', $this->module, $person->personId, $person->link->description), $addItemNumberMSG);
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
                            $this->addError(_M('O usuário @1 possui materiais em atraso e o grupo @2 não tem direito de retirar com materiais em atraso.', $this->module, $person->personId, $person->link->description), $addItemNumberMSG);
                            return false;
                        }
                    }
                }
            }

            if ( !MUtil::getBooleanValue($status->executeLoan) )
            {
                $this->addError(_M('Materiais no estado <b>"@1"</b> não podem ser emprestados.', $this->module, $status->description), $addItemNumberMSG);
                return false;
            }

            $policyFound = false;
            foreach ( $person->policy as $policy )
            {
                if ( $materialGenderId == $policy->materialGenderId )
                {
                    $policyFound = true;
                    $tmpList = $this->getItemsLoan($materialGenderId);
                    $amountTmpMaterial = 0;
                    if ( is_array($tmpList) )
                    {
                        foreach ( $tmpList as $k => $itensLoan )
                        {
                            //verifica se o empréstimo é do tipo padrão
                            if ( $itensLoan->loanTypeId == ID_LOANTYPE_DEFAULT )
                            {
                                $amountTmpMaterial++;
                            }
                        }
                    }

                    if ( !$loan || $loan->personId != $person->personId )
                    {

                        if ( ($policy->loanLimit - $policy->loanDefault - $amountTmpMaterial) <= 0 )
                        {
                            $this->addError(_M('Limite de empréstimo do grupo @1 e gênero @2 foi excedido.', $this->module, $person->link->description, $materialGender), $addItemNumberMSG);
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
                                    if ( $val->loanTypeId == ID_LOANTYPE_DEFAULT ) //se não for momentâneo e nem forçado
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
                                    if ( $loansDetail->loanTypeId == ID_LOANTYPE_DEFAULT )
                                    {
                                        $amountTmpMaterialGeneral++;  //soma os empréstimos da listagem com os empréstimo da base que são empréstimo padrão
                                    }
                                }
                            }

                            if ( ($person->generalPolicy->loanGeneralLimit - $amountTmpMaterialGeneral) <= 0 )
                            {
                                $this->addError(_M('Limite total de empréstimo do grupo @1 foi excedido.', $this->module, $person->link->description), $addItemNumberMSG);
                                return false;
                            }
                        }
                    }
                }
            }
            //Verifica se localizou políticas para o material
            if ( !$policyFound )
            {
                $this->addError(_M('O grupo @1 não possui políticas para materiais do gênero @2.', $this->module, $person->link->description, $materialGender), $addItemNumberMSG);
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
                        $this->addError(_M('O exemplar possui <b>reserva</b> e não pode ser emprestado para o usuário <b>@1</b>.', $this->module, "{$person->personId} - {$person->personName}"), $addItemNumberMSG);
                        return false;
                    }
                }
            }

            if ( $loan )
            {
                //Emprestado para o titular. Executará renovação
                if ( $loan->personId == $person->personId )
                {
                    //Executa o processo para checkar renovacao
                    $busOperationRenew = $this->MIOLO->getBusiness($this->module, 'BusOperationRenew');
                    $busOperationRenew->setRenewType(ID_RENEWTYPE_LOCAL);
                    $renewLoan = $busOperationRenew->checkLoan($loan->loanId);

                    if ( !$renewLoan )
                    {
                        $erros = $busOperationRenew->getErrors();

                        foreach ( $erros as $erro )
                        {
                            $msgErros[] = $erro->message;
                        }

                        $this->addError(_M('Não é possí­vel renovar o material: ', $this->module) . implode('. ', $msgErros), $addItemNumberMSG);
                        return false;
                    }

                    $busOperationRenew->addLoan($renewLoan);

                    //Se não deu erro, concluirá a renovação no finalize()
                    $concludesRenew = true;
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
                    $this->addError(_M('O exemplar possui um reserva solicitada para outro usuário.', $this->module), $addItemNumberMSG);
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

        if ( $loanTypeId == ID_LOANTYPE_FORCED )
        {
            $reserveStatusId = array( ID_RESERVESTATUS_REQUESTED, ID_RESERVESTATUS_ANSWERED, ID_RESERVESTATUS_REPORTED );

            if ( $loan )
            {
                $this->addError(_M('O material esta emprestado. Impossível retirá-lo.', $this->module));
                return false;
            }

            if ( $status->isReserveStatus )
            {
                $reserve = $this->busReserve->getReservesOfExemplary($itemNumber, $reserveStatusId);

                //se reversa for para OUTRA pessoa
                if ( is_array($reserve) )
                {
                    if ( $reserve[0]->personId != $person->personId )
                    {

                        if ( $reserve[0]->reserveStatusId == ID_RESERVESTATUS_REPORTED )
                        {
                            $this->addError(_M('O exemplar possui uma reserva comunicada.', $this->module), $addItemNumberMSG);
                        }
                        else
                        {
                            $ok = $this->addQuestion(_M('Altera a reserva para solicitada??', $this->module));
                            //FIXME este ok  fake precisa pedir isto para o operador, isto Ã© sÃ³ a aplicaÃ§Ã£o da lÃ³gica
                            if ( $ok )
                            {
                                $exemplary->requestedReserve = true;
                            }
                            else
                            {
                                $this->addError(_M('Existem reservas para este exemplar.', $this->module), $addItemNumberMSG);
                                return false;
                            }
                        }
                    }
                }
                //se a reserva for para MESMA pessoa
                else
                {
                    $concludesReserve = true;
                }
            }

            $returnForecastDate = MIOLO::_REQUEST(returnForecastDate);
        }

        //caso seja empréstimo momentâneo
        if ( $loanTypeId == ID_LOANTYPE_MOMENTARY )
        {
            // Verifica o direito do grupo em empréstimo forçado.
            $right = $busRight->hasRight($exemplary->libraryUnitId, $person->linkId, $materialGenderId, ID_OPERATION_LOAN_MOMENTARY);

            if ( !$right )
            {
                $this->addError(_M('O grupo @1 não possui direitos para retirar materiais de gênero @2.', $this->module, $person->link->description, $materialGender), $addItemNumberMSG);

                return false;
            }

            //verifica se o estado permite empréstimo momentâneo
            if ( !MUtil::getBooleanValue($status->momentaryLoan) )
            {
                throw new Exception(_M('Não é permitido fazer empréstimo momentâneo de exemplar que está no estado: @1.', $this->module, $status->description));
            }

            //Para garantir que o exemplar nao seja emprestado quando houver o emprestimo em aberto
            if ( $this->busLoan->getLoanOpen($itemNumber) )
            {
                throw new Exception(_M('O exemplar @1 possui empréstimo em aberto', $this->module, $itemNumber));
            }

            $returnForecastDate = GDate::now();

            //avalia o período de empréstimo momentâneo
            if ( defined('LOAN_MOMENTARY_PERIOD') && LOAN_MOMENTARY_PERIOD == 'H' )
            {
                $returnForecastDate->addHour($status->daysOfMomentaryLoan);
                $returnForecastDate = $returnForecastDate->getDate(GDate::MASK_TIMESTAMP_USER);
            }
            else
            {
                $returnForecastDate->addDay($status->daysOfMomentaryLoan);
                $returnForecastDate = $returnForecastDate->getDate(GDate::MASK_DATE_USER);
            }
        }

        $exemplary->status = $status;
        $exemplary->concludesRenew = $concludesRenew;
        $exemplary->doReturn = $doReturn;
        $exemplary->concludesReserve = $concludesReserve;
        //$exemplary->requestedReserve    = $requestedReserve;
        $exemplary->returnForecastDate = $returnForecastDate;

        $person = $this->getPerson();

        $imgPrint = new MImage('imgPrint', null, GUtil::getImageTheme('print-16x16.png'));
        $imgPrint = $imgPrint->generate();

        $imgEmail = new MImage('imgEmail', null, GUtil::getImageTheme('email-16x16.png'));
        $imgEmail = $imgEmail->generate();

        $location = $this->getLocation(true);
            
        //verifica se é para enviar recibo por email
        if ( MUtil::getBooleanValue($location->sendLoanReceiptByEmail) )
        {
            $exemplary->sendReceipt = true;
        }
        else
        {
            $exemplary->sendReceipt = $person->sendReceipt;
        }
        
        if ( !MUtil::getBooleanValue($printReceip == 'f') )
        {
            if ( MUtil::getBooleanValue($printReceip == 't') )
            {
                $exemplary->printReceipt = true;
            }
            else
            {
                $exemplary->printReceipt = $person->printReceipt;
            }
        }
        
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
        
        $ok = $this->_addItemNumber($exemplary);

        if ( !$ok )
        {
            $this->addError(_M('O exemplar já está na lista de empréstimos.', $this->module), $addItemNumberMSG);
        }

        return $ok;
    }

    /**
     * add an exemplary to item list
     *
     * @param integer $itemNumber the itemNumber of exemplar
     * @param string $statusDescription the description of actual status of the exemplary
     */
    protected function _addItemNumber($exemplary)
    {
        $itemNumber = $exemplary->itemNumber;
        $exemplary->exemplaryStatus = $exemplary->status->description;

        $items = $this->session->get('itemsLoan');
        $items[$itemNumber] = $exemplary;
        $this->session->set('itemsLoan', $items);

        return true;
    }

    public function deleteItemNumber($itemNumber)
    {
        unset($_SESSION['itemsLoan'][$itemNumber]);
    }

    /**
     * Return an array of objects  with all item (exemplary) informatin.
     *
     * @return array an array of objects  with all item (exemplary) informatin.
     */
    public function getItemsLoan($materialGenderId = false)
    {
        if ( !$materialGenderId )
        {
            return $this->session->get('itemsLoan');
        }
        else
        {
            if ( is_array( $this->session->get('itemsLoan') ) )
            {
                foreach ( $this->session->get('itemsLoan') as $item )
                {
                    if ( ($item->materialGenderId == $materialGenderId) && (!$item->concludesRenew) )
                    {
                        $list[] = $item;
                    }
                }
            }
            return $list;
        }
    }

    /**
     * Clear the exemplary list
     *
     */
    public function clearItemsLoan()
    {
        $this->session->unregister('itemsLoan');
        $busOperationRenew = $this->MIOLO->getBusiness($this->module, 'BusOperationRenew');
        $busOperationRenew->clearData();
    }

    /**
     * Finaliza operação de devolução
     *
     * @param object $receipt
     * @return unknown
     */
    public function finalize($formData = null)
    {
        $module = MIOLO::getCurrentModule();
        $person = $this->getPerson();

        if(RFID_INTEGRATION == DB_TRUE)
        {
            if(! isset($_COOKIE[RFID_COOKIE]))
            {
                $this->addError("Você esta usando RFID, mas não esta identificado. Identifique-se antes de continuar.");
                return false;
                
            }else
            {
                if(!RFID::verifyStatus())
                {
                    $this->addError("O status do equipamento RFID é inativo.");
                    return false;
                }
                 
            }
        }

        //Limpa a variável
        unset($selectedPolicy->forecastDate);

        // verifica se ha itens na lista.
        $items = $this->getItemsLoan();

        if ( !is_array($items) )
        {
            $this->addInformation(_M('Não há itens para finalizar o processo de empréstimo.', $module));
            return false;
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


            //define se é ou não para imprimir ou enviar recibo
            $location = $this->getLocation();

            if ( $formData->sendReceipt == GnutecaReceipt::RECEIPT_USER_CONFIG )
            {
                $sendReceipt = MUtil::getBooleanValue($info->sendReceipt);
            }
            else
            {
                $sendReceipt = MUtil::getBooleanValue($formData->sendReceipt);
            }

            if ( $formData->printReceipt == GnutecaReceipt::RECEIPT_USER_CONFIG )
            {
                $printReceipt = MUtil::getBooleanValue($info->printReceipt);
            }
            else
            {
                $printReceipt = MUtil::getBooleanValue($formData->printReceipt);
            }

            $itemNumber = $info->itemNumber;
            $currentStateId = $info->status->exemplaryStatusId;
            $location = $this->getLocation(true);
            $loanType = $busLoanType->listLoanType();

            $futureStatus = $this->busRulesForMaterialMovement->getFutureStatus($itemNumber, $currentStateId, ID_OPERATION_LOAN, $this->getLocation());
            if ( !$futureStatus )
            {
                $rule = _M('<b>Estado atual</b>: @1, <b>Operação:</b> @2 e <b>Local</b>: @3', $module, $currentStateId . ' -  ' . $info->status->description, ID_OPERATION_LOAN . ' - ' . $loanType[ID_OPERATION_LOAN][1], $this->getLocation() . ' - ' . $this->getLocation(true)->description, $finalizeMSG);
                $this->addError(_M('Não foi possível localizar um <b>estado futuro</b> para o exemplar. @1', $module, $rule), $finalizeMSG);
                //$this->deleteItemNumber($itemNumber); //remove o item da lista para não gerar recibo

                continue;
            }

            //Marcado como renovação
            if ( $info->concludesRenew )
            {
                //Executa processo de conclusao de renovação
                $busOperationRenew = $this->MIOLO->getBusiness($this->module, 'BusOperationRenew');
                $busOperationRenew->finalize($info->itemNumber, $sendReceipt, $printReceipt);
                $erros = $busOperationRenew->getErrors();

                //obtem os recibos da operação de renovação
                $renewReceipt = $busOperationRenew->receipt->getItens();
                $renewReceipt = $renewReceipt['LoanReceipt'];

                foreach ( $renewReceipt as $line => $receipt )
                {
                    $receiptObject->addItem($receipt);
                }

                //limpa os recibos da operação
                $busOperationRenew->receipt->setItens(null);

                //se contiver erros falha a operação
                if ( is_array($erros) )
                {
                    foreach ( $erros as $erro )
                    {
                        $msgErros[] = $erro->message;
                    }

                    $this->addError(_M('Não é possível renovar o material: ', $this->module) . implode('. ', $msgErros), $finalizeMSG);
                }

                $infos = $busOperationRenew->getInformations();
                $loanId = $busOperationRenew->getLoanIdFromItemNumber($info->itemNumber);

                if ( $loanId )
                {
                    $returnForecastDate = new GDate($this->busLoan->getReturnForecastDate($loanId));
                    $returnForecastDate = $returnForecastDate->getDate(GDate::MASK_DATE_USER);
                }

                $finalizeMSG['returnForecastDate'] = '<b>' . $returnForecastDate . '</b>';

                foreach ( $infos as $inf )
                {
                    $this->addInformation($inf->message, $finalizeMSG);
                }

                $busOperationRenew->clearMessages();
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
                        $this->addError($erro->message, $finalizeMSG);
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
            if ( ($info->loanTypeId == ID_LOANTYPE_FORCED && $info->returnForecastDate ) || $info->loanTypeId == ID_LOANTYPE_MOMENTARY )
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
            
            if ( $info->loanTypeId == ID_LOANTYPE_MOMENTARY )
            {
                $data->policy->fineValue = $selectedPolicy->momentaryFineValue;
            }
            else
            {
                $data->policy->fineValue = $selectedPolicy->fineValue;
            }
            
            $data->policy->penaltyByDelay = $selectedPolicy->penaltyByDelay;

            //tenta localizar um empréstimo duplicado, razões de segurança e integridade
            $locateDuplicateLoan = new BusinessGnuteca3BusLoan();
            $locateDuplicateLoan->loanTypeIdS = $data->loanTypeId;
            $locateDuplicateLoan->personIdS = $data->personId;
            $locateDuplicateLoan->itemNumberS = $data->itemNumber;
            $locateDuplicateLoan->libraryUnitIdS = $data->libraryUnitId;
            $locateDuplicateLoan->loanOperatorS = $data->loanOperator;
            $locateDuplicateLoan->renewalAmountS = $data->renewalAmount;
            $locateDuplicateLoan->renewalWebAmountS = $data->renewalWebAmount;
            $locateDuplicateLoan->renewalWebBonusS = $data->renewalWebBonus;
            $locateDuplicateLoan->beginLoanDateS = date('Y/m/d');
            $locateDuplicateLoan->endLoanDateS = date('Y/m/d');
            $locateDuplicateLoan->status = 1; //ainda não devolvido
            //busca empréstimos com mesmos dados
            $duplicateLoan = $locateDuplicateLoan->searchLoan(true);
            //pega o primeiro retornado, caso tenha acontecido
            $duplicateLoan = $duplicateLoan[0];

            //se for um objeto é porque é um empréstimo duplicado
            if ( is_object($duplicateLoan) )
            {
                $this->addError(_M('Empréstimo duplicado.', $module), $finalizeMSG);
                return false;
            }

            $finalizeMSG['returnForecastDate'] = '<b>' . $data->returnForecastDate . '</b>';

            $this->busLoan->setData($data);

            $ok = $this->busLoan->insertLoan();

            if ( $ok )
            {
                //ADICIONA ITEM PARA RECIBO DE EMPRESTIMO, define regras de impressão e envio de email
                $receiptObject->addItem(new LoanReceipt($data, $sendReceipt, $printReceipt));

                $this->addInformation(_M('Exemplar emprestado com sucesso para o usuário <b>@1 - @2</b> ', $module, $person->personId, $person->personName), $finalizeMSG);
                $ok = $this->busExemplaryControl->changeStatus($itemNumber, $futureStatus, $data->loanOperator);
                
                
                
                //Caso tenha o sistema de integração com RFID, tenta remover o bit
                if(RFID_INTEGRATION == DB_TRUE)
                {
                    $r = RFID::removeBitAgainstTheft();
                    //sleep(3);
                    
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
                        $this->addInformation("Removido o bit <b>anti-furto</b> do exemplar.");
                    }else
                    {
                        $this->addError("Não foi possível desativar o <b>anti-furto</b>! Após solucionado o problema, vá até: <b>Circulação de material</b>-><b>Empréstimo</b>. <br> Consultando pelo <b>número do exemplar</b>, <b>desative manualmente</b> o anti-furto.");
                        $this->addError($msgBit);
                    }
                }
            }
            else
            {
                $this->addError(_M('Não foi possível emprestar o exemplar.', $module), $finalizeMSG);
            }
        }

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
        if ( $_SESSION['personId'] )
        {
            $this->busPerson->removeOperationProcess($_SESSION['personId']);
        }

        return true;
    }

    /**
     * Comunica alunos que tem material para devolver.
     *
     * @return boolean
     */
    public function communicateReturn($libraryUnitId)
    {
        $libraryUnitIds = is_array($libraryUnitId) ? $libraryUnitId : array( $libraryUnitId );
        $busLibraryUnitConfig = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnitConfig');
        $busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');

        foreach ( $libraryUnitIds as $libraryUnitId )
        {
            $beginDate = GDate::now();
            $endDate = clone($beginDate);
            $endDate->addDay(LIMIT_DAYS_BEFORE_EXPIRED);

            unset($endDate->MIOLO);

            $loans = $this->busLoan->getLoansOpenLibrary($beginDate->getDate(GDate::MASK_DATE_DB), $endDate->getDate(GDate::MASK_DATE_DB), $libraryUnitId);

            if ( !$loans )
            {
                continue;
            }

            $subject = $busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_RETURN_SUBJECT');
            $content = $busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_RETURN_CONTENT');

            if ( !($subject && $content) )
            {
                $this->addError(_M('O e-mail não está configurado.', $this->module));
                return false;
            }

            $communicateMessage = array( );

            foreach ( $loans as $loan )
            {
                $config = $this->busPersonConfig->getValuePersonConfig($loan->personId, 'USER_DAYS_BEFORE_EXPIRED');
                $userBeforeExpired = MUtil::getBooleanValue($this->busPersonConfig->getValuePersonConfig($loan->personId, 'USER_SEND_DAYS_BEFORE_EXPIRED'));

                $now = GDate::now();
                $returnForecastDate = new GDate($loan->returnForecastDate);
                $diff = $returnForecastDate->diffDates($now, GDate::ROUND_UP);
                $days = $diff->days;

                if ( ($days == $config) && ($userBeforeExpired) )
                {
                    $margTitleTag = explode('.', MARC_TITLE_TAG);
                    $fieldId = $margTitleTag[0];
                    $subfieldId = $margTitleTag[1];
                    $loan->title = ($loan->controlNumber) ? $busMaterial->getContent($loan->controlNumber, $fieldId, $subfieldId) : '';

                    //mensagem
                    $communicateMessage[$loan->loanId][0] = $loan->loanId;
                    $communicateMessage[$loan->loanId][1] = "{$loan->personId} - {$loan->personName}";
                    $communicateMessage[$loan->loanId][4] = $loan->itemNumber;
                    $communicateMessage[$loan->loanId][5] = $loan->personEmail;
                    $communicateMessage[$loan->loanId][6] = $loan->title;

                    //testa se pessoa tem e-mail
                    if ( !strlen($loan->personEmail) )
                    {
                        $communicateMessage[$loan->loanId][2] = DB_FALSE;
                        $communicateMessage[$loan->loanId][3] = _M("E-mail da pessoa está em branco", $this->module);
                        continue;
                    }

                    //testa se e-mail é no formato conta@domínio.extensão
                    if ( !preg_match("/^[a-zA-Z0-9\._-]+@[a-zA-Z0-9\._-]+\.([a-zA-Z]{2,4})$/", $loan->personEmail) )
                    {
                        $communicateMessage[$loan->loanId][2] = DB_FALSE;
                        $communicateMessage[$loan->loanId][3] = _M("Person mail is invalid.", $this->module);
                        continue;
                    }

                    if ( !$this->sendMail->sendMailToUserCommunicateReturn($loan, $subject, $content) )
                    {
                        $communicateMessage[$loan->loanId][2] = DB_FALSE;
                        $communicateMessage[$loan->loanId][3] = _M("Falha no envio do e-mail", $this->module);
                    }
                    else
                    {
                        $communicateMessage[$loan->loanId][2] = DB_TRUE;
                        $communicateMessage[$loan->loanId][3] = _M("Sucesso!", $this->module);
                    }
                }
            }

            //avisa admin
            $this->sendMail->sendMailToAdminResultOfCommunicateReturn($communicateMessage, $libraryUnitId);
        }

        // MONTA GRID COM OS DADOS DOS ENVIOS DE EMAIL.
        foreach ( $communicateMessage as $content )
        {
            $this->addGridData($content);
        }

        return true;
    }

    /**
     * Envia e-mail de devolução
     * @param $libraryUnitId
     */
    public function communicateDelayedLoan($libraryUnitId)
    {
        $busMyLibrary = $this->MIOLO->getBusiness('gnuteca3', 'BusMyLibrary');

        $libraryUnitIds = is_array($libraryUnitId) ? $libraryUnitId : array( $libraryUnitId );

        //percorre todas unidades
        foreach ( $libraryUnitIds as $libraryUnitId )
        {
            $generalPreferences = explode(';', USER_DELAYED_LOAN);
            $quant = $generalPreferences[0];
            $period = $generalPreferences[1];

            //data inicial    
            $beginDate = GDate::now();
            $beginDate->addDay(-($quant * $period));
            $beginDate = $beginDate->getDate(GDate::MASK_DATE_DB);

            //data final
            $endDate = GDate::now();
            $endDate->addDay(-1);
            $endDate = $endDate->getDate(GDate::MASK_DATE_DB);

            //Busca todos os empréstimos atrasados com data prevista entre ontem e x dias atrás
            $loans = $this->busLoan->getLoansOpenLibrary($beginDate, $endDate, $libraryUnitId);

            //Se não tiver nenhum material atrasado neste período
            if ( !$loans )
            {
                continue;
            }

            $delayedLoanMessage = array( );

            $busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
            $busLibraryUnitConfig = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnitConfig');

            foreach ( $loans as $loan )
            {
                //Verifica se é para enviar aviso para o usuário
                $userBeforeExpired = MUtil::getBooleanValue($this->busPersonConfig->getValuePersonConfig($loan->personId, 'USER_SEND_DELAYED_LOAN'));
                $value = $this->busPersonConfig->getValuePersonConfig($loan->personId, 'USER_DELAYED_LOAN');

                list($quantidade, $periodo) = explode(';', $value);

                //Data de início é calculada de acordo com os valores da preferência USER_DELAYED_LOAN (pode ser a padrão ou a definida pelo usuário
                $beginDateObj = GDate::now();
                $beginDateObj->addDay(-($quantidade * ($periodo + 1)));
                $beginDate = $beginDateObj->getDate(GDate::MASK_DATE_DB);

                $endDateObj = GDate::now();
                $endDateObj->addDay(-1);
                $endDate = $endDateObj->getDate(GDate::MASK_DATE_DB);

                $returnForecastDateObj = new GDate($loan->returnForecastDate);


                $subject = $busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_DELAYED_LOAN_SUBJECT');
                $content = $busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_DELAYED_LOAN_CONTENT');

                //Só vai enviar o aviso para os empréstimos que estão entre este período
                if ( ($returnForecastDateObj->compare($beginDateObj, '>=')) && ($returnForecastDateObj->compare($endDateObj, '<=')) && ($userBeforeExpired) )
                {
                    $getLastSent = $this->busEmailControlDelayedLoan->getLastSent($loan->loanId);
                    $lastSend = new GDate($getLastSent->lastSent);
                    $now = GDate::now();

                    $checkSendDate1 = clone($returnForecastDateObj);
                    $checkSendDate1->addDay(1);

                    //Cria objeto de X dias configurados apos data prevista de devolucao
                    $checkSendDate2 = clone($returnForecastDateObj);

                    //fórmula: data prevista + ( período * quantidade de notificações já enviadas)
                    $enviados = $getLastSent->amountSent;
                    $enviados = $periodo == 1 ? $enviados + 1 : $enviados; //quando período é 1, considera o envio futuro
                    $dias = $periodo * $enviados;

                    $checkSendDate2->addDay($dias);

                    if ( ( ($checkSendDate1->compare($now, '=') || ($checkSendDate2->compare($now, '<='))) //Verifica se fecha os dias que deve enviar (+1 dia apos a data prevista OU hoje)
                            && (!$getLastSent || $lastSend->compare($now, '<')) //Se nao tiver nenhum registro de ultimo enviado OU o ultimo envio não foi hoje.
                            && ($getLastSent->amountSent < $quantidade) ) //Se quantidade limite ainda não foi atingida
                            && ($userBeforeExpired) ) //Preferencia de mandar email de aviso ativada
                    {
                        $marcTitleTag = explode('.', MARC_TITLE_TAG);
                        $fieldId = $marcTitleTag[0];
                        $subfieldId = $marcTitleTag[1];
                        $loan->title = ($loan->controlNumber) ? $busMaterial->getContent($loan->controlNumber, $fieldId, $subfieldId) : '';

                        //insere registro na minha biblioteca
                        $busMyLibrary->myLibraryId = null;
                        $busMyLibrary->personId = $loan->personId;
                        $busMyLibrary->date = GDate::now()->getDate(GDate::MASK_TIMESTAMP_USER);
                        $busMyLibrary->message = stripslashes($busMyLibrary->getDelayedLoanMessage());
                        $busMyLibrary->visible = DB_TRUE;
                        $busMyLibrary->insertMyLibrary();

                        //mensagem
                        $delayedLoanMessage[$loan->loanId][0] = $loan->loanId;
                        $delayedLoanMessage[$loan->loanId][1] = "{$loan->personId} - {$loan->personName}";
                        $delayedLoanMessage[$loan->loanId][4] = $loan->itemNumber;
                        $delayedLoanMessage[$loan->loanId][5] = $loan->personEmail;
                        $delayedLoanMessage[$loan->loanId][6] = $loan->title;

                        if ( !$loan->personEmail )
                        {
                            $delayedLoanMessage[$loan->loanId][2] = DB_FALSE;
                            $delayedLoanMessage[$loan->loanId][3] = _M("E-mail da pessoa está em branco", $this->module);
                            continue;
                        }

                        //testa se e-mail é no formato conta@domínio.extensão
                        if ( !preg_match("/^[a-zA-Z0-9\._-]+@[a-zA-Z0-9\._-]+\.([a-zA-Z]{2,4})$/", $loan->personEmail) )
                        {
                            $delayedLoanMessage[$loan->loanId][2] = DB_FALSE;
                            $delayedLoanMessage[$loan->loanId][3] = _M("Person mail is invalid.", $this->module);
                            continue;
                        }

                        if ( !$this->sendMail->sendMailToUserCommunicateDelayedLoan($loan, $subject, $content) )
                        {
                            $delayedLoanMessage[$loan->loanId][2] = DB_FALSE;
                            $delayedLoanMessage[$loan->loanId][3] = _M("Falha no envio do e-mail", $this->module);
                        }
                        else
                        {
                            $this->busEmailControlDelayedLoan->loanId = $loan->loanId;
                            $this->busEmailControlDelayedLoan->lastSent = $now->getDate(GDate::MASK_DATE_DB);
                            $this->busEmailControlDelayedLoan->amountSent = $getLastSent->amountSent + 1;
                            $this->busEmailControlDelayedLoan->deleteEmailControlDelayedLoan($loan->loanId);
                            $this->busEmailControlDelayedLoan->insertEmailControlDelayedLoan();

                            $delayedLoanMessage[$loan->loanId][2] = DB_TRUE;
                            $delayedLoanMessage[$loan->loanId][3] = _M("Sucesso!", $this->module);
                        }
                    }
                }
            }
            //envia e-mail para administrador com o resultado
            $this->sendMail->sendMailToAdminResultOfCommunicateDelayedLoan($delayedLoanMessage, $libraryUnitId);
        }
        // MONTA GRID COM OS DADOS DOS ENVIOS DE EMAIL.
        foreach ( $delayedLoanMessage as $content )
        {
            $this->addGridData($content);
        }

        return true;
    }

    public function addGridData($gridData)
    {
        $this->gridData[] = $gridData;
    }

    public function getGridData()
    {
        return $this->gridData;
    }

    public function getFine()
    {
        return $this->fine;
    }
}

?>
