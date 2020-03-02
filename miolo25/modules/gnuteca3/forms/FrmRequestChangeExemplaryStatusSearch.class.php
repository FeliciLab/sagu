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
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 08/04/2009
 *
 * */
class FrmRequestChangeExemplaryStatusSearch extends GForm
{

    public $busExemplaryStatus,
            $busLibraryUnit,
            $busRequestChangeExemplaryStatusStatus,
            $busOperation,
            $busRequestChangeExemplaryStatus;

    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $this->busExemplaryStatus = $MIOLO->getBusiness($module, 'BusExemplaryStatus');
        $this->busLibraryUnit = $MIOLO->getBusiness($module, 'BusLibraryUnit');
        $this->busOperation = $MIOLO->getBusiness($module, 'BusOperationRequestChangeExemplaryStatus');
        $this->busRequestChangeExemplaryStatusStatus = $MIOLO->getBusiness($module, 'BusRequestChangeExemplaryStatusStatus');
        $this->busRequestChangeExemplaryStatus = $MIOLO->getBusiness($module, 'BusRequestChangeExemplaryStatus');

        $this->setAllFunctions('RequestChangeExemplaryStatus', array('requestChangeExemplaryStatusIdS'), array('requestChangeExemplaryStatusId'));
        parent::__construct();
    }

    public function mainFields()
    {
        $fields[] = new MTextField("requestChangeExemplaryStatusIdS", $this->requestChangeExemplaryStatusId->value, _M("Código", $this->module), FIELD_ID_SIZE);

        // EXEMPLARY STATUS SELECT
        $options = $this->busRequestChangeExemplaryStatusStatus->listRequestChangeExemplaryStatusStatus(false, true);
        $fields[] = new GSelection('requestChangeExemplaryStatusStatusIdS', $this->requestChangeExemplaryStatusStatusIdS->value, _M('Estado', $this->module), $options, null, null, null, false);

        $this->busLibraryUnit->filterOperator = TRUE;
        $this->busLibraryUnit->labelAllLibrary = TRUE;
        $fields[] = new GSelection('libraryUnitIdS', $this->libraryUnitId->value, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnit(), null, null, null, TRUE);

        $fields[] = $personId = new GPersonLookup('personIdS', _M('Pessoa', $this->modules), 'Person');


        $listFutureExemplaryStatus = $this->busExemplaryStatus->listExemplaryStatus(false, false, true);

        $fields[] = new GSelection('exemplaryStatusIdS', $this->exemplaryStatusIdS->value, _M('Estado futuro', $this->module), $listFutureExemplaryStatus, null, null, null, false);
        $lblDate = new MLabel(_M('Data', $this->module) . ':');
        $beginDateS = new MCalendarField('beginDateS', $this->beginDateS->value, null, FIELD_DATE_SIZE);
        $endDateS = new MCalendarField('endDateS', $this->endDateS->value, null, FIELD_DATE_SIZE);
        $fields[] = new GContainer('', array($lblDate, $beginDateS, $endDateS));
        $lblDate = new MLabel(_M('Data final', $this->module) . ':');
        $beginFinalDateS = new MCalendarField('beginFinalDateS', $this->beginFinalDateS->value, null, FIELD_DATE_SIZE);
        $endFinalDateS = new MCalendarField('endFinalDateS', $this->endFinalDateS->value, null, FIELD_DATE_SIZE);
        $fields[] = new GContainer('', array($lblDate, $beginFinalDateS, $endFinalDateS));
        $fields[] = new MTextField('disciplineS', $this->discipline->value, _M('Disciplina', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('itemNumberS', null, _M('Exemplar', $this->module), FIELD_DESCRIPTION_SIZE);

        $this->setFields($fields);
    }

    public function cancelRequest()
    {
        $args['_id'] = MIOLO::_REQUEST('_id');
        $this->question(_M('Tem certeza que deseja cancelar a requisição?'), 'javascript:' . GUtil::getAjax("confirmCancelRequest", $args));
    }

    public function confirmCancelRequest($args)
    {
        $this->busOperation->clean();
        $this->busOperation->getRequest(MIOLO::_REQUEST('_id'));
        $ok = $this->busOperation->cancelRequest(MIOLO::_REQUEST('_id'));
        $this->popupMessages(_M('Mensagem da operação de cancelamento', $this->module));
    }

    /**
     * Pergunta para o usuário o que quer confirmar
     *
     * @param $args
     * @return unknown_type
     */
    public function aproveRequest($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $busExemplaryControl = $MIOLO->getBusiness($module, 'BusExemplaryControl');
        $args = (Object) $_REQUEST;
        $event = $this->getEvent();

        $requestId = $args->_id;
        $this->busOperation = new BusinessGnuteca3BusOperationRequestChangeExemplaryStatus(); //para autocomplete no Zend
        $this->busOperation->clean();
        $request = $this->busOperation->getRequest($requestId, true);

        if ($request->requestChangeExemplaryStatusStatusId != REQUEST_CHANGE_EXEMPLARY_STATUS_REQUESTED)
        {
            $this->error(_M('Sua solicitação não pode ser aprovada pois seu estado é ' . $request->requestChangeExemplaryStatusStatusDesc));
            return;
        }

        //FIXME não aceitou TAMANHO_CAMPO_DESCRICAO tive que por tamanho hardcode
        $date = new GDate($request->date);
        $finalDate = new GDate($request->finalDate);
        $fields[] = new MTextField(null, $request->requestChangeExemplaryStatusId, _M('Codigo'), 40, null, null, true);
        $fields[] = new MTextField(null, $request->requestChangeExemplaryStatusStatusId . ' - ' . $request->requestChangeExemplaryStatusStatusDesc, _M('Estado da solicitação'), 40, null, null, true);
        $fields[] = new MTextField(null, $request->futureStatusId . ' - ' . $request->futureStatusIdDesc, _M('Estado futuro'), 40, null, null, true);
        $fields[] = new MTextField(null, $request->libraryUnitId . ' - ' . $request->libraryName, _M('Unidade de biblioteca'), 40, null, null, true);
        $fields[] = new MTextField(null, $request->personId . ' - ' . $request->personNane, _M('Pessoa'), 40, null, null, true);
        $fields[] = new MTextField(null, $date->getDate(GDate::MASK_TIMESTAMP_USER) . ' - ' . $finalDate->getDate(GDate::MASK_TIMESTAMP_USER), _M('Data inicial / final'), 40, null, null, true);
        $lbl = new MLabel(_M('Aprovar apenas um', $this->module) . ':');
        $defineRule = new MRadioButtonGroup('aproveJustOnde', null, GUtil::listYesNo(1), $request->aproveJustOne, null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new GContainer('hctDefineRule', array($lbl, $defineRule));
        $fields[] = new MTextField(null, $request->discipline, _M('Disciplina'), 40, null, null, true);
        $tableData = null;
        $tableTitles = array(_M('Selecione', $module), _M('Número do exemplar', $module), _M('Dados', $module), _M('Estado', $module), _M('Detalhes', $module));

        $agendamento = false; //sem agendamentos no inicio, para ver se mostra ou não mensagem de agendamento

        if (is_array($request->requestChangeExemplaryStatusComposition))
        {
            foreach ($request->requestChangeExemplaryStatusComposition as $line => $composition)
            {
                $exemplary = $busExemplaryControl->getExemplaryControl($composition->itemNumber, true); //pega o estado o ItemNumber
                $data = new GDate($composition->date);

                $tableData[$line][0] = new MCheckBox('check[' . $composition->itemNumber . ']', 't', null, $composition->confirm == DB_TRUE ? true : false);
                $tableData[$line][1] = $composition->itemNumber;
                $tableData[$line][2] = $data->getDate(GDate::MASK_TIMESTAMP_USER);
                $tableData[$line][3] = $exemplary->exemplaryStatusDescription;

                $busExemplaryControl = $MIOLO->getBusiness('gnuteca3', 'BusExemplaryControl');
                $busSearchFormat = $MIOLO->getBusiness('gnuteca3', 'BusSearchFormat');
                $controlNumber  = $busExemplaryControl->getControlNumber($composition->itemNumber);

                $tableData[$line][4] = $busSearchFormat->getFormatedString( $controlNumber , FAVORITES_SEARCH_FORMAT_ID );

                if ($exemplary->currentStatus->level != ID_EXEMPLARYSTATUS_INITIAL)
                {
                    $agendamento = true; //mostra mensagem de agendamento
                }
            }
            //FIXME
            $fields[] = new MDiv(null, '<br/><br/>');

            if ($agendamento)
            {
                $fields[] = new MDiv('', REQUEST_CHANGE_STATUS_SCHEDULED_MSG, 'reportDescription');
            }

            $fields[] = $table = new MTableRaw(_M('Composição', $module), $tableData, $tableTitles, 'composition');
            $fields[] = $id = new MTextField('_id', $requestId);
            $id->addStyle('display', 'none');

            $buttons[0] = new MButton('btnYes', _M('Ok'), ':confirmAproveRequest', GUtil::getImageTheme('accept-16x16.png'));
            $buttons[1] = new MButton('btnClose', _M('Fechar'), GUtil::getCloseAction(true), GUtil::getImageTheme('exit-16x16.png'));

            $fields[] = new MDiv(null, $buttons);
        }


        $container = new MVContainer(null, $fields, MControl::FORM_MODE_SHOW_SIDE);
        $this->injectContent($container->generate(), false, _M('Selecione os exemplares a aprovar:'));
    }

    /**
     * Aplica as opções do usuário
     *
     * @param $args
     * @return unknown_type
     */
    public function confirmAproveRequest($args)
    {
        $args = (Object) $_REQUEST;
        $check = $args->check;
        $event = $this->getEventAjax();
        $module = MIOLO::getCurrentModule();

        $busRequestChangeExemplaryComposition = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusComposition');
        $busRequestChangeExemplaryComposition = new BusinessGnuteca3BusRequestChangeExemplaryStatusComposition();

        //aprova e desaprova cada itemNumber de acordo com os dados da interface
        $this->busOperation->clean();
        $request = $this->busOperation->getRequest($args->_id, true);

        $this->busRequestChangeExemplaryStatus->setAproveJustOne($args->aproveJustOnde, $args->_id);

        if (is_array($request->requestChangeExemplaryStatusComposition))
        {
            foreach ($request->requestChangeExemplaryStatusComposition as $line => $composition)
            {
                $request->composition[$line]->confirm = false;

                if ($check[$composition->itemNumber] == DB_TRUE)
                {
                    $request->composition[$line]->confirm = true;
                    $busRequestChangeExemplaryComposition->aproveItemNumberForRequest($args->_id, $composition->itemNumber);
                }
                else
                {
                    $busRequestChangeExemplaryComposition->disapproveCompositionForItemNumber($args->_id, $composition->itemNumber);
                }
            }
        }

        $ok = $this->busOperation->aproveRequest($args->_id);

        $this->popupMessages(_M('Mensagem da operação de aprovação', $this->module));
    }

    public function reproveRequest()
    {
        $args['_id'] = MIOLO::_REQUEST('_id');
        $this->question(_M('Tem certeza que deseja desaprovar a requisição?'), 'javascript:' . GUtil::getAjax("confirmReproveRequest", $args));
    }

    public function confirmReproveRequest()
    {
        $this->busOperation->clean();
        $this->busOperation->getRequest(MIOLO::_REQUEST('_id'));
        $ok = $this->busOperation->reproveRequest(MIOLO::_REQUEST('_id'));
        $this->popupMessages(_M('Mensagem da operação de reprovação', $this->module));
    }

    public function concludeRequest()
    {
        $this->question(_M('Tem certeza que deseja concluir a requisição?'), 'javascript:' . GUtil::getAjax('confirmConcludeRequest', array('_id' => MIOLO::_REQUEST('_id'))));
    }

    public function confirmConcludeRequest()
    {
        $args = (Object) $_REQUEST;
        $event = $this->getEventAjax();
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $this->busOperation->clean();
        $this->busOperation->getRequest($args->_id);
        $ok = $this->busOperation->concludeRequest($args->_id);

        if ($ok)
        {
            $busReserve = $MIOLO->getBusiness($module, 'BusReserve');
            $busReserve = new BusinessGnuteca3BusReserve();
            $request = $this->busOperation->getRequest($args->_id, true);

            //passa pela composição procurarando por reservas.
            if (is_array($composition = $request->composition))
            {
                foreach ($composition as $line => $comp)
                {
                    if (MUtil::getBooleanValue($comp->confirm))
                    {
                        $extraData = new StdClass();
                        $extraData->itemNumber = $comp->itemNumber;
                        $reserve = $busReserve->getReservesOfExemplary($comp->itemNumber, ID_RESERVESTATUS_REQUESTED, false, 1); //1 pra pegar só a primeira
                        $reserve = $reserve[0];

                        if ($reserve)
                        {
                            $this->busOperation->addInformation('Exemplar <b>' . $comp->itemNumber . '</b> possui reserva para pessoa ' . $reserve->personId . '.', $extraData);
                        }
                    }
                }
            }
        }

        $this->popupMessages(_M('Mensagem da operação de conclusão', $this->module));
    }

    public function renewRequest()
    {
        $this->question(_M('Tem certeza que deseja renovar a requisição?'), 'javascript:' . GUtil::getAjax("confirmRenewRequest", array('_id' => MIOLO::_REQUEST('_id'))));
    }

    public function confirmRenewRequest()
    {
        $ok = $this->busOperation->renewRequest(MIOLO::_REQUEST('_id'));
        $this->popupMessages(_M('Mensagem da operação de renovação', $this->module));
    }

    public function popupMessages($message, $id)
    {
        $extraColumns['itemNumber'] = _M('Número do exemplar', $this->module);
        $fields['table'] = $this->busOperation->getMessagesTableRaw(null, null, $extraColumns);
        $fields['table'] = $fields['table']->generate();
        $this->imageClose = GUtil::getImageTheme('exit-16x16.png');
        $js = GUtil::getCloseAction() . "; dojo.byId('requestChangeExemplaryStatusIdS').value='" . MIOLO::_REQUEST('_id') . "' ; " . GUtil::getAjax('searchFunction');
        $fields['close'] = new MButton('btnClose', _M('Fechar'), $js, $this->imageClose);

        $this->injectContent($fields, false, $message);
    }

}

?>
