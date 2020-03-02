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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 **/
class FrmLoan extends GForm
{
    public $MIOLO;
    public $module;
    public $action;
    public $business;
    public $busLibraryUnit;
    public $busPolicy;
    public $busExemplaryControl;
    public $busPrivilegeGroup;
    public $busHoliday;

    public function __construct()
    {
        $MIOLO  = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $this->business             = $MIOLO->getBusiness($module, 'BusLoan');
        $this->busLibraryUnit       = $MIOLO->getBusiness($module, 'BusLibraryUnit');
        $this->busPolicy            = $MIOLO->getBusiness($module, 'BusPolicy');
        $this->busExemplaryControl  = $MIOLO->getBusiness($module, 'BusExemplaryControl');
        $this->busPrivilegeGroup    = $MIOLO->getBusiness($module, 'BusPrivilegeGroup');
        $this->busHoliday           = $MIOLO->getBusiness($module, 'BusHoliday');

        $save_args = array('loanId', 'loanTypeId', 'itemNumber', 'libraryUnitId');
        $this->setAllFunctions('Loan', null, array('loanId'), $save_args);

        parent::__construct();
    }


    public function mainFields()
    {
        $busLoanType = $this->MIOLO->getBusiness('gnuteca3', 'BusLoanType');

        if ( MIOLO::_REQUEST('function') == 'update')
        {
            $fields[] = new MTextField('loanId', '', _M('Código','gnuteca3'), FIELD_ID_SIZE, null, null, true);
        }

        $fields[] = new GSelection('loanTypeId', null, _M('Tipo de empréstimo',$this->module), $busLoanType->listLoanType(null, true));

        if ( MIOLO::_REQUEST('function') == 'insert')
        {
            $fields[] = new GPersonLookup('personId', _M('Pessoa', $this->modules), 'activePerson');
        }
        else //update
        {
            $fields[] = new GPersonLookup('personId', _M('Pessoa', $this->modules), 'person');
            
            $busBond = $this->manager->getBusiness($this->module, 'BusBond');
            $fields[] = new GSelection('linkId', '', _M('Vínculo'), $busBond->listBond(), false, false, false, true );
        }

        $fields[] = $itemNumber = new MTextField('itemNumber', null, _M('Número do exemplar', $this->module))     ;

        $itemNumber->addAttribute('onchange', 'javascript:'.GUtil::getAjax('libraryUnitOnChange') );
        $this->busLibraryUnit->filterOperator = TRUE;
        $fields[] = $libraryUnitId  = new GSelection('libraryUnitId',   $this->libraryUnitId->value, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnit(), null, null, null, true);
        $libraryUnitId->addAttribute('onchange', 'javascript:'.GUtil::getAjax('libraryUnitOnChange') );

        $divPrivilegeGroup = new MDiv('divPrivilegeGroup', array(new MTextField('privilegeGroup', null, _M('Grupo de privilégio', $this->module),10,'',null, true), new MHiddenField('privilegeGroupId')));
        $privilegeGroupLabel = new MLabel(_M('Grupo de privilégio', $this->module) );
        $renewalWebBonusOpt = array( DB_TRUE =>_M('Sim', $this->module), DB_FALSE =>_M('Não', $this->module));

        $fields[] = new GContainer(null, array($privilegeGroupLabel, $divPrivilegeGroup) );
        
        $fields[] = new MTimestampField('loanDate', GDate::now(), _M('Data', $this->module));
        $fields[] = new MTextField('loanOperator', GOperator::getOperatorId(), _M('Operador', $this->module),10,null, null, true);
        $fields[] = new MTimestampField('returnForecastDate', null, _M('Data prevista da devolução', $this->module));
        $fields[] = new MTimestampField('returnDate', null, _M('Data de devolução', $this->module));
        $fields[] = new MTextField('returnOperator', null, _M('Operador da devolução', $this->module));
        $fields[] = new MIntegerField('renewalAmount', null, _M('Quantidade de renovações', $this->module));
        $fields[] = new MIntegerField('renewalWebAmount', null, _M('Quantidade de renovações web', $this->module));
        $fields[] = new GSelection('renewalWebBonus', null, _M('Bônus de renovações web', $this->module), $renewalWebBonusOpt);

        $validators[] = new MRequiredValidator('loanTypeId');
        $validators[] = new MRequiredValidator('personId' );
        $validators[] = new MRequiredValidator('itemNumber');
        $validators[] = new MRequiredValidator('privilegeGroupId', _M('Grupo de privilégio', $this->module));
        $validators[] = new MRequiredValidator('loanDate');
        $validators[] = new MRequiredValidator('loanOperator');
        $validators[] = new MRequiredValidator('returnForecastDate');
        $validators[] = new MRequiredValidator('renewalAmount');
        $validators[] = new MRequiredValidator('renewalWebAmount');
        $validators[] = new MRequiredValidator('renewalWebBonus');

        $this->setFields($fields);
        $this->setValidators($validators);
    }


