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
 * Fine form
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
 * Class created on 01/08/2008
 *
 **/
class FrmFine extends GForm
{
    public $MIOLO;
    public $module;
    public $function;
    public $busFineStatus;
    public $busLoan;


    public function __construct()
    {
        $this->MIOLO         = MIOLO::getInstance();
        $this->module        = MIOLO::getCurrentModule();
        $this->function      = MIOLO::_REQUEST('function');
        $this->busFineStatus = $this->MIOLO->getBusiness($this->module, 'BusFineStatus');
        $this->busLoan       = $this->MIOLO->getBusiness($this->module, 'BusLoan');
        $this->setAllFunctions('Fine', null, 'fineId', array('loanId', 'fineStatusId'));
        parent::__construct();
    }


    public function mainFields()
    {
        if ($this->function != 'insert')
        {
            $fields[]       = new MTextField('fineId', $this->fineId->value, _M('Código', $this->module), FIELD_ID_SIZE,null, null, true);
            $validators[]   = new MIntegerValidator('fineId');
            $fields[]       = new MHiddenField('fineId', $this->fineId->value);
        }

        $loanIdLabel = new MLabel(_M('Código do empréstimo', $this->module) . ':');
        $loanId = new GLookupTextField('loanId', '', '', FIELD_LOOKUPFIELD_SIZE);
        $loanId->setContext($this->module, $this->module, 'Loan', 'filler', 'loanId,loanIdDescription', '', true);
        $loanId->baseModule = $this->module;
        $loanIdDescription = new MTextField('loanIdDescription', $this->loanIdDescription, NULL, FIELD_DESCRIPTION_LOOKUP_SIZE);
        $loanIdDescription->setReadOnly(true);
        $validators[]   = new MIntegerValidator('loanId', _M('Código do empréstimo', $this->module), 'required');
        $fields[]       = new GContainer('loanIdContainer', array($loanIdLabel, $loanId, $loanIdDescription));

        $beginDate      = new MCalendarField('beginDate', $this->beginDate->value, _M('Data inicial', $this->module), FIELD_DATE_SIZE, null);
        $validators[]   = new MDateDMYValidator('beginDate');
        $validators[]   = new MRequiredValidator('beginDate');
        $fields[]       = $beginDate;

        $lblValue       = new MLabel(_M('Valor', $this->module) . ':');
        $fields[]       = new MFloatField('value', $this->value->value, _M('Valor', $this->module), FIELD_ID_SIZE);
        $validators[]   = new MRequiredValidator('value');

        $fields[]       = new MHiddenField('fineStatusIdCurrent');
        $fineStatusId   = new GSelection('fineStatusId', $this->fineStatusId->value, _M('Estado da multa', $this->module), $this->busFineStatus->listFineStatus());
        $date = GDate::now()->getDate(GDate::MASK_DATE_USER);
        $fineStatusId->addAttribute("onchange", "currentStatus = document.getElementById('fineStatusIdCurrent').value;
                                                 dijit.byId( 'endDate' ).attr( 'displayedValue', '{$date}' );
                                                 gnuteca.setDisplay('observationHistoric', true, this.value != currentStatus ? 'block' : 'none' );"
                                                    );
        $fields[]       = $fineStatusId;
        $validators[]   = new MRequiredValidator('fineStatusId');
        $fields[]       = new MSeparator();

        //gtcFineStatusHistory
        $fields[]= $observationHistoric = new MMultiLIneField ('observationHistoric', NULL, _M('Observação para troca de estado', $this->module), NULL, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        //esconde campo com a label
        $this->page->onload("gnuteca.setDisplay('observationHistoric', true, 'none');");

        $endDate      = new GContainer('contEndDate', array(new MLabel(_M('Data final', $this->module)), new MCalendarField('endDate', $this->endDate->value, null, FIELD_DATE_SIZE, null)));
        $validators[] = new MDateDMYValidator('endDate');
        $fields[]     = $endDate;
        $fields[] = new MMultiLineField('observation', null, _M('Observação', $this->module), NULL, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);

        $this->setFields($fields);
        $this->setValidators($validators);

        if ($this->function == 'update')
        {
            $this->_toolBar->addRelation(_M('Histórico'), GUtil::getImageTheme('info-16x16.png'), 'javascript:'.Gutil::getAjax('showDetail'));
        }
    }


    public function loadFields()
    {
        $data = $this->business->getFine( MIOLO::_REQUEST('fineId') );
        $data->value     = GUtil::floatToMoney($data->value);
        $data->fineStatusIdCurrent = $data->fineStatusId;
        if ( strlen($data->beginDate) > 0)
        {
        	$date = new GDate($data->beginDate);
        	$data->beginDate = $date->getDate(GDate::MASK_DATE_USER);
        }
        
        if ( strlen($data->endDate) > 0)
        {
            $date = new GDate($data->endDate);
            $data->endDate = $date->getDate(GDate::MASK_DATE_USER);
        }
        $this->setData($data);
    }


    public function tbBtnSave_click($sender)
    {
        if (!$this->busLoan->checkAccessLoan($sender->loanId))
        {
        	$errors[] = _M("Você não tem acesso à este empréstimo.", $this->module);
        }

        $data = $this->getData();
        $data->value = GUtil::moneyToFloat($data->value);
    	parent::tbBtnSave_click($sender, $data, $errors);
    }
    
    
    /**
     * Método ajax para setar a data final
     * 
     * @param $args
     */
    public function setEndDate($args)
    {
    	if ( $args->fineStatusId != $this->busFineStatus->getFineStatusOpen() )
    	{
            $date = GDate::now()->getDate(GDate::MASK_DATE_USER);
            $this->page->onload("dijit.byId( 'endDate' ).attr( 'displayedValue', '{$date}' );");
    	}

        $this->setResponse(null, 'limbo');
    }

    public function showDetail()
    {
        $this->MIOLO->uses( "/forms/FrmFineSearch.class.php", 'gnuteca3');
        FrmFineSearch::showDetail();
    }
}
?>
