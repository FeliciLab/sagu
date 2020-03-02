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
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 04/08/2008
 *
 **/
class FrmPolicy extends GForm
{
    public $session;
    public $business;

    function __construct()
    {
        $this->session = new MSession('policy');
        $this->setAllFunctions('Policy', 'privilegeGropId', array('privilegeGroupId', 'linkId', 'materialGenderId'), array('privilegeGroupId', 'linkId', 'materialGenderId'));
        
        parent::__construct();
    }

    public function mainFields()
    {
        $businessPrivilegeGroup = $this->MIOLO->getBusiness($this->module, 'BusPrivilegeGroup');
        $businessLink = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $busMaterialGender = $this->MIOLO->getBusiness($this->module, 'BusMaterialGender');
        
        if ( $this->function != 'update' )
        {
            $fields[] = new MMultiSelection('linkList', array(null), _M('Vínculo',$this->module), $businessLink->listBond(true), null, null, 5);
            $fields[] = new GSelection('privilegeGroupId', null, _M('Grupo de privilégio',$this->module), $businessPrivilegeGroup->listPrivilegeGroup());
            $fields[] = new MMultiSelection('materialGenderList', array(null), _M('Gênero do material',$this->module), $busMaterialGender->listMaterialGender(), null, null, 5);
            
            $validators[] = new MRequiredValidator('linkList');
            $validators[] = new MRequiredValidator('privilegeGroupId');
            $validators[] = new MRequiredValidator('materialGenderList');
        }
        else
        {
            $fields[] = new MTextField('linkId', null, _M('Código do vínculo',$this->module), FIELD_ID_SIZE,null, null, true);
            $fields[] = new MTextField('privilegeGroupId', null, _M('Código do grupo de privilégio',$this->module), FIELD_ID_SIZE,null, null, true);
            $fields[] = new MTextField('materialGenderId', null, _M('Código do gênero do material',$this->module), FIELD_ID_SIZE,null, null, true);
        }
        
        $fields[] = new MCalendarField('loanDate', null, _M('Data de empréstimo',$this->module) );
        $fields[] = new MTextField('loanDays', null, _M('Dias de empréstimo',$this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('loanLimit', null, _M('Limite de empréstimo',$this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('renewalLimit', null, _M('Limite de renovação',$this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('reserveLimit', null, _M('Limite de reserva',$this->module), FIELD_ID_SIZE);
        $fields[] = $fineValue = new MFloatField('fineValue', null, _M('Valor da multa',$this->module), FIELD_ID_SIZE);
        $fineValue->setHint(_M('Valor aplicado por dia.','gnuteca3'));
        $fields[] = $momentaryFineValue = new MFloatField('momentaryFineValue', null, _M('Multa momentânea',$this->module), FIELD_ID_SIZE);
        $fields[] = $penaltyByDelay = new MTextField('penaltyByDelay', null, _M('Penalidade por atraso',$this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('daysOfWaitForReserve', null, _M('Dias de espera por reserva',$this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('reserveLimitInInitialLevel', null, _M('Limite de reserva de nível inicial', $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('daysOfWaitForReserveInInitialLevel', null, _M('Dias de espera por reserva no nível inicial', $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('renewalWebLimit', null, _M('Limite de renovações web',$this->module), FIELD_ID_SIZE);
        $fields[] = new GRadioButtonGroup('renewalWebBonus', _M('Bônus de renovação web', $this->module), GUtil::listYesNo(1), DB_FALSE, null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new MTextField('additionalDaysForHolidays', $this->additionalDaysForHolidays->value, _M('Adicional de dias para feriado',$this->module), FIELD_ID_SIZE);
        
        $penaltyByDelay->setHint(_M('Número de dias aplicado para cada dia de atraso.',$this->module));
        
        if ( defined('LOAN_MOMENTARY_PERIOD') )
        {
            $momentaryFineValue->setHint( _M('Valor aplicado por @1.',$this->module,(LOAN_MOMENTARY_PERIOD == 'H' )? 'hora':'dia'));
        }
        

        $validators[] = new MDateDMYValidator('loanDate');
        $validators[] = new MIntegerValidator('loanDays');
        $validators[] = new MIntegerValidator('loanLimit');
        $validators[] = new MIntegerValidator('renewalLimit');
        $validators[] = new MIntegerValidator('reserveLimit');
        $validators[] = new MFloatValidator('penaltyByDelay');
        $validators[] = new MIntegerValidator('daysOfWaitForReserve');
        $validators[] = new MIntegerValidator('reserveLimitInInitialLevel');
        $validators[] = new MIntegerValidator('daysOfWaitForReserveInInitialLevel');
        $validators[] = new MIntegerValidator('renewalWebLimit');
        $validators[] = new MIntegerValidator('additionalDaysForHolidays');

        $this->setFields($fields);
        $this->setValidators($validators);
    }

    public function tbBtnSave_click($sender=null)
    {
        $busMaterialGender = $this->MIOLO->getBusiness($this->module, 'BusMaterialGender');
        $busUserGroup = $this->MIOLO->getBusiness($this->module, 'BusUserGroup');
        
    	$data = $this->getData();

    	//Policies
    	if ( MIOLO::_REQUEST('update_repeat') )
    	{
            $data = $this->session->get('data');
            $data->update_repeat = MIOLO::_REQUEST('update_repeat');
            $this->session->set('data', null);
    	}
    	else
    	{
            $policies = array();
            
            foreach ( (array)$data->materialGenderList as $materialGenderId )
            {
                foreach ( (array)$data->linkList as $linkId )
                {
                    $policy = $this->business->getPolicy($data->privilegeGroupId, $linkId, $materialGenderId, TRUE);
                    
                    if ( $policy )
                    {
                    	$link = $busUserGroup->getUserGroup($policy->linkId)->description;
                    	$materialGender = $busMaterialGender->getMaterialGender($policy->materialGenderId)->description;
                        $policies[] = "{$link} - {$materialGender}";
                    }
                }
            }

            if ($policies)
            {
            	$policies = implode(', ', $policies);

            	//Save form data on session
                $this->session->set('data', $data);

                $args['event']         = __FUNCTION__;
                $args['function']      = $this->function;
                $args['update_repeat'] = DB_TRUE;
                $gotoYes = $this->MIOLO->getActionURL($this->module, $this->_action, NULL, $args);
                $this->question(_M("As políticas a seguir já existem: <br>@1<br> Você quer atualizá-las?", $this->module, $policies), $gotoYes);
                return;
            }
    	}

    	parent::tbBtnSave_click($sender, $data);
    }
}
?>