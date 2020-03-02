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
 * Class Marc Tag Listing Form
 *
 * @author Luiz Gilberto Gregory Filho [luiz@solis.coop.br]
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
 * Class created on 08/04/2009
 *
 **/
class FrmRequestChangeExemplaryStatus extends GForm
{
	public $tables;
    public $busExemplaryStatus,
           $busLibraryUnit,
           $busOperation,
           $busStatus,
           $composition,
           $busReqChanExeStatus;

    public function __construct()
    {
        $MIOLO      = MIOLO::getInstance();
        $module     = MIOLO::getCurrentModule();
        $function   = MIOLO::_REQUEST('function');

        $this->busExemplaryStatus               = $MIOLO->getBusiness($module, 'BusExemplaryStatus');
        $this->busLibraryUnit                   = $MIOLO->getBusiness($module, 'BusLibraryUnit');
        $this->busOperation                     = $MIOLO->getBusiness($module, 'BusOperationRequestChangeExemplaryStatus');
        $this->busStatus                        = $MIOLO->getBusiness($module, 'BusRequestChangeExemplaryStatusStatus');
        $this->busReqChanExeStatus              = $MIOLO->getBusiness($module, 'BusRequestChangeExemplaryStatus');

        $this->setAllFunctions('RequestChangeExemplaryStatus', array(), 'requestChangeExemplaryStatusId', array());
        parent::__construct(null);
 
        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') )
        {
            $this->getRepetitiveField();
            $this->tables['requestChangeExemplaryStatusComposition']->clearData();
            // Cria um bus para poder setar o radiobutton confirm como falso.
            $data = $MIOLO->getBusiness($module, 'BusOperationRequestChangeExemplaryStatus');
            $this->setData($data);// O metodo setData garante que o confirm seja falso "Não".
        }
    }


