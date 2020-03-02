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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandro@solis.coop.br]
 * Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 12/01/2009
 *
 * */
class FrmMaterialCirculationReserve extends FrmMaterialCirculationLoan
{

    /**
     * Mount the Reserve form, called when user press F8 function
     *
     * @param object $args the the default miolo ajax stdclass object
     *
     */
    public function onkeydown119($args = NULL)
    {
      
        $this->busOperationReserve->unsetBlockReserveProcess();

        $this->setMMType('119');
        $this->changeTab('btnAction119');

        //Sao tem acesso se tiver permissao gtcMaterialMovementRequestReserve
        if ( GPerms::checkAccess('gtcMaterialMovementRequestReserve', NULL, false) || GPerms::checkAccess('gtcMaterialMovementAnswerReserve', NULL, false) )
        {
            $module = MIOLO::getCurrentModule();

            if ( GPerms::checkAccess('gtcMaterialMovementRequestReserve', NULL, false) )
            {
                $functionButtons[] = new MButton('btnRequest', _M('[F2] Solicitar', module), ':onkeydown113');
            }
            if ( GPerms::checkAccess('gtcMaterialMovementAnswerReserve', NULL, false) )
            {
                $functionButtons[] = new MButton('btnAnswer', _M('[F3] Atender', $module), ':onkeydown114');
            }

            $functionButtons[] = new MButton('btnFinalize', _M('[F4] Finalizar', $module), ':onkeydown115');
            $functionButtons[] = new MButton('btnCleanData', _M('[ESC] Limpar', $module), ':onkeydown27');

            $fields[] = $this->getButtonContainer($functionButtons);
            $fields[] = $this->getLibrarySelection();

            //adiciona select de bases Ldap
            $bases = BusinessGnuteca3BusAuthenticate::listMultipleLdap();
            if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) && (strlen(implode('', $bases)) > 0) )
            {
                $fields[] = new GContainer('contBaseLdap', array( new MLabel(_M('Base', $this->module)), new GSelection('baseLdap', '', null, $bases, false, '', '', true) ));
                $related = 'personId,personName,baseLdap';
                $filter = array( 'personId', 'baseLdap' );
            }
            else
            {
                $related = 'personId,personName';
                $filter = array( 'personId' );
            }

            $lookupF[] = new MTextField('personName', null, null, 33, null, null, true);
            $fields[] = $personId = new GLookupField('personId', null, _M('Pessoa', $module), 'PersonMaterialCirculation', $lookupF);
            $personId->lookupTextField->related = $related; //seta o related no lookup de pessoa
            $personId->lookupTextField->filter = $filter; //seta o filter no lookup de pessoa
            $personId->lookupTextField->addAttribute('onPressEnter', GUtil::getAjax('getPersonSimple'));

            $reserveOptions[] = _M('[F5] Obra', $module);
            $reserveOptions[] = _M('[F6] Exemplar', $module);

            if ( MUtil::getBooleanValue(SHOW_RESERVE_BY_ITEMNUMBER) == true )
            {
                $reserveOptions[] = _M('Obra por exemplar', $module);
            }

            $optionCont[0] = new GRadioButtonGroup('option', _M('Tipo', $module), $reserveOptions, '[F6] Exemplary', null, 'horizontal');
            $optionCont[0]->addAttribute('onPressEnter', GUtil::getAjax('selectOption'));
            $optionCont[0]->addAttribute('onclick', "checkBoxOnClick();");
            $fields[] = new GContainer('', $optionCont);

            $fields[] = $numberLabel = new MLabel(_M('Número de controle', $module) . ':');
            $numberLabel->setId('numberLabel');
            
            //Verifica se está utilizando smartReader
            if(GSipCirculation::usingSmartReader())
            {
                $fields[] = $itemNumber = new MTextField('itemNumber', null, null, 10, null, null, true);
                $itemNumber->addAttribute('onPressEnter', 'verificaItemNumberReserva();');
                
                $fields[] = $codItens =  new MTextField('codItens', '', '', 100, null, null, TRUE);
                $fields[] = $codItensFinalize =  new MTextField('codItensFinalize', '', '', 100, null, null, TRUE);
                $codItens->addStyle('display', 'none');
                $codItensFinalize->addStyle('display', 'none');
            }
            else
            {
                $fields[] = $itemNumber = new MTextField('itemNumber', null, null, 10, null, null, true);
                $itemNumber->addAttribute('onPressEnter', GUtil::getAjax('addReserve'));
            }

