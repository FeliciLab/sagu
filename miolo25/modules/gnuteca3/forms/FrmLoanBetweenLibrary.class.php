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
 * Class created on 16/12/2008
 *
 * */
class FrmLoanBetweenLibrary extends GForm
{

    public $busExemplaryControl;
    public $busLibraryUnit;
    public $busLoanBetweenLibrary;
    public $busLoanBetweenLibraryComposition;
    public $busLoanBetweenLibraryStatus;
    public $busOperationLoanBetweenLibrary;
    public $_libraryComposition;

    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();

        $this->busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busLoanBetweenLibrary = $this->MIOLO->getBusiness($this->module, 'BusLoanBetweenLibrary');
        $this->busLoanBetweenLibraryComposition = $this->MIOLO->getBusiness($this->module, 'BusLoanBetweenLibraryComposition');
        $this->busLoanBetweenLibraryStatus = $this->MIOLO->getBusiness($this->module, 'BusLoanBetweenLibraryStatus');
        $this->busOperationLoanBetweenLibrary = $this->MIOLO->getBusiness($this->module, 'BusOperationLoanBetweenLibrary');

        $this->setAllFunctions('LoanBetweenLibrary', 'loanBetweenLibraryId', array('loanBetweenLibraryId', 'libraryUnitId', 'personId', 'loanBetweenLibraryStatusId'), array('personId'));

        parent::__construct();

        if ($this->primeiroAcessoAoForm() && ($this->function != 'update'))
        {
            $this->_libraryComposition->clearData();
        }