    /**
     * Atualiza privilegeGroup e outros dados caso existir
     *
     */
    public function libraryUnitOnChange()
    {
        $data = $this->getData();
        
        if ($data->libraryUnitId)
        {
            $libraryUnit = $this->busLibraryUnit->getLibraryUnit($data->libraryUnitId);

            $data->privilegeGroupId = $libraryUnit->privilegeGroupId;
            $privilegeGroup = $this->busPrivilegeGroup->getPrivilegeGroup($data->privilegeGroupId);

            $fields[] = new MTextField('privilegeGroup', $privilegeGroup->description, _M('Grupo de privilégio', $this->module), 10, '', null, true);
            $fields[] = new MHiddenField('privilegeGroupId', $data->privilegeGroupId);

            $data->materialGenderId = $this->busExemplaryControl->getMaterialGender($data->itemNumber);

            if ($data->libraryUnitId && $data->linkId && $data->materialGenderId)
            {
                $policy = $this->busPolicy->getPolicy($data->privilegeGroupId, $data->linkId, $data->materialGenderId);
                if ($policy->privilegeGroupId) //objeto retornado existe
                {
                    //converte forecastDate para timestampUnix
                    $timestampUnix = new GDate($policy->forecastDate);
                    $timestampUnix = $timestampUnix->getTimestampUnix();

                    //faz a verificação se é feriado ou se a biblioteca esta fechada
                    $timestampUnix = $this->busHoliday->checkHolidayDate($timestampUnix, $policy->additionalDaysForHolidays, $data->libraryUnitId);

                    //converte novamente para dd/mm/yyyy
                    $forecastDate = new GDate($timestampUnix);

                    $this->page->addJsCode("
                    document.getElementById('returnForecastDateDate').value = '{$forecastDate->getDate(GDate::MASK_DATE_USER)}';
                    document.getElementById('returnForecastDateTime').value = '{$forecastDate->getDate(GDate::MASK_TIME)}';
                    document.getElementById('returnForecastDate').value = '{$forecastDate->getDate(GDate::MASK_TIMESTAMP_USER)}';
                    document.getElementById('renewalAmount').value      = '{$policy->renewalLimit}';
                    document.getElementById('renewalWebAmount').value   = '{$policy->renewalWebLimit}';
                    document.getElementById('renewalWebBonus').value    = '{$policy->renewalWebBonus}';");
                }
            }
        }

        $this->setResponse($fields, 'divPrivilegeGroup');
    }

    public function tbBtnSave_click($sender)
    {
    	if (($sender->itemNumber) && ($this->busExemplaryControl->getExemplaryControl($sender->itemNumber)->libraryUnitId != $sender->libraryUnitId))
    	{
    		$errors[] = _M('O exemplar não pertence a biblioteca.', $this->module);
    	}

    	parent::tbBtnSave_click($sender, NULL, $errors);
    }
}
?>
