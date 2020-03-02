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
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
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
 * Class created on 29/07/2008
 *
 **/
class FrmPurchaseRequest extends GForm
{
    /** @var BusinessGnuteca3BusPurchaseRequest*/
    public $business, $module;
    private  $busLibraryUnit, $busSupplier;

    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busLibraryUnit = $this->MIOLO->getBusiness('gnuteca3', 'BusLibraryUnit');
        $this->busSupplier = $this->MIOLO->getBusiness('gnuteca3', 'BusSupplier');
        
        $this->setAllFunctions('PurchaseRequest', array('libraryUnitId', 'personId', 'costCenterId', 'amount'), 'purchaseRequestId', array('libraryUnitId', 'personId', 'costCenterId', 'amount'));
        $this->setWorkflow( 'PURCHASE_REQUEST' );
        parent::__construct();
      
        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') )
        {
            GRepetitiveField::clearData('quotation');
        }
    }


    public function mainFields()
    {
        if ( $this->function != 'insert' )
        {
            $fields[] = new MTextField('purchaseRequestId', '', _M('Código', $this->module), FIELD_ID_SIZE,null, null, true);
        }
        
        $this->busLibraryUnit->filterOperator = true;
        $fields[] =  new GSelection('libraryUnitId', null, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnit(), null, null, null, true);
         
        $fields[] = new GPersonLookup('personId', _M('Pessoa', $this->modules), 'person');
        
        $lookup = array();
        $lookup[] = new MTextField('costCenterIdDescription', '', '', FIELD_DESCRIPTION_LOOKUP_SIZE, '', null, true);
        $fields[] = new GLookupField('costCenterId', '', _M('Centro de custo', $this->module), 'costCenter', $lookup );
        $fields[] = new MSeparator('<br>');
        
        //campos dinâmicos
        $purchaseFields = $this->business->parseFieldsPurchaseRequest();
        
        if ( is_array($purchaseFields) )
        {
            foreach ( $purchaseFields as $i=> $value )
            {
                $pFields[] = new MTextField('dinamic' . $value->id, null, $value->label . ":", FIELD_DESCRIPTION_SIZE, $value->hint);
             
                if ( $value->required == DB_TRUE )
                {
                    $validators[]   = new MRequiredValidator('dinamic' . $value->id);
                }
            }
            
            $fields[] = new MBaseGroup('workInformation', _M('Informações da obra', $this->module), $pFields, 'vertical', 'css', MControl::FORM_MODE_SHOW_NBSP);
        }
        
        $fields[] = new MSeparator('<br>');
        $fields[] = new MIntegerField('amount', null, _M('Quantidade', $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('course', null, _M('Curso',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MCalendarField('needDelivery', null, _M('Data de necessidade de entrega', $this->module));
        $fields[] = new MCalendarField('forecastDelivery', null, _M('Previsão de entrega', $this->module));
        $fields[] = new MCalendarField('deliveryDate', null, _M('Data de entrega', $this->module));
        $fields[] = new MIntegerField('voucher', null, _M('Nota fiscal', $this->module), FIELD_ID_SIZE);
        $fields[] = new MMultiLineField('observation', null, _M('Observação', $this->module), NULL, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $fields[] = new MTextField('controlNumber', null, _M('Número de controle',$this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('preControlNumber', null, _M('Numero da pŕe-catalogação',$this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('externalId', null, _M('Código externo',$this->module), FIELD_ID_SIZE);

        //validadores
        $validators[]   = new MRequiredValidator('libraryUnitId');
        $validators[]   = new MRequiredValidator('personId');
        $validators[]   = new MRequiredValidator('amount');

        $fields[] = $quotation = new GRepetitiveField('quotation', _M('Cotação', $this->module) );
        
        $supplierId = new GLookupTextField ('supplierId','','',FIELD_LOOKUPFIELD_SIZE);
   
        $lookup = array();
        $lookup[] = new MTextField ('supplierIdDescription','',null, FIELD_DESCRIPTION_LOOKUP_SIZE,null, null, true);
        $quotationFields[] = new MDiv('contSupplierId', array(new GLookupField('supplierId', null, _M('Fornecedor', $module), 'supplierType', $lookup)));
        
        $quotationFields[] = new MFloatField('value', null, _M('Valor:', $this->module), FIELD_ID_SIZE);
        $quotationFields[] = new MMultiLineField('observationQ', null, _M('Observação:', $this->module), NULL, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        
        $quotation->setFields($quotationFields);

        $quotationValidators[] = new GnutecaUniqueValidator('supplierId',_M('Fornecador','gnuteca3') );
        $quotationValidators[] = new MRequiredValidator('supplierIdDescription',_M('Nome do Fornecedor','gnuteca3') );
        $quotationValidators[] = new MRequiredValidator('value', _M('Valor','gnuteca3'));
        $quotation->setValidators( $quotationValidators );

        $columns[] = new MGridColumn( _M('Fornecedor', $this->module), 'left', true, '', false, 'supplierId');
        $columns[] = new MGridColumn( _M('Fornecedor', $this->module),      'left', true, '', true, 'supplierIdDescription');
        $columns[] = new MGridColumn( _M('Valor', $this->module),      'left', true, '', true, 'value');
        $columns[] = new MGridColumn( _M('Observação', $this->module),      'left', true, '', true, 'observationQ');
        $quotation->setColumns($columns);

        $this->setFields($fields);
        $this->setValidators($validators);
    }

    public function tbBtnSave_click($sender = null)
    {
        $data = $this->getData(true);
        
        //separa os campos dinâmicos
        $newData = new stdClass();
        $dinamicData = new stdClass();
        foreach ( $data as $i=>$nData )
        {
            if ( strpos($i, 'dinamic') === 0 )
            {
                $key = str_replace('_', '.', str_replace('dinamic', '', $i));
                $dinamicData->$key = $nData;
            }
            
            $newData->$i = $nData;
        }
        
        //trata os dados da repetitivefield
        if ( is_array($newData->quotation) )
        {
            foreach ( $newData->quotation as $i=> $quotation )
            {
                $newData->quotation[$i]->observation = $quotation->observationQ;
            }
        }
        
        $newData->dinamicFields = $dinamicData;
        
        parent::tbBtnSave_click($sender, $newData);
    }
    
    public function loadFields()
	{
        $data = $this->business->getPurchaseRequest(MIOLO::_REQUEST('purchaseRequestId'));
        $this->business->quotation = $this->purchaseRequestQuotationParse($data->quotation);
        $this->setData( $this->business, true);
        
        //seta os dados dinâmicos
        if ( is_array($this->business->dinamicFields) )
        {
            foreach( $this->business->dinamicFields as $key => $dinamicFields )
            {
                $fieldId = 'dinamic' . $dinamicFields->fieldId . '_' . $dinamicFields->subfieldId;

                if ( $this->fields[$fieldId] )
                {
                    $this->fields[$fieldId]->setValue($dinamicFields->content);
                }
                else
                {
                    throw new Exception ( _M("Campo @1 não existe", 'gnuteca3', $fieldId ) );
                }
            }
        }
        
	}
    
    public function addToTable($args, $forceMode = false)
    {
        $repetitive = $args->GRepetitiveField;
        $arrayItem = $args->arrayItemTemp;
        
        $errors = array();
        $data = GRepetitiveField::getData($repetitive);
        if ( is_array($data) )
        {
            foreach( $data as $key => $value )
            {
                //identifica o item da repetitive
                if ( ($value->arrayItem == $arrayItem) && ($args->__mainForm__EVENTTARGETVALUE == 'addToTable') )
                {
                    if ( $value->supplierId != $args->supplierId )
                    {
                        $errors[] = _M('O fornecedor não pode ser alterado', $this->module);
                    }
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

    public function forceAddToTable($args)
    {
        $this->addToTable($args, true);
    }
    
    /**
     * Trata os dados da multa ao adicionar um valor
     * 
     * @param $data
     */    
    function purchaseRequestQuotationParse($data)
    {
        if (is_array($data))
        {
            $arrData = array();
            for ($i=0, $c=count($data); $i < $c; $i++)
            {
                $arrData[] = $this->purchaseRequestQuotationParse($data[$i]);
            }
            return $arrData;
        }
        else if (is_object($data))
        {
            if ($data->libraryUnitId1)
            {
                $data->libraryUnitId = $data->libraryUnitId1;
            }
            
            $data->supplierIdDescription = $this->busSupplier->getSupplier($data->supplierId)->name;
            
            if ( !$data->observationQ )
            {
                $data->observationQ = $data->observation;
            }
            
            return $data;
        }
    }
}
?>