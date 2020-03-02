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
 * Interchange form
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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 20/02/2009
 *
 **/
class FrmInterchange extends GForm
{
    public $MIOLO;
    public $module;
    public $busInterchangeType;
    public $busInterchangeStatus;
    public $busSupplierTypeAndLocation;
    public $busMaterial;
    public $_interchangeItem;
    public $_interchangeObservation;

    public function __construct()
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busInterchangeType         = $this->MIOLO->getBusiness($this->module, 'BusInterchangeType');
        $this->busInterchangeStatus       = $this->MIOLO->getBusiness($this->module, 'BusInterchangeStatus');
        $this->busSupplierTypeAndLocation = $this->MIOLO->getBusiness($this->module, 'BusSupplierTypeAndLocation');
        $this->busMaterial                = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        $this->setAllFunctions('Interchange', 'supplierId', 'interchangeId', array('interchangeId'));
        
        parent::__construct();

        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') )
        {
            GRepetitiveField::clearData('_interchangeItem');
            GRepetitiveField::clearData('_interchangeObservation');
        }
    }


    public function mainFields()
    {
        $defaultTypeId = INTERCHANGE_TYPE_SEND;

        if ($this->function == 'update')
        {
            $interchangeId = new MTextField('interchangeId', NULL, _M('Código', $this->module), FIELD_ID_SIZE);
            $interchangeId->setReadOnly(TRUE);
            $fields[] = $interchangeId;
        }
        $validators[] = new MIntegerValidator('interchangeId');
        $defaultType = 'p'; //Permuta
        $type = new GSelection('type', $defaultType, _M('Tipo', $this->module), $this->business->listTypes(), null, null, null, TRUE);
        $type->addAttribute('onchange', GUtil::getAjax('changeType'));
        $validators[] = new MRequiredValidator('supplierId');
        $fields[] = $type;
        $fields[] = $supplier = new MDiv('divSupplier', $this->getSupplierField($defaultType));
        $supplier->addStyle('float','left');
        $fields[] = new MCalendarField('date', GDate::now()->getDate(GDate::MASK_DATE_DB), _M('Data', $this->module), FIELD_DATE_SIZE);
        $validators[] = new MDATEDMYValidator('date', null, 'required');
        $interchangeTypeId = new GSelection('interchangeTypeId', $defaultTypeId, _M('Tipo de permuta', $this->module), $this->busInterchangeType->listInterchangeType(), null, null, null, TRUE);
        $interchangeTypeId->addAttribute('onchange', "javascript:".GUtil::getAjax('changeStatus') );
        $interchangeTypeId->addAttribute('onkeydown', "javascript:".GUtil::getAjax('changeStatus') );
        $fields[] = $interchangeTypeId;
        $fields[] = new GContainer('status', array($this->getInterchangeStatusField($defaultTypeId)));
        $operator = new MTextField('operator', GOperator::getOperatorId(), _M('Operador', $this->module));
        $operator->setReadOnly(TRUE);
        $fields[] = $operator;

        //Interchange item
        $columns    = null;
        $flds       = null;
        $valids     = null;

        $itemNumber = new MTextField('_number', null, _M('Número', $this->module), FIELD_ID_SIZE);
        $flds[] = $itemNumber;
        
        $options    = array(
            array(_M('Número de controle', $module), 'controlNumber'),
            array(_M('Número do exemplar', $module), 'itemNumber'),
        );
        $gRadioGroup = new MRadioButtonGroup('numberType', ' ', $options, 'controlNumber', null, 'vertical');
        $flds[] = new GContainer('container', $gRadioGroup );
        
        if (!GForm::getEvent() || (GForm::getEvent() == 'tbBtnNew:click')) //selecionar por default controlNumber (nao funciona no repetitiveField) 
        {
            $this->page->onload("dojo.byId('numberType_1').checked = 'checked'");
        }

        $flds[] = new MHiddenField('interchangeItemId');
        $columns[] = new MGridColumn(_M('Número de controle', $this->module), MGrid::ALIGN_LEFT, true, true, true,  'controlNumber');
        $columns[] = new MGridColumn(_M('Título', $this->module),          MGrid::ALIGN_LEFT, true, true, true,  'contentTitle');
        $columns[] = new MGridColumn(null,                                MGrid::ALIGN_LEFT, true, true, false, 'interchangeItemId');
        $valids[] = new MIntegerValidator('controlNumber', _M('Número de controle', $this->module), 'required');
        $opts = array('remove', 'noUpdateButton');
        $interchangeItem = new GRepetitiveField('_interchangeItem', _M('Itens', $this->module), $columns, $flds, $opts);
        //$interchangeItem->setUpdateButton(TRUE);
        $interchangeItem->setValidators($valids);
        $fields[] = $interchangeItem;

        //Interchange observation
        $columns    = null;
        $flds       = null;
        $valids     = null;

        $flds[] = new MCalendarField('obs_date', GDate::now(), _M('Data', $this->module), FIELD_DATE_SIZE);
        $flds[] = new MSeparator();
        $flds[] = new MMultiLineField('observation', null, _M('Observação', $this->module), NULL, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $flds[] = new MSeparator();
        $operator = new MTextField('operator', GOperator::getOperatorId(), _M('Operador', $this->module));
        $operator->setReadOnly(TRUE);
        $flds[] = $operator;
        $flds[] = $interchangeObservationId = new MTextField('interchangeObservationId');
        $interchangeObservationId->addStyle('display','none');
        $columns[] = new MGridColumn(_M('Data', $this->module),        MGrid::ALIGN_LEFT, true, true, true,  'obs_date');
        $columns[] = new MGridColumn(_M('Observação', $this->module), MGrid::ALIGN_LEFT, true, true, true,  'observation');
        $columns[] = new MGridColumn(_M('Operador', $this->module),    MGrid::ALIGN_LEFT, true, true, true,  'operator');
        $columns[] = new MGridColumn( null, MGrid::ALIGN_LEFT, true, true, false, 'interchangeObservationId');
        $fields[] = $interchangeObservation = new GRepetitiveField('_interchangeObservation', _M('Observação', $this->module), $columns, $flds);
        $interchangeObservation->setUpdateButton(TRUE);
        $interchangeObservation->setDefaultValue( 'obs_date', GDate::now()->getDate(GDate::MASK_DATE_USER) );

        $this->setFields($fields);
        $this->setValidators($validators);
	}


    public function changeStatus($sender)
    {
        $this->setResponse($this->getInterchangeStatusField($sender->interchangeTypeId), 'status');
    }


    public function getInterchangeStatusField($interchangeTypeId)
    {
        $lblES = new MLabel( _M('Estado', $this->module) . ':' );
        $lblES->setWidth(FIELD_LABEL_SIZE);
        $interchangeStatusId = new GSelection('interchangeStatusId', null, null, $this->busInterchangeStatus->listInterchangeStatus($interchangeTypeId), null, null, null, TRUE);
        $hct = new GContainer('hctInterchangeStatus', array($lblES, $interchangeStatusId));
        $hct->addStyle('width', '100%');
        return $hct;
    }


    public function changeType($sender)
    {
    	$fields[] = $this->getSupplierField($sender->type);
        $this->setResponse($fields, 'divSupplier');
    }


    public function getSupplierField($type = NULL)
    {
        $lbl = new MLabel (_M('Fornecedor:',$this->module));
        $lbl->width = FIELD_LABEL_SIZE;
        $supplierId = new GLookupTextField ('supplierId','','',FIELD_LOOKUPFIELD_SIZE);
        $supplierId->setContext($this->module, $this->module, 'supplierType', 'filler', 'supplierId,supplierIdDescription', '', true);
        $supplierId->baseModule = 'gnuteca3';
        $supplierIdDescription = new MTextField ('supplierIdDescription','',null, FIELD_DESCRIPTION_LOOKUP_SIZE);
        $supplierIdDescription->setReadOnly(true);
        $supplierIdContainer = new GContainer('supplierIdContainer', array ($lbl, $supplierId, $supplierIdDescription,));
        return $supplierIdContainer;
    }


    public function loadFields()
    {
    	$this->business->getInterchange( MIOLO::_REQUEST('interchangeId') );
    	$date = new GDate($this->business->date);
    	$this->business->date = $date->getDate(GDate::MASK_DATE_USER);
        $this->business->numberType = 'itemNumber'; //força o dado para número de exemplar no editar
    	$this->setData($this->business);

    	//InterchangeItem
        if ($this->business->interchangeItem)
        {
            foreach ($this->business->interchangeItem as $i => $v)
            {
                $this->business->interchangeItem[$i]->contentTitle = $v->content;
            }
        }
    	$this->_interchangeItem->setData($this->business->interchangeItem);

    	//InterchangeObservation
        if ($this->business->interchangeObservation)
        {
        	foreach ($this->business->interchangeObservation as $i => $v)
        	{
        		$this->business->interchangeObservation[$i]->obs_date = GDate::construct($this->business->interchangeObservation[$i]->date)->getDate(GDate::MASK_DATE_USER);
        	}
        }

        $this->_interchangeObservation->setData($this->business->interchangeObservation);

        if (MIOLO::_REQUEST('function') == 'update')
        {
            $this->getControlById('interchangeStatusId')->options = $this->busInterchangeStatus->listInterchangeStatus($this->business->interchangeTypeId);
        }
    }


    public function tbBtnSave_click($sender)
    {
    	$this->addValidator(new MRequiredValidator('supplierId'));
        $this->mainFields();
        $data = $this->getData();

        //caso for insert, obtem novo id
        if ($this->function == 'insert')
        {
            $data->interchangeId = $this->business->getNextId();
            $session = new MSession();
            $session->set('interchangeId', $data->interchangeId);
        }

        $data->interchangeItem = $this->_interchangeItem->getData();

        //trata itens
        if ($data->interchangeItem)
        {
            foreach ($data->interchangeItem as $i => $v)
            {
            	$data->interchangeItem[$i]->content = $v->contentTitle;
            }
        }

        $data->interchangeObservation = $this->_interchangeObservation->getData();

        //trata observações
        if ( $data->interchangeObservation )
        {
        	foreach ($data->interchangeObservation as $i => $v)
        	{
                $data->interchangeObservation[$i]->date = $v->obs_date;
        	}
        }

    	parent::tbBtnSave_click($sender, $data);
    }


    public function forceAddToTable($args)
    {
    	$this->addToTable($args, TRUE);
    }


    public function addToTable($args, $forceMode = TRUE)
    {
        $errors = array();
    	if ($args->GRepetitiveField == '_interchangeItem')
    	{
            if ($args->numberType == 'controlNumber')
            {
                $args->controlNumber = $args->_number;
                $args->contentTitle = $this->busMaterial->getContentTag($args->_number, MARC_TITLE_TAG);
                if (!$args->contentTitle)
                {
                    $errors[] = _M('Número de controle não encontrado', $this->module);
                }
            }
            else //itemNumber
            {
                $args->controlNumber = $this->busMaterial->getContentByItemNumber($args->_number, MARC_CONTROL_NUMBER_TAG);
                $args->contentTitle  = $this->busMaterial->getContentByItemNumber($args->_number, MARC_TITLE_TAG);
                if (!$args->controlNumber || !$args->contentTitle)
                {
                    $errors[] = _M('Número do exemplar não encontrado', $this->module);
                }
            }
    	}
        
        $error = null;
        if ( count($errors) > 0 )
        {
            $error = $errors;
        } 
        
        ($forceMode) ? parent::forceAddToTable($args, null, $error) : parent::addToTable($args, null, $error);
    }
}
?>