        $this->mainFields();
    }

    public function mainFields()
    {
        if ($this->function == 'update')
        {
            $fields[] = new MTextField('loanBetweenLibraryId', null, _M('Código', $this->module), FIELD_ID_SIZE, null, null, true);
            $validators[] = new MRequiredValidator('loanBetweenLibraryId');
        }

        //$this->busLibraryUnit->filterOperator = TRUE;
        //$fields[]       = new GSelection('libraryUnitId', null, _M('Library unit', $this->module), $this->busLibraryUnit->listLibraryUnit(), null, null, null, TRUE);
        $fields[] = new MHiddenField('libraryUnitId', GOperator::getLibraryUnitLogged());
        $fields[] = new MCalendarField('loanDate', GDate::now()->getDate(GDate::MASK_DATE_DB), _M('Data do empréstimo', $this->module), FIELD_DATE_SIZE);
        $fields[] = new MCalendarField('returnForecastDate', null, _M('Data prevista da devolução', $this->module), FIELD_DATE_SIZE);
        $fields[] = new MCalendarField('limitDate', null, _M('Data limite', $this->module), FIELD_DATE_SIZE);

        $validators[] = new MDATEDMYValidator('loanDate', null, 'required');
        $validators[] = new MDATEDMYValidator('returnForecastDate', null, 'required');

        $fields[] = new GPersonLookup('personId', _M('Pessoa', $this->modules), 'person');

        if ($this->function == 'update')
        {
            $fields[] = new MCalendarField('returnDate', null, _M('Data de devolução', $this->module), FIELD_DATE_SIZE);
            $validators[] = new MDATEDMYValidator('returnDate');
            $datas = $this->business->getLoanBetweenLibrary(MIOLO::_REQUEST('loanBetweenLibraryId'));
            $loanStatus = $this->busLoanBetweenLibraryStatus->getLoanBetweenLibraryStatus($datas->loanBetweenLibraryStatusId);
            $fields[] = new MHiddenField('loanBetweenLibraryStatusId', $datas->loanBetweenLibraryStatusId);
            $fields[] = new MTextField('loanBetweenLibraryStatusIdDesc', $loanStatus->description, _M('Estado', $this->module), FIELD_DESCRIPTION_LOOKUP_SIZE, null, null, true);
        }
        else
        {
            $fields[] = new MHiddenField('loanBetweenLibraryStatusId', ID_LOANBETWEENLIBRARYSTATUS_REQUESTED);
        }

        $fields[] = new MMultiLineField('observation', null, _M('Observação', $this->module), null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);

        //libraryComposition (gtcLoanBetweenLibraryComposition)
        unset($controls, $columns);
        $controls[] = new MTextField('itemNumber', null, _M('Número do exemplar', $this->module));



        $lbl = new MLabel(_M('Está confirmado', $this->module) . ':');
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $isConfirmed = new MRadioButtonGroup('isConfirmed', _M('Está confirmado', $this->module), GUtil::listYesNo(1), 'f', null, MFormControl::LAYOUT_HORIZONTAL);
        $controls[] = $isConfirmed;

        $controls[] = new MHiddenField('isConfirmedLabel');
        $columns[] = new MGridColumn(_M('Número do exemplar', $this->module), MGrid::ALIGN_LEFT, true, null, true, 'itemNumber');
        $columns[] = new MGridColumn(_M('Está confirmado', $this->module), 'left', true, null, false, 'isConfirmed');
        $columns[] = new MGridColumn(_M('Está confirmado', $this->module), 'left', true, "64%", true, 'isConfirmedLabel');
        $columns[] = new MGridColumn(_M('Código da biblioteca', $this->module), MGrid::ALIGN_LEFT, true, null, false, 'libraryUnitId');
        $this->_libraryComposition = new GRepetitiveField('libraryComposition', _M('Exemplar', $this->module), null, null, array('edit', 'remove'));
        $validat[] = new GnutecaUniqueValidator('itemNumber', _M('Número do exemplar', $this->module), 'unique');
        $this->_libraryComposition->setValidators($validat);

        $this->_libraryComposition->setColumns($columns);
        $this->_libraryComposition->setFields(array(new MVContainer('hctLC', $controls)));
        $fields[] = $this->_libraryComposition;
        $this->setFields($fields);
        $this->setValidators($validators);
    }

    public function forceAddToTable($data)
    {
        $this->addToTable($data);
    }

    public function addToTable($data)
    {
        if ($data->itemNumber)
        {
            $data->libraryUnitId = $this->busExemplaryControl->getExemplaryControl($data->itemNumber)->libraryUnitId;
            $data->libraryName = $this->busLibraryUnit->getLibraryUnit($data->libraryUnitId)->libraryName;
            $data->isConfirmedLabel = GUtil::getYesNo($data->isConfirmed);
            parent::addToTable($data);
        }
        else if ($data->controlNumber)
        {
            $objName = $data->GRepetitiveField;
            $exemplaryes = $this->busExemplaryControl->getExemplaryOfMaterial($data->controlNumber);
            if ($exemplaryes)
            {
                foreach ($exemplaryes as $ex)
                {
                    unset($tmp);
                    $tmp->itemNumber = $ex->itemNumber;
                    $tmp->isConfirmed = $ex->isConfirmed;
                    $tmp->libraryUnitId = $this->busExemplaryControl->getExemplaryControl($ex->itemNumber)->libraryUnitId;
                    $tmp->libraryName = $this->busLibraryUnit->getLibraryUnit($ex->libraryUnitId)->libraryName;
                    GRepetitiveField::addData($tmp, $objName);
                }
                //unset($_SESSION['GRepetitiveField'][$object]);
                $this->setResponse(GRepetitiveField::generate(false, $objName), "div{$objName}");
            }
            else
            {
                $errors[] = _M('Nenhum número de exemplar encontrado para este número de controle');
                autoAddAction(null, null, $errors);
            }
        }
    }

    public function tbBtnSave_click($args = NULL)
    {
        $itens = ($_SESSION[GRepetitiveField][libraryComposition]);
        if (($count = count($itens)) > 1)
        {
            $itemNumbers = array();

            foreach ($itens as $key => $value)
            {
                //Verifica se itemnumber existe no $itemNumbers
                if (in_array($value->itemNumber, $itemNumbers))
                {
                    //Remove itemnumber
                    unset($itens[$key]);
                }
                else
                {
                    //Adiciona itemnumber no array
                    $itemNumbers[] = $value->itemNumber;
                }
            }
        }

        $_SESSION[GRepetitiveField][libraryComposition] = $itens;
        $data = (object) $_REQUEST;
        $data->libraryComposition = GRepetitiveField::getData('libraryComposition');

        $function = MIOLO::_REQUEST('function');
        $errors = array();

        if (!$data->libraryComposition)
        {
            $this->error(_M('Composição de sua requisição é inválida.', $this->module));
            return;
        }

        foreach ($data->libraryComposition as $index => $item)
        {
            //Continua se não tiver itemNumber
            //E também se for excluído itemNumber. Como o GRepetitiveField não exclui, só esconde itemNumber
            if (!strlen($item->itemNumber) || ($item->removeData))
            {
                continue;
            }

            $exemplary = $this->busExemplaryControl->getExemplaryControl($item->itemNumber);

            if (!$exemplary)
            {
                $errors[] = _M('O exemplar @1 é inválido!', $this->module, $item->itemNumber);
                continue;
            }

            // não permite solicitar emprestimo de material da mesma unidade
            if ((!$exemplary->libraryUnitId || $data->libraryUnitId == $exemplary->libraryUnitId) && ($function == 'insert'))
            {
                $errors[] = _M('O número de exemplar: @1. Você não pode solicitar um material da sua unidade de biblioteca.', $this->module, $item->itemNumber);
                continue;
            }

            //Não solicitar materiais desaparecido ou danificado
            if ($exemplary->exemplaryStatusId == DEFAULT_EXEMPLARY_STATUS_DESAPARECIDO)
            {
                $errors[] = _M('O exemplar: @1', $this->module, $item->itemNumber . ' está desaparecido .');
                continue;
            }
            elseif ($exemplary->exemplaryStatusId == DEFAULT_EXEMPLARY_STATUS_DANIFICADO)
            {
                $errors[] = _M('O exemplar: @1', $this->module, $item->itemNumber . ' está danificado.');
                continue;
            }
            //Não solicitar materiais já aprovados por outras unidades
            $confirm = $this->busLoanBetweenLibrary->getLoanBetweenLibraryConfirm($item->itemNumber);

            if ($confirm)
            {
                $errors[] = _M('O exemplar: @1', $this->module, $item->itemNumber . ' foi aprovado por outra unidade.');
                continue;
            }
        }

        $this->insertFunction = 'insertRequest';
        $this->updateFunction = 'updateRequest';

        parent::tbBtnSave_click($args, $data, $errors, $this->busOperationLoanBetweenLibrary);
    }

    /**
     * Enter description here...
     *
     */
    public function loadFields()
    {
        $data = $this->business->getLoanBetweenLibrary(MIOLO::_REQUEST('loanBetweenLibraryId'));

        //loanDate
        $date = new GDate($this->business->loanDate);
        $this->business->loanDate = $date->getDate(GDate::MASK_DATE_USER);

        //returnForecastDate
        $date = new GDate($this->business->returnForecastDate);
        $this->business->returnForecastDate = $date->getDate(GDate::MASK_DATE_USER);

        //limitDate
        $date = new GDate($this->business->limitDate);
        $this->business->limitDate = $date->getDate(GDate::MASK_DATE_USER);

        //returnDate
        $date = new GDate($this->business->returnDate);
        $this->business->returnDate = $date->getDate(GDate::MASK_DATE_USER);

        $this->setData($this->business);

        //setData na RepetitiveField
        $this->_libraryComposition->setData($this->parseLoanBetweenLibraryComposition($data->libraryComposition));
    }

    public function parseLoanBetweenLibraryComposition($data)
    {
        for ($i = 0; $i < count($data); $i++)
        {
            $data[$i]->isConfirmedLabel = GUtil::getYesNo($data[$i]->isConfirmed);
        }
        return $data;
    }

}

?>