    /**
     * Create Default Fileds for Search Form
     *
     * @return void
     */
    public function mainFields($sender)
    {
        $fields[] = new MTextField("requestChangeExemplaryStatusId",   $this->requestChangeExemplaryStatusId, _M("Código",          $this->module), FIELD_ID_SIZE);
        $fields[] = new GSelection('requestChangeExemplaryStatusStatusId',  $this->requestChangeExemplaryStatusStatusId->value, _M('Estado', $this->module), $this->busStatus->listRequestChangeExemplaryStatusStatus($this->statusId->value), NULL, NULL, NULL, TRUE);
        $fields[] = $oldEstado = new MTextField('oldRequestChangeExemplaryStatusStatusId');
        $oldEstado->addStyle('display', 'none');
        $fields[] = new GSelection('libraryUnitId',  $this->libraryUnitId->value, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnit(), NULL, NULL, NULL, TRUE);

        // PERSON LOOK UP
        $personLabel    = new MLabel(_M('Pessoa', $this->module) . ':');
        $personLabel    ->setWidth(FIELD_LABEL_SIZE);
        $personId       = new GLookupTextField('personId', '', '', FIELD_LOOKUPFIELD_SIZE);
        $personId->setContext($this->module, $this->module, 'activeperson', 'filler', 'personId,personIdDescription', '', true);
        $personId->baseModule = $this->module;
        $personIdDesc   = new MTextField('personIdDescription', $this->loanIdDescription, NULL, FIELD_DESCRIPTION_LOOKUP_SIZE);
        $personIdDesc   ->setReadOnly(true);
        $fields[]       = new GContainer('pContainer', array($personLabel, $personId, $personIdDesc) );

        // EXEMPLARY STATUS SELECT
        $listFutureExemplaryStatus = $this->busExemplaryStatus->listExemplaryStatus(null, true);
       
        $fields[] = new GSelection('futureStatusId', $this->futureStatusId->value, _M('Estado futuro', $this->module), $listFutureExemplaryStatus, NULL, NULL, NULL, TRUE);
        
        
        $fields[] = new MMultiLineField('observation',  null, _M('Observação', $this->module), FIELD_DESCRIPTION_SIZE, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE  );

        $fields[] = new MCalendarField('date',         $this->date->value,        _M('Data',          $this->module), FIELD_DATE_SIZE, null, 'calendar-win2k-1');
        $fields[] = new MCalendarField('finalDate',    $this->finalDate->value,   _M('Data final',    $this->module), FIELD_DATE_SIZE, null, 'calendar-win2k-1');

        $fields[] = new MTextField('discipline', $this->discipline->value, _M('Disciplina', $this->module), FIELD_DESCRIPTION_SIZE);

        $lbl = new MLabel(_M('Aprovar apenas um', $this->module) . ':');
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $aproveJustOne = new MRadioButtonGroup('aproveJustOne', $this->aproveJustOne->value, GUtil::listYesNo(1), 't', null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new GContainer('hctScheduleChangeStatusForRequest', array($lbl, $aproveJustOne));

        $fields[] = new MSeparator();
        $fields[] = $this->getRepetitiveField();
        
        $this->setFields( $fields );

        $this->setFormOptions();
    }


    /**
     * Cria a repetitive field do formulario.
     *
     * @return repetitive field
     */
    private function getRepetitiveField()
    {
        $this->tables['requestChangeExemplaryStatusComposition'] = new GRepetitiveField('requestChangeExemplaryStatusComposition', _M('Composição da requisição de alteração de estado do exemplar', $this->module));

        // FIELDS
        $tableFields[] = new MTextField('itemNumber', null, _M('Número do exemplar', $this->module), FIELD_DESCRIPTION_SIZE);
        $tableFields[] = $confirm = new MRadioButtonGroup('confirm', _M('Confirmar', $this->module), GUtil::listYesNo(1),'f');
        $this->tables['requestChangeExemplaryStatusComposition']->setFields($tableFields);

        // COLUMNS
        $columns[] = new MGridColumn( _M('Número do exemplar', $this->module), 'left', true, null, true, 'itemNumber' );
        $columns[] = new MGridColumn( _M('Old Item Number', $this->module), 'left', true, null, false, 'oldItemNumber' );
        $columns[] = new MGridColumn( _M('Confirmar',     $this->module), 'left', true, null, false, 'confirm' );
        $columns[] = new MGridColumn( _M('Confirmar',     $this->module), 'left', true, null, true, 'confirmDesc' );
        $this->tables['requestChangeExemplaryStatusComposition']->setColumns($columns);

        //VALIDATORS
        $repetitiveFieldsValidators[] = new MRequiredValidator      ('itemNumber', _M('Número do exemplar', $this->module));
        $repetitiveFieldsValidators[] = new GnutecaUniqueValidator  ('itemNumber', _M('Número do exemplar', $this->module));
        $this->tables['requestChangeExemplaryStatusComposition']->setValidators($repetitiveFieldsValidators);

        return $this->tables['requestChangeExemplaryStatusComposition'];
    }


    /**
     * Seta as opções do formulario
     *
     */
    private function setFormOptions()
    {
        $this->requestChangeExemplaryStatusId->setReadOnly(true);

        if($this->function == 'insert')
        {
            $this->requestChangeExemplaryStatusStatusId->setReadOnly(true);
            $this->requestChangeExemplaryStatusStatusId->setValue(REQUEST_CHANGE_EXEMPLARY_STATUS_REQUESTED);
        }

        $validators[] = new MRequiredValidator  ('libraryUnitId');
        $validators[] = new MRequiredValidator  ('personId');
        $validators[] = new MRequiredValidator  ('futureStatusId' );
        $validators[] = new MRequiredValidator  ('date');
        $validators[] = new MRequiredValidator  ('finalDate');
        $validators[] = new MRequiredValidator  ('discipline' );
        $this->setValidators($validators);
    }
    
    public function loadFields ()
    {
        $requestId = $this->requestChangeExemplaryStatusId->getValue();
        
        $request = $this->busReqChanExeStatus->getRequestChangeExemplaryStatus($requestId);
        
        $this->requestChangeExemplaryStatusId->setValue($request->requestChangeExemplaryStatusId);
        $this->requestChangeExemplaryStatusStatusId->setValue($request->requestChangeExemplaryStatusStatusId);
        $this->oldRequestChangeExemplaryStatusStatusId->setValue($request->requestChangeExemplaryStatusStatusId);
        $this->observation->setValue($request->observation);
        $this->personId->setValue($request->personId);
        $this->date->setValue($request->date);
        $this->futureStatusId->setValue($request->futureStatusId);
        $this->finalDate->setValue($request->finalDate);
        $this->aproveJustOne->setValue($request->aproveJustOne);
        $this->discipline->setValue($request->discipline);
        
        $composition = $request->requestChangeExemplaryStatusComposition;
        
        foreach ($composition as $valores)
        {
            $valores->oldItemNumber = $valores->itemNumber;
        }
        $this->tables['requestChangeExemplaryStatusComposition']->setData($composition);
    }

    /**
     * Metodo chamado ao clicar no botao btnSearch
     *
     * @return void
     */
    public function tbBtnSave_click($sender)
    {
        $this->mainFields();
        $function = MIOLO::_REQUEST('function');

        $formData       = $this->getData();
        $composition    = GRepetitiveField::getData('requestChangeExemplaryStatusComposition');
        $cmp            = array();

        if ( !$this->validate($data, $errors) )
        {
            return false;
        }


        if(is_array($composition))
        {
            foreach($composition as $index => $values)
            {

	        	$cmp[$index]->itemNumber    = $values->itemNumber;
                $cmp[$index]->oldItemNumber = $values->oldItemNumber;
                $cmp[$index]->confirm       = $values->confirm;
                $cmp[$index]->delete        = $values->removeData;
	        	$cmp[$index]->update        = $values->updateData;
	        	$cmp[$index]->insert        = $values->insertData;
                
            }
        }

        $ok = false;
        if ($function == 'insert')
        {
            $ok = $this->insertRequest($formData, $cmp);
        }
        elseif($function == 'update')
        {
            $ok = $this->updateRequest($formData, $cmp);
        }

        $optsYes    = array('event' => 'tbBtnNew_click', 'function' => $function);
        $gotoYes    = $this->MIOLO->getActionURL($this->module, $this->_action, null, $optsYes);
        $optsNo['function']                           = 'search';
        $optsNo['requestChangeExemplaryStatusIdS']    = $ok ? $this->busOperation->requestChangeExemplaryStatusId : '';
        $gotoNo     = $this->MIOLO->getActionURL($this->module, $this->_action, null, $optsNo);

        if(!$ok)
        {
            $this->error( $this->busOperation->getMsg(), null, _M('Erro',$this->module) );
            return false;
        }
        if ($function == 'insert')
        {
        	$this->question( MSG_RECORD_INSERTED, $gotoYes, $gotoNo);
        }
        else
        {
        	$this->information( MSG_RECORD_UPDATED, $gotoNo);
        }
    }


    /**
     * Insere requisição
     *
     * @param object $formData
     * @param simple array $composition
     * @return boolean
     */
    private function insertRequest($formData, $composition)
    {
        $this->busOperation->clean();
        $this->busOperation->setRequestChangeExemplaryStatusStatusId($formData->requestChangeExemplaryStatusStatusId);
        $this->busOperation->setLibraryUnit                         ($formData->libraryUnitId);
        $this->busOperation->setPersonId        ($formData->personId);
        $this->busOperation->setDate            ($formData->date);
        $this->busOperation->setFinalDate       ($formData->finalDate);
        $this->busOperation->checkComposition   ($composition);
        $this->busOperation->setFutureStatusId  ($formData->futureStatusId);
        $this->busOperation->setObservation     ($formData->observation);
        $this->busOperation->setAproveJustOne   ($formData->aproveJustOne);
        $this->busOperation->setDiscipline      ($formData->discipline);

        return $this->busOperation->insertRequest();
    }


    /**
     * Insere requisição
     *
     * @param object $formData
     * @param simple array $composition
     * @return boolean
     */
    private function updateRequest($formData, $composition)
    {
        $this->busOperation->clean();
        $this->busOperation->setRequestChangeExemplaryStatusId      ($formData->requestChangeExemplaryStatusId);
        $this->busOperation->setRequestChangeExemplaryStatusStatusId($formData->requestChangeExemplaryStatusStatusId);
        $this->busOperation->setLibraryUnit                         ($formData->libraryUnitId);
        $this->busOperation->setPersonId                            ($formData->personId);
        $this->busOperation->setDate                                ($formData->date);
        $this->busOperation->setFinalDate                           ($formData->finalDate);
        $this->busOperation->checkComposition                       ($composition);
        $this->busOperation->setFutureStatusId                      ($formData->futureStatusId);
        $this->busOperation->setObservation                         ($formData->observation);
        $this->busOperation->setAproveJustOne                       ($formData->aproveJustOne);
        $this->busOperation->setDiscipline                          ($formData->discipline);

        return $this->busOperation->updateRequest();
    }


    /**
     * Método reescrito para chamar o addToTable
     */
    public function forceAddToTable($args)
    {
        $this->addToTable($args, TRUE);
    }


    /**
     * Método reescrito para fazer o parse da repetivie de composição
     */
    public function addToTable( $args, $forceMod )
    {
        $item = $args->GRepetitiveField;
        if ( $item == 'requestChangeExemplaryStatusComposition' )
        {
            $args = $this->compositionParse($args);
        }

        if ( $forceMod )
        {
            parent::forceAddToTable($args);
        }
        else
        {
            parent::addToTable($args, $object, $errors);
        }
    }


    /**
     * Método para tratar o booleano
     *
     * @param (array) ou (object) com os dados da repetitive
     * @return (array) ou (object) com os dados da repetitive
     */
    public function compositionParse($data)
    {
        if (is_array($data))
        {
            $arr = array();
            foreach ($data as $val)
            {
                $arr[] = $this->compositionParse($val);
            }

            return $arr;
        }
        else if (is_object($data))
        {
        	$boolean = GUtil::listYesNo();
            $data->confirmDesc = $boolean[$data->confirm];

            return $data;
        }
    }

    /**
     * Faz com que o confirm do repetitiveField requestChangeExemplaryStatusComposition venha por padrão falso.
     * Seta os dados automaticamente no form.
     * 
     * @param stdClass $data objeto que vem do bus
     */
    public function setData($data)
    {
        $data->confirm = DB_FALSE;
        parent::setData($data, true);
    }
}

?>