            if ( GSipCirculation::usingSmartReader() )
            {
                $legend = '<div id="contador">Itens [0]</div>';
            }
            else
            {
                $legend = 'Itens';
            }
            
            $tableItemsReserve = new GRepetitiveField('tableItemsReserve', _M($legend, $module), null, null, array( 'noButtons' ));
            $tableItemsReserve->addAction('removeReserve', 'table-delete.png', 'gnuteca3');
            $tableItemsReserve->setShowButtons(false);

            $tableItemsColumns = array
                (
                new MGridColumn(_M('Número do exemplar', $module), 'left', true, "20%", true, 'itemNumber'),
                new MGridColumn(_M('Dados do exemplar', $module), 'left', true, "64%", true, 'searchData'),
                new MGridColumn(_M('Estado', $module), 'left', true, "64%", true, 'exemplaryStatusDescription'),
            );

            $tableItemsReserve->setColumns($tableItemsColumns);
            $this->tables['tableItemsReserve'] = $tableItemsReserve;

            $fields[] = $tableItemsReserve;

            $tablePolicyReserve = new GRepetitiveField('tablePolicyReserve', _M('Política', $module), null, null, array( 'noButtons' ));
            $tablePolicyReserve->setShowButtons(false);

            $tablePolicyColumns = array
                (
                new MGridColumn(_M('Tipo de material', $module), 'left', true, "20%", true, 'materialGenderDescription'),
                new MGridColumn(_M('Limite de reserva', $module), 'left', true, "20%", true, 'reserveLimit'),
                new MGridColumn(_M('Limite de reserva de nível inicial', $module), 'left', true, "20%", true, 'reserveLimitInInitialLevel'),
                new MGridColumn(_M('Dias de reserva', $module), 'left', true, "20%", true, 'daysOfWaitForReserve'),
                new MGridColumn(_M('Dias de reserva de nível inicial', $module), 'left', true, "20%", true, 'daysOfWaitForReserveInInitialLevel'),
                new MGridColumn(_M('Reservas', $module), 'left', true, "20%", true, 'reserves'),
                new MGridColumn(_M('Reservas comunicadas', $module), 'left', true, "20%", true, 'answeredReserves')
            );

            $tablePolicyReserve->setColumns($tablePolicyColumns);
            $this->tables['tablePolicyReserve'] = $tablePolicyReserve;

            $fields[] = $tablePolicyReserve;

            if ( GPerms::checkAccess('gtcMaterialMovementVerifyUser', NULL, false) )
            {
                $buttons[] = new MButton('btnViewReserve', _M('Reservas', $this->module), ':displayReserve', GUtil::getImageTheme('reserve-16x16.png'));
            }

            $buttons[] = new MHiddenField('initialStatusConfirmed');
            $fields[] = new Mdiv('', $buttons);
            $fields[] = new MDiv('DIVE');
            $divReserve = new MDiv('divReserve', $fields);

            //Se operador não tiver acesso ao Requisitar reserva. Deve bloquear a opção Obra e setar código da pessoa.
            if ( (!GPerms::checkAccess('gtcMaterialMovementRequestReserve', NULL, false)) && (GPerms::checkAccess('gtcMaterialMovementAnswerReserve', NULL, false)) )
            {
                $this->jsSetChecked('option_1');
                $this->jsSetDisable('option_0', true);
                $this->jsSetFocus('personId');
            }
            else
            {
                $this->jsSetChecked('option_0');
            }

            $label0 = _M('Número de controle', $module);
            $label1 = _M('Número do exemplar', $module);

