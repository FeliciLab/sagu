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
 * Renew form
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 28/07/2008
 *
 * */

/**
 * Form to manipulate a preference
 * */
class FrmRenew extends GForm
{

    public $MIOLO;
    public $module;
    public $busLoan;
    public $busRenew;
    public $busRenewType;

    function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busLoan = $this->MIOLO->getBusiness($this->module, 'BusLoan');
        $this->busRenew = $this->MIOLO->getBusiness($this->module, 'BusRenew');
        $this->busRenewType = $this->MIOLO->getBusiness($this->module, 'BusRenewType');
        $this->setAllFunctions('Renew', null, array('renewId'), array('loanId', 'renewTypeId', 'operator'));
        parent::__construct();
    }

    public function mainFields()
    {
        if ($this->function != "insert")
        {
            $fields[] = new MTextField('renewId', $this->renewId->value, _M('Código', $this->module), FIELD_ID_SIZE, null, null, true);

            $validators[] = new MIntegerValidator('renewId');
            $validators[] = new MRequiredValidator('renewId');
            $fields[] = new MHiddenField('renewDate', $this->renewDate->value);
        }
        else
        {
            $fields[] = new MHiddenField('renewDate', GDate::now()->getDate(GDate::MASK_DATE_DB));
        }

        $fields[] = new GPersonLookup('loanId', _M('Código do empréstimo', $this->modules), 'Loan');
        $validators[] = new MRequiredValidator('loanId');
        
        
        $fields[] = new GSelection('renewTypeId', $this->renewTypeId->value, _M('Tipo de renovação', $this->module), $this->busRenewType->listRenewType());
        $fields[] = new MCalendarField('returnForecastDate', $this->returnForecastDate->value, _M('Data prevista da devolução', $this->module), FIELD_DATE_SIZE, null);
        $fields[] = new MTextField('operator', GOperator::getOperatorId(), _M('Operador', $this->module), null, null, true);

        $this->setFields($fields);

        $validators[] = new MDateDMYValidator('returnForecastDate');
        $validators[] = new MRequiredValidator('returnForecastDate');
        $validators[] = new MRequiredValidator('operator');
        $validators[] = new MRequiredValidator('renewTypeId');

        $this->setValidators($validators);
    }

    public function tbBtnSave_click($sender)
    {
        if (($sender->loanId) && (!$this->busLoan->checkAccessLoan($sender->loanId)))
        {
            $errors[] = _M('Você não tem acesso a este empréstimo.', $this->module);
        }
        
        $loan = $this->busLoan->getLoan($sender->loanId);

        $this->busRenew->loanId = $loan->loanId;
        $this->busRenew->renewTypeId = $sender->renewTypeId;
        $this->busRenew->renewDate = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);

        $this->busRenew->returnForecastDate = $loan->returnForecastDate;
        $this->busRenew->operator = GOperator::getOperatorId();

        if ( $renewId = $this->busRenew->insertRenew() )
        {
            $loan->returnForecastDate = $sender->returnForecastDate;
            $this->busLoan->setData($loan);
            if ( $this->busLoan->updateLoan() )
            {
                $function = MIOLO::_REQUEST('function');
                $optsYes    = array( 'event'=>'tbBtnNew_click', 'function' => $function, 'pn_page' => 1 );
                $gotoYes    = $this->MIOLO->getActionURL($this->module, $this->_action, null, $optsYes);
                $optsNo     = array( 'function'=>'search' , 'pn_page' => 1, 'renewIdS' => $renewId[0][0]);
                
                $gotoNo = $this->MIOLO->getActionURL($this->module, $this->_action, null, $optsNo);
                
                $this->question( MSG_RECORD_INSERTED , $gotoYes, $gotoNo);
            }
        }
    }
    
    public function getData()
    {
        $data = parent::getData();
        $data->loanId = MIOLO::_REQUEST('loanId');
        
        return $data;
    }

}
?>