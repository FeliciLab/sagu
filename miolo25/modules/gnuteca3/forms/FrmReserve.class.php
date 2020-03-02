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
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 **/

class FrmReserve extends GForm
{
    public $busLU;
    public $busRS;
    public $busRT;
    public $busExemplaryControl;
    public $tables;


    function __construct()
    {
        global $MIOLO, $module;
        $this->busLU = $MIOLO->getBusiness($module, 'BusLibraryUnit');
        $this->busRS = $MIOLO->getBusiness($module, 'BusReserveStatus');
        $this->busRT = $MIOLO->getBusiness($module, 'BusReserveType');
        $this->busExemplaryControl = $MIOLO->getBusiness($module, 'BusExemplaryControl');

        $saveArgs = array('libraryUnitId', 'personId', 'reserveStatusId', 'reserveTypeId');
        $this->setAllFunctions('Reserve', null, array('reserveId'), $saveArgs);
        parent::__construct();
        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') )
        {
            GRepetitiveField::clearData('reserveComposition');
        }
    }


    public function mainFields()
    {
        if ($this->function != 'insert')
        {
            $reserveId = new MTextField('reserveId', null, _M('Código', $this-module));
            $reserveId->setReadOnly(true);
            $fields[] = $reserveId;
            $data = new StdClass();
            $data->reserveId = MIOLO::_REQUEST('reserveId');
        }
       
        $fields[] = new GPersonLookup('personId', _M('Pessoa', $this->modules), 'person');

        $fields[]               = new MHiddenField('requestedDate');
        $lblRequestedDate       = new MLabel(_M('Data/Hora da solicitação', $this->module) . ':');
        $lblRequestedDate->setWidth(FIELD_LABEL_SIZE);
        $requestedDate_DATE     = new MCalendarField('requestedDate_DATE', GDate::now()->getDate(GDate::MASK_DATE_DB), '', FIELD_DATE_SIZE);
        $requestedDate_TIME     = new MTimeField('requestedDate_TIME', substr(GDate::now()->getDate(GDate::MASK_TIME), 0, 5), '', FIELD_TIME_SIZE);
        $fields[]               = new GContainer('hctRequestedDate', array($lblRequestedDate, $requestedDate_DATE, $lblRequestedDate_TIME, $requestedDate_TIME));

        $this->busLU->filterOperator = TRUE;
        $fields[]       = new GSelection('libraryUnitId',   $this->libraryUnitId->value, _M('Unidade de biblioteca', $this->module), $this->busLU->listLibraryUnit(), null, null, null, true);
        $fields[]       = new MCalendarField('limitDate', null, _M('Data limite',$this->module),FIELD_DATE_SIZE);
        $fields[]       = new GSelection('reserveStatusId', null, _M('Estado da reserva', $this->module), $this->busRS->listReserveStatus());
        $fields[]       = new GSelection('reserveTypeId', null, _M('Tipo de reserva', $this->module), $this->busRT->listReserveType());
        $controls[]     = new MTextField('itemNumber', null, _M('Número do exemplar',$this->module) );

        $controls[] = new GRadioButtonGroup('isConfirmed', _M('Está confirmado', $this->module), GUtil::listYesNo(1), DB_FALSE, '', MFormControl::LAYOUT_HORIZONTAL);
        $controls[] = new MHiddenField('isConfirmedLabel');
        $reserveComposition = new GRepetitiveField('reserveComposition', _M('Reservar composição', $this->module), NULL, NULL, array('edit', 'remove'));
        $reserveComposition->setFields($controls);
        $columns[] = new MGridColumn( _M('Número do exemplar',   $this->module), 'left', true, "20%", true, 'itemNumber' );
        $columns[] = new MGridColumn( _M('Está confirmado',  $this->module), 'left', true, "64%", true, 'isConfirmedLabel' );
        $columns[] = new MGridColumn( _M('Está confirmado',  $this->module), 'left', true, null, false, 'isConfirmed' );
        $_valids[] = new GnutecaUniqueValidator('itemNumber', _M('Número do exemplar', $this->module), 'required');
        $_valids[] = new MRequiredValidator('isConfirmed');
        $reserveComposition->setColumns($columns);
        $reserveComposition->setValidators($_valids);
        $fields[] = $reserveComposition;
        $fields[] = new MSeparator();

        $valids[] = new MDateDMYValidator('requestedDate_DATE', _M('Data da solicitação', $this->module) . ' (' . _M('Data', $this->module) . ')', 'required');
        $valids[] = new MRequiredValidator('personId', _M('Pessoa', $this->module));
        $valids[] = new MRequiredValidator('libraryUnitId');
        $valids[] = new MRequiredValidator('reserveStatusId');
        $valids[] = new MRequiredValidator('reserveTypeId');

        $this->setFields($fields);
        $this->setValidators($valids);
    }
    public function forceAddToTable($args)
    {
        $this->addToTable($args);
    }

    public function addToTable($args)
    {
        $args->isConfirmedLabel = GUtil::getYesNo($args->isConfirmed);
        $data = null;
        $i    = null;
        $data[$i]->isConfirmedLabel = GUtil::getYesNo($data[$i]->isConfirmed);
	    parent::addToTable($args);
    }

    /*
     * Método reescrito em função dos campos :
     * requestedDate_DATE
     * requestedDate_TIME
     */
    public function loadFields()
    {
        $data = $this->business->getReserve( MIOLO::_REQUEST('reserveId') );
        //Convert values to user like format
        $requestedDate = new GDate($data->requestedDate);
        $data->requestedDate_DATE = $requestedDate->getDate(GDate::MASK_DATE_USER);
        $data->requestedDate_TIME = substr($requestedDate->getDate(GDate::MASK_TIME), 0, 5);
        $limitDate = new GDate($data->limitDate);
        $data->limitDate = $limitDate->getDate(GDate::MASK_DATE_USER);
        $data->isConfirmed = DB_FALSE;
        $this->setData($data,true);
    }


    public function tbBtnSave_click($sender=NULL)
    {
        $data = $this->getData();
        $data->reserveComposition = GRepetitiveField::getData('reserveComposition');
        $data->requestedDate = $data->requestedDate_DATE . ' ' . $data->requestedDate_TIME;

        if ($data->reserveComposition)
        {
            foreach ($data->reserveComposition as $item)
            {
                $library = $this->busExemplaryControl->getExemplaryControl($item->itemNumber)->libraryUnitId;
            	if (!$library || $sender->libraryUnitId != $library)
            	{
            		$errors[] = _M('O exemplar: @1', $this->module, $item->itemNumber.' não existe ou não pertence a esta biblioteca.');
            	}
            }
        }
        elseif (!$data->reserveComposition)
        {
        	$errors['reserveComposition'] = _M('Você precisa adicionar algum exemplar na composição da reserva', $this->module);
        }

        parent::tbBtnSave_click($sender, $data, $errors);
    }


    public function parseReserveComposition($data)
    {
        for ($i=0; $i < count($data); $i++)
        {
            $data[$i]->isConfirmedLabel = GUtil::getYesNo($data[$i]->isConfirmed);
        }
        return $data;
    }
}
?>