            $this->page->addJsCode("
            function checkBoxOnClick()
            {
	            if ( element = document.getElementById('option_0').checked )
	            {
                    dojo.byId('numberLabel').innerHTML = '$label0:';
	            }
	            else
	            {
                    dojo.byId('numberLabel').innerHTML = '$label1:';
	            }
	        }");

            return $this->addResponse($divReserve, $args);
        }
        else
        {
            $this->setResponse('', 'limbo');
        }
    }

    /**
     * Remove um item da tabela de pesquisa
     *
     * @param stdclass $args
     */
    public function removeReserve($args)
    {
        $itemData = GRepetitiveField::getDataItem($args->arrayItemTemp, $args->GRepetitiveField);
        $this->busOperationReserve->deleteItemNumber($itemData->itemNumber);
        
        if( GSipCirculation::usingSmartReader() )
        {
            $this->page->onload("removeItemFromSmartReaderCache('{$itemData->itemNumber}');");
        }
        
        $items = $this->busOperationReserve->getExemplarys();
        GRepetitiveField::setData($items, 'tableItemsReserve');
        $this->setResponse(GRepetitiveField::generate(false, 'tableItemsReserve'), 'divtableItemsReserve');
        
        if ( GSipCirculation::usingSmartReader() )
        {
            $totalItens = count($items);
            
            $this->page->onload("
                contadorDeItens = {$totalItens};
                dojo.byId('contador').innerHTML = 'Itens [' + contadorDeItens + ']';
            ");
        }
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $args
     */
    public function selectOption($args)
    {
        $this->jsSetReadOnly('itemNumber', false);
        $this->jsSetFocus('itemNumber');
        $this->jsSetValue('itemNumber', '');
        $this->setResponse('', 'limbo');
    }

    /**
     * Esta função adiciona uma reserva
     *
     * Ela adiciona tanto por itemNumber como controlNumber
     *
     * Se existirem exemplares disponíveis ela pergunta e se chama novamente passando yes como parametro para ela mesmo
     *
     */
    public function addReserve($args)
    {
        $module = MIOLO::getCurrentModule();

        if ( !is_object($args) )
        {
            $itemNumber = $args;
            $args = (object) $_REQUEST;
            $args->itemNumber = $itemNumber;
        }
        
        if ( !$args->itemNumber )
        {
            $this->error(_M('Por favor informe um número.', $this->module));
            return false;
        }
         
        if(GSipCirculation::usingSmartReader())
        {
            $itemNumberR = '-' . $args->itemNumber;
              
            $js = "
            while( mutexTrava == 't');
             mutexTrava = 't';
            codItensFinalize = dojo.byId('codItensFinalize').value;
            codItensFinalize = codItensFinalize.replace('{$itemNumberR}', '');
            dojo.byId('codItensFinalize').value = codItensFinalize;
            mutexTrava = 'f';";

            $this->page->onload($js);
        }

        //Verifica se foi clicado no sim de exemplar em nível inicial no F8 da Circulação de material
        if ( $args->initialStatusConfirmed == 'yes' )
        {
            $this->busOperationReserve->setReserveType(ID_RESERVETYPE_LOCAL_INITIAL_STATUS);
            $this->jsSetValue('initialStatusConfirmed', '');
        }
        else
        {
            //remove itemNumber do cache do smartReader
            if ( GSipCirculation::usingSmartReader() )
            {
                $this->page->onload("removeItemFromSmartReaderCache('{$args->itemNumber}');");
            }
        }

        //FIXME encontra uma solução melhor para essa verificação, pode ter problemas com tradução
        if ( $args->option == _M('[F5] Obra', $module) )
        {
            $ok = $this->busOperationReserve->addMaterial($args->itemNumber, null, $args->initialStatusConfirmed);
        }
        else if ( $args->option == _M('Obra por exemplar', $module) )
        {
            $ok = $this->busOperationReserve->addMaterialByExemplar($args->itemNumber, null, $args->initialStatusConfirmed);
        }
        else
        {
            $ok = $this->busOperationReserve->addItemNumber($args->itemNumber, $args->initialStatusConfirmed);
        }
        
        //se aconteceram erros mostra mensagem
        if ( $this->busOperationReserve->getErrors() || is_array($_SESSION['solicitarReserva']))
        {
            if ( GSipCirculation::usingSmartReader() )
            {
                $erros = $this->busOperationReserve->getErrors();

                if ( is_array($erros) )
                {
                    $_SESSION['solicitarReserva'][$args->itemNumber]  = serialize($erros[0]);
                }

                $this->busOperationReserve->clearMessages();

                $erros = array();
                foreach ( $_SESSION['solicitarReserva'] as $error )
                {
                    $erros[] = unserialize($error);
                }

                $this->busOperationReserve->setMessages($erros);
                $this->page->onload("removeItemFromSmartReaderCache('{$args->itemNumber}');");
            }
            
            $this->busOperationReserve->unsetBlockReserveProcess();
            $extraColumns['itemNumber'] = _M('Número do exemplar', $this->module);
            $table = $this->busOperationReserve->getMessagesTableRaw(null, false, $extraColumns);
            
            $this->injectContent($table, "dojo.byId('itemNumber').value = ''; dojo.byId('itemNumber').focus(); return false;", _M('Problemas ao inserir um reserva', $this->module));
        }

        $goto = "miolo.getElementById('itemNumber').value = '{$args->itemNumber}'; miolo.getElementById('option').value = '{$args->option}'; " . GUtil::getAjax('addReserve');
        $gotoYes = "javascript:miolo.getElementById('initialStatusConfirmed').value = 'yes';" . $goto . 'gnuteca.closeAction();';
        $gotoNo = "javascript:dojo.byId('initialStatusConfirmed').value = null; gnuteca.closeAction();";
        
        //pergunta se é para adicionar o exemplar mesmo que esteja disponível
        if ( $ok === 'initial_confirm' )
        {
            $goto = $this->MIOLO->getActionURL(MIOLO::getCurrentModule(), $this->_action, NULL, $args);
            $this->question(_M('O(s) exemplar(es) estão em estado inicial. Adicionar o(s) número(s) de tombo(s)?', $this->module), $gotoYes, $gotoNo);
            $this->jsSetFocus('btnYes', true); //resolve o problema do enter ao adicionar
        }
        //pergunta se é para adicionar vários exemplares (controlNumber) disponíveis
        else if ( $ok === 'available_confirm' )
        {
            $this->question(MSG_INITIAL_STATUS, $gotoYes, $gotoNo);
            $this->jsSetFocus('btnYes', true); //resolve o problema do enter ao adicionar
        }
        else if ( $ok )
        {
            $items = $this->busOperationReserve->getExemplarys();
            $title = $items[count($items) - 1]->title;
            $this->updateTableItemReserve($args);
            
            if ( !GSipCirculation::usingSmartReader() )
            {
                $this->page->onLoad($gotoNo);
            }
            
        }
        
        if ( !$ok )
        {
            //remove itemNumber do cache do smartReader
            if ( GSipCirculation::usingSmartReader() )
            {
                $this->page->onload("removeItemFromSmartReaderCache('{$args->itemNumber}');");
            }
        }
        
        $this->jsSetValue('itemNumber', '');
        $this->jsSetFocus('itemNumber');
    }
    
    /**
     * Enter description here...
     *
     */
    public function cleanData119($args)
    {
        $this->jsDisabled('libraryUnitId_', false);
        $this->jsDisabled('personId', false);
        $this->jsSetValue('personId', '');
        $this->jsSetValue('initialStatusConfirmed', '');
        $this->jsSetReadOnly('personId', false);
        
        if(GSipCirculation::usingSmartReader())
        {
            $this->jsSetFocus('popupTitle');
        }
        else
        {
            $this->jsSetFocus('personId');
        }
        
        $this->jsSetValue('personName', '');
        $this->jsSetReadOnly('personName', true);
        $this->jsSetValue('itemNumber', '');
        $this->jsSetReadOnly('itemNumber', true);
        $this->busOperationReserve->clearItemsReserve();
        $this->updateTablePolicyReserve($args);
        GRepetitiveField::clearData('tablePolicyReserve');
        
        $_SESSION['solicitarReserva'] = NULL;
        
        $this->updateTableItemReserve($args);
        if ( !$args->cleanData )
        {
            $args->cleanData = true;
            $op = $this->getMMOperatioN();
            if ( $op == 'REQUEST' )
            {
                $this->onkeydown113_119($args);
            }
            else if ( $op == 'ANSWER' )
            {
                $this->onkeydown114_119($args);
            }
        }
    }

    /**
     *
     */
    public function updateTableItemReserve($args)
    {
        $items = $this->busOperationReserve->getExemplarys();
        GRepetitiveField::setData($items, 'tableItemsReserve');

        if ( !$args->return )
        {
            $this->setResponse(GRepetitiveField::generate(false, 'tableItemsReserve'), 'divtableItemsReserve');
        }
        
        if ( GSipCirculation::usingSmartReader() )
        {
            $totalItens = count($items);
            
            $this->page->onload("
                contadorDeItens = {$totalItens};
                dojo.byId('contador').innerHTML = 'Itens [' + contadorDeItens + ']';
            ");
        }
    }

    /**
     *
     */
    public function updateTablePolicyReserve($items)
    {
        GRepetitiveField::setData(NULL, 'tablePolicyReserve');

        if ( strlen(GForm::getEvent()) )
        {
            $this->setResponse(GRepetitiveField::generate(false, 'tablePolicyReserve'), 'divtablePolicyReserve');
        }
    }

    /**
     * Requisição de reserva
     *
     * @param $args
     * @return unknown_type
     */
    public function onkeydown113_119($args)
    {
        if ( $this->checkAcces('gtcMaterialMovementRequestReserve') )
        {
            //resolve problemas de abertura ao "reabrir" o form
            if ( !$args->return )
            {
                $this->cleanData($args);
            }

            $this->setMMOperation('REQUEST');
            $this->jsChangeButtonColor('btnRequest');
            $this->busOperationReserve->setReserveType(ID_RESERVETYPE_LOCAL);

            //ativa novamente o campo base
            if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) || (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN) )
            {
                $this->jsEnabled('baseLdap');
                $this->jsSetReadOnly('baseLdap', false);
            }

            $this->jsSetReadOnly('personId', false);
            $this->jsSetDisable('option_0', false);
            $this->jsSetDisable('option_2', false);
            $this->jsSetValue('personId', '');
            
            //Verifica se está utilizando smartStation
            if(GSipCirculation::usingSmartReader())
            {
                $this->page->onload("var btFinalize = document.getElementById('btnClose');
                    
                                     if(btFinalize != null)
                                     {
                                        document.getElementById('btnClose').focus();
                                     }
                                     else
                                     {
                                        document.getElementById('personId').focus();
                                     }");
            }
            
            $this->jsSetFocus('personId');
        }
        else
        {
            $this->jsChangeButtonColor('btnAnswer');
            $this->setResponse('', 'limbo');
            $this->onkeydown114_119();
        }
    }

    /**
     * Atender reserva
     *
     * @param object $args
     */
    public function onkeydown114_119($args)
    {
        if ( GPerms::checkAccess('gtcMaterialMovementAnswerReserve', NULL, false) )
        {
            $this->cleanData($args);
            $this->setMMOperation('ANSWER');
            $this->jsChangeButtonColor('btnAnswer');
            $this->busOperationReserve->setReserveType(ID_RESERVETYPE_LOCAL_ANSWERED);
            $this->jsSetDisable('option_0', true);
            $this->jsSetDisable('option_2', true);
            $this->jsSetChecked('option_1');

            //desativa campo baseLdap
            if ( (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE) || (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN) )
            {
                $this->jsSetReadOnly('baseLdap', false);
                $this->jsDisabled('baseLdap', false);
            }

            $this->jsSetReadOnly('personId', false);
            $this->jsSetValue('personId', '');
            
            //Verifica se está utilizando smartStation
            if(GSipCirculation::usingSmartReader())
            {
                $this->page->onload("var btFinalize = document.getElementById('btnClose');
                    
                                     if(btFinalize != null)
                                     {
                                        document.getElementById('btnClose').focus();
                                     }
                                     else
                                     {
                                        document.getElementById('personId').focus();
                                     }");
            }
            
            $this->jsSetFocus('personId');
        }
        else
        {
            $this->setResponse('', 'limbo');
        }
    }

    /**
     * Finalize reserve press F4
     *
     * @param object $args
     */
    public function onkeydown115_119($args)
    {
        $ok = $this->busOperationReserve->finalize();
        
        if ( GSipCirculation::usingSmartReader() )
        {
            if ( $ok )
            {
                $table = $this->busOperationReserve->getMessagesTableRaw(null);
                $this->injectContent($table, "miolo.doAjax('onkeydown27','','__mainForm');", true);
            }
            else
            {
                $this->setResponse('', 'limbo');
            }
        }
        else
        {
            $table = $this->busOperationReserve->getMessagesTableRaw(null);
            $this->injectContent($table, "miolo.doAjax('onkeydown27','','__mainForm');", true);
        }
    }

    /**
     * Finalize reserve press F5
     *
     * @param object $args
     */
    public function onkeydown116_119($args)
    {
        if ( GPerms::checkAccess('gtcMaterialMovementRequestReserve', NULL, false) )
        {
            $operation = $this->getMMOperation();
            $module = MIOLO::getCurrentModule();
            $label = _M('Número de controle', $module) . ':';

            if ( $operation == 'REQUEST' )
            {
                $this->jsSetInner('numberLabel', $label);
                $this->jsSetChecked('option_0', true);
            }
        }

        $this->setResponse('', 'limbo');
    }

    /**
     * Finalize reserve press F6
     *
     * @param object $args
     */
    public function onkeydown117_119($args)
    {
        if ( GPerms::checkAccess('gtcMaterialMovementAnswerReserve', NULL, false) || GPerms::checkAccess('gtcMaterialMovementRequestReserve', NULL, false) )
        {
            $module = MIOLO::getCurrentModule();
            $label = _M('Número do exemplar', $module) . ':';
            $this->jsSetInner('numberLabel', $label);
            $this->jsSetChecked('option_1', true);
        }

        $this->setResponse('', 'limbo');
    }

    /**
     * Display reserves
     *
     * @param unknown_type $args
     */
    public function displayReserve($args)
    {
        $args->event = 'onkeydown114'; //key event to show reserves
        $this->openVerifyUserWindow($args);
    }
}

?>
