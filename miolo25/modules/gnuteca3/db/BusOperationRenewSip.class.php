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
$MIOLO->usesBusiness($module, 'BusOperationRenew');

class BusinessGnuteca3BusOperationRenewSip extends BusinessGnuteca3BusOperationRenew
{
    public $renew;
    public $loanID;
    public $operationDate;
    public $msgs;
    
    public function addLoan($busLoan)
    {
        $this->renew[] = $busLoan;
    }
    
    public function getLoans($itemNumber = NULL)
    {
        return $this->renew;
    }
    
    public function finalize($itemNumber = NULL, $sendReceipt = FALSE, $printReceipt = FALSE, $offline = FALSE)
    {
        $module = MIOLO::getCurrentModule();
        
        // Verifica se ha emprestimos.
        $loans = $this->getLoans($itemNumber);
        $this->loanID = $loans->loanId;
        
        if ( !$loans )
        {
            $this->addInformation(_M('Desculpe, nenhum empréstimo foi renovado.', $module));
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
            $this->busRenew->renewTypeId = ID_RENEWTYPE_DEFAULTSIPEQUIPAMENT;
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
            
            if ( $this->getRenewType() == ID_RENEWTYPE_WEB || $this->getRenewType() == ID_RENEWTYPE_DEFAULTSIPEQUIPAMENT || $this->getRenewType() == ID_RENEWTYPE_OFFLINE)
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

            if ( $var = $this->busRenew->insertRenew() )
            {
                $this->loanID = $this->busRenew->renewId;
                $this->busLoan->setData($loan);
                
                if ( $this->busLoan->updateLoan() )
                {
                    $this->addInformation(_M('Sucesso! Seu empréstimo foi renovado.'));
                }
                
                $this->operationDate = $this->busLoan->loanDate;

                // Define data prevista de devolução no formato d/m/Y.
                $loan->returnForecastDate = $newLoanForecastDate;
                
                // Adiciona emprestimo ao recibo.
                $this->receipt->addItem(new LoanReceipt($loan, TRUE, FALSE, "Renovação"));
            }
        }
        return true;
    }
    
    /* Comentado pois sobrescreve o método que retorna o erro
     
    function addError($message )
    {
        $this->msgs[] = '[ERRO] - ' . $message;
    }
     */
    
    function addInformation($message )
    {
        $this->msgs[] = '[Informação] - ' . $message;
    }
    
    function addAlert($message )
    {
        $this->msgs[] = '[Aviso] - ' . $message;
    }
}