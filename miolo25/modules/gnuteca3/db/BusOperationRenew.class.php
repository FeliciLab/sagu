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
 * Class GOperationRenew
 * Class to manage to operation renew of gnuteca.
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 09/10/2008
 *
 * */
$MIOLO = MIOLO::getInstance();
$MIOLO->uses('/classes/receipt/GnutecaReceipt.class.php', 'gnuteca3');

class BusinessGnuteca3BusOperationRenew extends GMessages
{
    public $MIOLO;
    public $module;

    /** @var BusinessGnuteca3BusLoan  */
    public $busLoan;
    public $busLibraryUnit;
    public $busExemplaryControl;
    public $busHoliday;
    public $busPolicy;
    public $busFine;
    public $busPenalty;
    public $busPerson;
    public $busBond;
    public $busReserve;
    public $busRight;
    public $busRenew;
    public $busUserGroup;
    public $busMaterialGender;

    /** @var GnutecaReceipt */
    public $receipt;

    /**
     * Class construct
     */
    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busLoan = $this->MIOLO->getBusiness($this->module, 'BusLoan');
        $this->busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busPerson = $this->MIOLO->getBusiness($this->module, 'BusPerson');
        $this->busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busHoliday = $this->MIOLO->getBusiness($this->module, 'BusHoliday');
        $this->busPolicy = $this->MIOLO->getBusiness($this->module, 'BusPolicy');
        $this->busFine = $this->MIOLO->getBusiness($this->module, 'BusFine');
        $this->busPenalty = $this->MIOLO->getBusiness($this->module, 'BusPenalty');
        $this->busPerson = $this->MIOLO->getBusiness($this->module, 'BusPerson');
        $this->busBond = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $this->busReserve = $this->MIOLO->getBusiness($this->module, 'BusReserve');
        $this->busRight = $this->MIOLO->getBusiness($this->module, 'BusRight');
        $this->busRenew = $this->MIOLO->getBusiness($this->module, 'BusRenew');
        $this->busUserGroup = $this->MIOLO->getBusiness($this->module, 'BusUserGroup');
        $this->busMaterialGender = $this->MIOLO->getBusiness($this->module, 'BusMaterialGender');
        $this->busPersonLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusPersonLibraryUnit');
        $this->receipt = new GnutecaReceipt();
    }

    /**
     * Define the renew Type (web or local)
     *
     * Bas config DEFINEs:
     * ID_RENEWTYPE_WEB
     * ID_RENEWTYPE_LOCAL
     *
     * @param $type the int with renew type code
     */
    public function setRenewType($type)
    {
        $_SESSION['renewTypeId'] = $type;
    }

    /**
     * Return the renew type
     *
     * Bas config DEFINEs:
     * ID_RENEWTYPE_WEB
     * ID_RENEWTYPE_LOCAL
     *
     * @return $type the renew type
     */
    public function getRenewType()
    {
        return $_SESSION['renewTypeId'];
    }

    /**
     * Clear the exemplary list
     *
     */
    public function clearData()
    {
        unset($_SESSION['renewTypeId']);
        unset($_SESSION['itemsRenew']);
    }

    /**
     * Checks if loan exists
     *
     * @param loanId the code of the loan
     */
    public function checkLoan($loanId, $personId = NULL)
    {
        $loan = $this->busLoan->getLoan($loanId, true);
        $extraColumns['itemNumber'] = $loan->itemNumber;
        
        if ( !$loan->loanId )
        {
            $this->addError(_M('Não existe o empréstimo.', $this->module), $extraColumns);
            return false;
        }
        else if ( $loan->loanTypeId == ID_LOANTYPE_MOMENTARY )
        {
            $this->addError(_M('O empréstimo não pode ser renovado porque é do tipo momentâneo.', $this->module), $extraColumns);
            return false;
        }
        else
        {            
            if ( $personId )
            {
                $ok = $this->busPerson->checkAccessLibraryUnit($personId, $loan->libraryUnitId);
                if ( !$ok )
                {
                    $libraryName = $this->busLibraryUnit->getLibraryUnit($loan->libraryUnitId)->libraryName;
                    $this->addError(_M('Você não possui direito de retirar/renovar materiais da biblioteca @1', $this->module, $libraryName), $extraColumns);
                    return false;
                }
            }
            if ( $loan->returnDate )
            {
                $this->addError(_M('O empréstimo foi fechado na data @1.', $this->module, $loan->returnDate), $extraColumns);
                return false;
            }
            else
            {
                $loan->libraryUnit = $this->busLibraryUnit->getLibraryUnit($loan->libraryUnitId);
                $loan->materialGenderId = $this->busExemplaryControl->getMaterialGender($loan->itemNumber);
              
                $this->busPolicy->loanForecastDate = $loan->returnForecastDate;
                $loan->policy = $this->busPolicy->getPolicy($loan->privilegeGroupId, $loan->linkId, $loan->materialGenderId, TRUE, !MUtil::getBooleanValue(USE_LOAN_DATE_FOR_RENEW));

                if ( !$loan->policy->privilegeGroupId )
                {
                    $personLink = $this->busUserGroup->getUserGroup($loan->linkId);
                    $materialGenderObj = $this->busMaterialGender->getMaterialGender($loan->materialGenderId);

                    if ( !$materialGenderObj->description )
                    {
                        $this->addError(_M('Impossível encontrar descrição para gênero do material "@1".', $this->module, $loan->materialGenderId), $extraColumns);
                        return false;
                    }

                    $this->addError(_M('O grupo @1 não possui políticas para materiais do gênero @2.', $this->module, $personLink->description, $materialGenderObj->description), $extraColumns);
                    return false;
                }
                else
                {

                    if ( $this->getRenewType() == ID_RENEWTYPE_WEB || $this->getRenewType() == ID_RENEWTYPE_DEFAULTSIPEQUIPAMENT || $this->getRenewType() == ID_RENEWTYPE_OFFLINE)
                    {
                        $loan->link = $this->busBond->getPersonlink($loan->personId);
                        if ( !$loan->link->linkId ) //working right
                        {
                            $this->addError(_M('Você não possui vínculo.', $this->module), $extraColumns);
                            return false;
                        }
                        
                        $library = $this->busPerson->checkAccessLibraryUnit($loan->personId, $loan->libraryUnitId);
                        if ( !$library )
                        {
                            $this->addError(_M('Este material pertence a uma biblioteca que você não tem acesso.', $this->module), $extraColumns);
                            return false;
                        }
                        
                        $loan->fine = $this->busFine->getFineOpenOfAssociation($loan->libraryUnitId, $loan->personId);
                        if ( $loan->fine[0]->fineId )
                        {
                            $hasRightToDoLoanWithFine = $this->busRight->hasRight($loan->libraryUnitId, $loan->linkId, $loan->materialGenderId, ID_OPERATION_LOAN_FINE);
                            if ( !$hasRightToDoLoanWithFine )
                            {
                                $this->addError(_M('Você possui multas em aberto. Por favor, regularize sua situação com a biblioteca.', $this->module), $extraColumns);
                                return false;
                            }
                        }
                        
                        if ( $this->busLoan->amountLoansDelayOfAssociation($loan->libraryUnitId, $loan->personId) > 0 )
                        {
                            $hasRightToDoLoanWithDelayLoan = $this->busRight->hasRight($loan->libraryUnitId, $loan->linkId, $loan->materialGenderId, ID_OPERATION_LOAN_DELAY_LOAN);
                            if ( !$hasRightToDoLoanWithDelayLoan )
                            {
                                $this->addError(_M('Você possui empréstimos em atraso. Por favor, regularize sua situação com a biblioteca.', $this->module), $extraColumns);
                                return false;
                            }
                        }
                        
                        $loan->penalty = $this->busPenalty->getPenaltyOfAssociation($loan->libraryUnitId, $loan->personId);
                        if ( is_array($loan->penalty) && count($loan->penalty) > 0 )
                        {
                            $hasRightToDoLoanWithPenalty = $this->busRight->hasRight($loan->libraryUnitId, $loan->linkId, $loan->materialGenderId, ID_OPERATION_LOAN_PENALTY);
                            if ( !$hasRightToDoLoanWithPenalty )
                            {
                                $this->addError(_M('Você possui penalidades em aberto. Por favor, regularize sua situação com a biblioteca.', $this->module), $extraColumns);
                                return false;
                            }
                        }
                        
                        if ( $loan->renewalWebAmount <= 0 ) //working ??
                        {
                            $this->addError(_M('Não há mais renovações web para este empréstimo.', $this->module), $extraColumns);
                            return false;
                        }

                        $loan->reserve = $this->busReserve->hasReserve($loan->itemNumber, ID_RESERVESTATUS_REQUESTED);
                        if ( $loan->reserve )
                        {
                            $this->addError(_M('Este exemplar possui uma reserva, o que impede a renovação.', $this->module), $extraColumns);
                            return false;
                        }
                        
                        $forecastDate = $this->calculateForecastDate($loan->policy->forecastDate, $loan->policy->additionalDaysForHolidays, $loan->libraryUnit->libraryUnitId);
                        
                        
                        $forecastDate = new GDate($forecastDate);
                        $loanForecastDate = new GDate($loan->returnForecastDate);

                        $diff = $forecastDate->diffDates($loanForecastDate);

                        $busRenew = $this->MIOLO->getBusiness($this->module, 'BusRenew');
                        
                        $hj = GDate::now();
                        $diffHj = $loanForecastDate->diffDates($hj);
                        $dias = $diffHj->days;
                        
                        if ( $diff->days <= 0 || ($dias >= $loan->policy->loanDays) ) //Verifica para ver se a renovação terá efeito positivo
                        {
                            $this->addError(_M('Prazo de devolução não será aumentado.', $this->module), $extraColumns);
                            return false;
                        }
                        
                        $hasRightToDoLoan = $this->busRight->hasRight($loan->libraryUnitId, $loan->linkId, $loan->materialGenderId, ID_OPERATION_LOAN);

                        if ( !$hasRightToDoLoan )
                        {
                            $this->addError(_M('Você não tem direito a retirar materiais.', $this->module), $extraColumns);
                            return false;
                        }
                    }
                    elseif ( $this->getRenewType() == ID_RENEWTYPE_LOCAL )
                    {
                        if ( $loan->renewalAmount <= 0 )
                        {
                            $this->addError(_M('Limite de renovações para este empréstimo esgotou.', $this->module), $extraColumns);
                            return false;
                        }

                        $loan->reserve = $this->busReserve->hasReserve($loan->itemNumber, ID_RESERVESTATUS_REQUESTED);
                        if ( $loan->reserve )
                        {
                            $this->addError(_M('Este exemplar possui uma reserva, o que impede a renovação.', $this->module), $extraColumns);
                            return false;
                        }
                    }
                }
            }
        }
        return $loan;
    }

    public function addLoan($busLoan)
    {
        $_SESSION['itemsRenew'][] = $busLoan;
    }

    /**
     * retorna os emprestimos para renovação da sessao
     *
     * @param optional inteher $itemNumber
     * @return array
     */
    public function getLoans($itemNumber = NULL)
    {
        if ( is_null($itemNumber) )
        {
            return $_SESSION['itemsRenew'];
        }
        if ( !is_array($_SESSION['itemsRenew']) )
        {
            return null;
        }
        foreach ( $_SESSION['itemsRenew'] as $object )
        {
            if ( $object->itemNumber == $itemNumber )
            {
                return array( $object );
            }
        }
        return null;
    }

    /**
     * retorna os emprestimos para renovação da sessao
     *
     * @param optional inteher $itemNumber
     * @return array
     */
    public function getLoanIdFromItemNumber($itemNumber)
    {
        if ( !is_array($_SESSION['itemsRenew']) )
        {
            return false;
        }

        foreach ( $_SESSION['itemsRenew'] as $object )
        {
            if ( $object->itemNumber == $itemNumber )
            {
                return $object->loanId;
            }
        }

        return false;
    }

    public function calculateForecastDate($forecastDate, $additionalDaysForHolidays, $libraryUnitId)
    {
        //converte forecastDate para timestampUnix
        $timestampUnix = GDate::construct($forecastDate)->getTimestampUnix();
        //faz a verificação se é feriado ou se a biblioteca esta fechada
        $timestampUnix = $this->busHoliday->checkHolidayDate($timestampUnix, $additionalDaysForHolidays, $libraryUnitId);
        //converte novamente para dd/mm/yyyy
        $forecastDate = GDate::construct($timestampUnix)->getDate(GDate::MASK_DATE_DB);

        return $forecastDate;
    }

    /**
     *  Método para finalizar a renovação.
     * 
     * @param int $itemNumber Número do exemplar.
     * @param boolean $sendReceipt Define se recibo de renovação será enviado por e-mail.
     * @param boolean $printReceipt Defube se recibo de devolução será impresso.
     * @return boolean Caso positivo, renovação foi executada com sucesso.
     */
    public function finalize($itemNumber = NULL, $sendReceipt = FALSE, $printReceipt = FALSE)
    {
        // Verifica se ha emprestimos.
        $loans = $this->getLoans($itemNumber);

        if ( !$loans )
        {
            $this->addInformation(_M('Nenhum empréstimo foi renovado.', $module));
            return false;
        }

        foreach ( $loans as $loan )
        {
            $extraColumns['itemNumber'] = $loan->itemNumber;

            $check = $this->checkLoan($loan->loanId);
            if ( !$check )
            {
                if ( trim($check) )
                {
                    $this->addError($check, $extraColumns);
                }
                
                continue;
            }

            $this->busRenew->loanId = $loan->loanId;
            $this->busRenew->renewTypeId = $this->getRenewType();
            $this->busRenew->renewDate = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
            
            $this->busRenew->returnForecastDate = $loan->returnForecastDate;
            $this->busRenew->operator = GOperator::getOperatorId();

            $loanForecastDate = GDate::construct($loan->returnForecastDate)->getDate(GDate::MASK_DATE_USER);

            $loan->libraryUnit = $this->busLibraryUnit->getLibraryUnit($loan->libraryUnitId);
            $loan->materialGenderId = $this->busExemplaryControl->getMaterialGender($loan->itemNumber);

            $this->busPolicy->loanForecastDate = $loan->returnForecastDate;
            $loan->policy = $this->busPolicy->getPolicy($loan->privilegeGroupId, $loan->linkId, $loan->materialGenderId, TRUE, !MUtil::getBooleanValue(USE_LOAN_DATE_FOR_RENEW));

            $loan->returnForecastDate = $this->calculateForecastDate($loan->policy->forecastDate, $loan->policy->additionalDaysForHolidays, $loan->libraryUnit->libraryUnitId);
            $newLoanForecastDate = GDate::construct($loan->returnForecastDate)->getDate(GDate::MASK_DATE_USER);

            if ( $this->getRenewType() == ID_RENEWTYPE_WEB )
            {
                $loan->renewalWebAmount = $loan->renewalWebAmount - 1;
            }
            elseif ( $this->getRenewType() == ID_RENEWTYPE_LOCAL )
            {
                $loan->renewalAmount = $loan->renewalAmount - 1;

                // Se reserva for local e tiver bonus de reserva web, reinicia a contagem de renovações web permitidas.
                if ( MUtil::getBooleanValue($loan->policy->renewalWebBonus) == TRUE )
                {
                    $loan->renewalWebAmount = $loan->policy->renewalWebLimit;
                }
            }

            $busRenew = $this->MIOLO->getBusiness($this->module, 'BusRenew');
            
            if ( $this->busRenew->insertRenew() )
            {
               // $this->renewId = $this->busRenew->renewId;
                $this->busLoan->setData($loan);
                if ( $this->busLoan->updateLoan() )
                {
                    $this->addInformation(_M('O empréstimo foi renovado com sucesso para @1.', $module, GDate::construct($loan->returnForecastDate)->getDate(GDate::MASK_DATE_USER)), $extraColumns);
                }
                
                // Define data prevista de devolução no formato d/m/Y.
                $loan->returnForecastDate = $newLoanForecastDate;

                // Adiciona emprestimo ao recibo.
                $this->receipt->addItem(new LoanReceipt($loan, $sendReceipt, $printReceipt, "Renovação"));
            }
        }
        return true;
    }
}

?>