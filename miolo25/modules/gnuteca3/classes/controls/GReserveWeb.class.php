<?php

//controlador de eventos
$possibleEvents = array( 'gridReserve', 'selectReserveLibraryUnit', 'btn_reserveConfirm_click', 'reserveInInitialStatusConfirm' );
$event = GUtil::getAjaxFunction();

if ( in_array($event, $possibleEvents) )
{
    $reservaWeb = new GReserveWeb();
    $reservaWeb->$event((object) $_REQUEST);
}

class GReserveWeb
{

    /**
     * Monta parte referente a reserva (parte inicial)
     *
     * O Sistema de reserva web usa as seguintes funções:
     * 1. gridReserve - inicio do processo, solicitado pela grid, ou pelo botão de reserva dos detalhes, pede para selecionar a unidade caso seja preciso
     * 2. selectReserveLibraryUnit - após selecionado a unidade, pede que se seleciona os exemplares, mas somente se for necessário também
     * 3. btn_reserveConfirm_click - confirma a reserva, ou pede para o usuário confirmar, caso seja necessário
     * adiciona a reserva ao banco e informa ao usuário o resultado final, Única função que instancia a operação
     * #. reserveInInitialStatusConfirm - pode ser chamado caso existem exemplares em nível inicial (disponíveis)
     *
     * @param object $args
     */
    public function gridReserve($args)
    {
        $controlNumber = GUtil::getAjaxEventArgs();
        $MIOLO = MIOLO::getInstance();
        $module = 'gnuteca3';
        $busMaterialControl = $MIOLO->getBusiness($module, 'BusMaterialControl');
        $busLibraryUnit = $MIOLO->getBusiness($module, 'BusLibraryUnit');
        $busExemplaryControl = $MIOLO->getBusiness($module, 'BusExemplaryControl');

        $argsLogin->loginType = LOGIN_TYPE_USER_AJAX;
        $frmLogin = $MIOLO->getUI()->getForm($module, 'FrmLogin', $argsLogin);

        //TRUE se esta dentro da circulacao (MaterialMovement) e FALSE se esta no form Simple Search normal
        $isMaterialMovement = (MIOLO::_REQUEST('action') == 'main:materialMovement');

        $controlNumberFather = $busMaterialControl->getControlNumberFather($args->_id);
        $isArticle = $busMaterialControl->isColletionArticle($args->_id);

        if ( $args->reserveLibraryId && !$args->libraryUnitId )
        {
            $args->libraryUnitId = $args->reserveLibraryId;
        }

        //Se estiver reservando artigo, pegar o controlNumber do pai
        if ( $controlNumberFather && $isArticle )
        {
            $controlNumber->_id = $controlNumberFather;
        }

        if ( $isMaterialMovement )
        {
            //Reserva pelos Detalhes pega o controlNumber
            $controlNumber = $controlNumber ? $controlNumber : $args->_id;
        }
        else  //Na pesquisa normal, recebe por parâmetro o controlNumber
        {
            //Quando usuário não estiver logado, deve pegar o _id. Pois o controlnumber é um array
            if ( $controlNumber->_id )
            {
                $controlNumber = $controlNumber->_id;
            }
            else
            {
                $controlNumber = $controlNumber ? $controlNumber : $args->_id;
            }
        }

        //Solucao encontrada para resolver problema de valor que vinha incorreto (vinha o controlNumber pai) apos confirmar reserva em estado inicial
        if ( $args->_idFixed )
        {
            $controlNumber = $args->_idFixed;
        }

        $fields['controlNumber'] = new MHiddenField('controlNumber', $controlNumber);

        //Se estiver acessando pelo Circulação de material, pega o código do usuário
        if ( $isMaterialMovement )
        {
            if ( !$args->personId ) //se não tiver personId nos argumentos
            {
                $logged = $frmLogin->isAuthenticated();
                $args->personId = MIOLO::_REQUEST('uid');

                //Deve sempre pedir código do usuário, mesmo que tenha alguém logado na Minha biblioteca
                if ( !$args->personId )
                {
                    unset($logged);
                }
            }
            else //se tiver personId quer dizer que foi chamado na confirmação
            {
                $logged = true;
            }
        }
        //Se estiver acessando por outros módulos, como as Pesquisas; pega o código do usuário
        else
        {
            $args->personId = MIOLO::_REQUEST('uid');
            $logged = $frmLogin->isAuthenticated();
        }

        //por fim se não estiver logado mostra form de login
        if ( !$logged )
        {
            GForm::injectContent($frmLogin->getLoginFields(), false, _M('LOGAR', $module) . ' - ' . _M('Reserva', $module), '600px');
            return false;
        }
        else
        {
            //0.lista unidades com acesso, considera tanto usuário aluno como administrador
            $busLibraryUnit->onlyWithAccess = true;
            $librarys = $busLibraryUnit->listLibraryUnit(true); //PRECISA DE OTIMIZAÇÂO
            //0.busca exemplares filtrados por unidade
            $exemplarys = $busExemplaryControl->getExemplaryOfMaterialByLibrary($controlNumber); //PRECISA DE OTIMIZAÇÃO
            $exemplarys = FrmSimpleSearch::checkExemplarysInclude($exemplarys); //FIXME o que faz isso?? não esta documentado

            $libraryArray = null;
            $libraryArrayName = null;


            /**
             * A Lógica dessa parte funciona assim:
             *
             * 0. Monta listagem des bibliotecas e listagem de exemplares
             * 1. O sistema monta um array simples com as bibliotecas
             * 2. Passa por todos exemplares, limitando o array de bibliotecas caso alguma biblioteca não tenha exemplar
             * Isso limita caso alguma biblioteca não tenha exemplar ela é removida da relação neste momento
             * 3. Se no fim, a relação tiver somente uma biblioteca, pula uma etapa, evitando que seja necessário para o usuário selecionar uma única opção
             * 4. Por fim, tendo uma unidade selecionada, ou somente uma pessoa passa para a próxima etapa
             * 5. Verifica se a unidade selecionada esta na lista de unidades possíveis, é raro de entrar neste caso, mas pode acontecer.
             * 6. Caso não exista unidade, monta select para usuário
             * 7. Caso não existam listagem de exemplares ou de unidade é impossível reservar ( raro entrar aqui ).
             *
             */
            if ( is_array($librarys) && is_array($exemplarys) )
            {
                //1.monta array simples com relação de ids das bibliotecas
                foreach ( $librarys as $line => $info )
                {
                    $libraryArray[$info->option] = $info->option;
                    $libraryArrayName[$info->option] = $info->description;
                }

                //2.passa por cada exemplar, verificando se coincide exemplar com biblioteca, isso limita caso alguma biblioteca não tenha exemplar ela é removida neste momento
                foreach ( $exemplarys as $line => $info )
                {
                    if ( in_array($line, $libraryArray) )
                    {
                        $finalLibraryList[$line] = $libraryArrayName[$line];
                        $libraryUnitId = $line;
                    }
                }

                //3.se só tiver uma possível marca ela automaticamente
                if ( count($finalLibraryList) == 1 )
                {
                    $args->libraryUnitId = $libraryUnitId;
                }

                //4.trabalha de forma diferenciada caso exista uma unidade selecionada
                if ( $args->libraryUnitId && !ereg(',', $args->libraryUnitId) )
                {
                    //5.Verifica se esta unidade selecionada esta na relação de unidade possíveis
                    if ( array_key_exists($args->libraryUnitId, $finalLibraryList) )
                    {
                        $args->controlNumber = $controlNumber; //ajeita os argumentos para passar para o próximo passo
                        $this->selectReserveLibraryUnit($args);
                        return false;
                    }
                    else
                    {
                        //É raro entrar neste caso, mas pode acontecer
                        GForm::error(_M('Falha na operação de reserva. A Biblioteca selecionada não possui exemplares.', $module));
                        return false;
                    }
                }
                else
                {
                    if ( $finalLibraryList )
                    {
                        //6. Não exista uma única unidade , mostra select para o usuário, permitindo que ele escolha uma unidade
                        $fields[] = $personId = new MTextField('personId', $args->personId); //adiciona o campo, para manter o dados no form
                        $personId->addStyle('display', 'none'); //esconde o campo

                        $fields[] = new MDiv('', '<b>' . _M('Por favor selecione um unidade de biblioteca, ou pressione Enter para confirmar', $module) . '</b>');
                        $fields[] = $reserveLibraryId = new GSelection('reserveLibraryId', null, '', $finalLibraryList, null, null, null, TRUE);
                        $reserveLibraryId->addAttribute('onchange', 'javascript:' . GUtil::getAjax('selectReserveLibraryUnit'));
                        $reserveLibraryId->addAttribute('onPressEnter', GUtil::getAjax('selectReserveLibraryUnit'));

                        $buttons[] = GForm::getCloseButton();
                        $buttons[] = new MButton('btnSelectReserveLibraryUnit', _M('Próximo', $module), ':selectReserveLibraryUnit', GUtil::getImageTheme('next-16x16.png'));

                        $fields[] = new MDiv('containerReserve', $buttons);

                        GForm::jsSetFocus('reserveLibraryId');
                        GForm::injectContent($fields, false, _M('Reserva', $module));
                    }
                    else
                    {
                        //Aviso para quando operador ou usuário não tem permissão de acesso a determinada biblioteca. É necessário pois pode-se pesquisar em todas unidades
                        GForm::error(_M('Você não tem permissão para reservar materiais desta unidade.', $module));
                        return false;
                    }
                }
            }
            else
            {
                //7. Caso as listas não estejam ok, retornar erro
                GForm::information(_M('Sem exemplares para reservar.', $module));
                return false;
            }
        }
    }

    function getMeetReserveExemplaries($tempExemplarys)
    {
        if ( !is_array($tempExemplarys) )
        {
            return array( );
        }

        $exemplarys = array( );

        foreach ( $tempExemplarys as $line => $info )
        {
            $executeReserve = MUtil::getBooleanValue($info->exemplaryStatus->executeReserve);
            $executeReserveInInitialLevel = MUtil::getBooleanValue($info->exemplaryStatus->executeReserveInInitialLevel);
            $displayExemplary = GMaterialDetail::checkDisplayExemplary($info->exemplaryStatusId);
            $level = $info->exemplaryStatus->level;

            if ( !$displayExemplary )
            {
                continue;
            }

            // SE FOR ESTADO INICIAL NECESSITA DE DUAS PERMISSOES
            $podeReservar = ($level == 1) ? ($executeReserve && $executeReserveInInitialLevel) : $executeReserve;

            if ( !$podeReservar )
            {
                continue;
            }

            $exemplarys[] = $info;
        }

        return $exemplarys;
    }

    /**
     * Após selecionar biblioteca na reserva, monta relação de exemplares ou pula a etapa, se não for necessário
     *
     * @param unknown_type $args
     */
    public function selectReserveLibraryUnit($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $imageExit = GUtil::getImageTheme('exit-16x16.png');
        $imageReserve = GUtil::getImageTheme('reserve-16x16.png');
        $busMaterialType = $MIOLO->getBusiness($module, 'BusMaterialType');
        $busMaterialPhysicalType = $MIOLO->getBusiness($module, 'BusMaterialPhysicalType');

        //redefine a pessoa
        $fields['personId'] = new MTextField('personId', $args->personId); //adiciona o campo, para manter o dados no form
        $fields['personId']->addStyle('display', 'none'); //esconde o campo

        $MIOLO->getClass($module, 'controls/GMaterialDetail');

        // tenta pegar a unidade pela selecionada pela reserva, ou caso não existe, pega a da pesquisa mesmo, mas a da reserva tem prioridade
        // acontece que pode se definir a unidade nos dois casos
        $libraryUnitId = $args->reserveLibraryId ? $args->reserveLibraryId : $args->libraryUnitId;

        //se não tiver unidade selecionada retorna erro
        if ( !$libraryUnitId )
        {
            GForm::error(_M('Falha no processo de reserva. Não foi encontrada a unidade selecionada.', $module));
            return;
        }

        //se não tiver número de controle retornar erro , aqui derepente poderia tentar pegar o $args->_id
        if ( !$args->controlNumber )
        {
            GForm::error(_M('Impossível encontrar número de controle selecionado.', $module));
            return;
        }

        $busExemplaryControl = new BusinessGnuteca3BusExemplaryControl(); //para autocomplete no Zend
        //busca relação de exemplares, pode ser otimizado para aproveitar a relação do passo anterior
        $exemplarys = $busExemplaryControl->getExemplaryOfMaterial($args->controlNumber, $libraryUnitId, true, null, false, true);
        $exemplarys = $this->getMeetReserveExemplaries($exemplarys); //FIXME o que faz isso?? não esta documentado
        //se não tiver exemplares para o controlNumber retorna erro
        if ( !$exemplarys )
        {
            GForm::error(_M('Não é possível reservar materiais neste estado.', $module));
            return;
        }

        $filter = $this->getExemplaryGroup($exemplarys); //FIXME o que faz isso?? não esta documentado

        $temVolumeOuTomo = false;
        
        /* Contador para determinar se tem mais de um volume ou tomo porque se tiver apenas um 
         *volume ou tomo, não é necessário mostrar seleção de exemplar pra reservar.
         * verifica se precisa montar relação de item/Volume/Tomo caso tenha mais de um exemplar */
        $temVolumeOuTomoCount = 0;
        $exemplaryCount = 0; //Contador para determinar quantos exemplares existem no total
        
        if ( is_array($filter) )
        {
            foreach ( $filter as $materialTypeId => $materialPhysicalTypeId )
            {
                //Para separar tipos diferentes de materiais na reserva
                $tiposM[] = $materialTypeId;
                $tipos = array_keys($materialPhysicalTypeId);
                $tipos = array_unique($tipos);
                if ( (sizeof($tipos) > 1) || (sizeof($tiposM) > 1) )
                {
                    //Tem vários materiais. Valor repetido
                    $temMaterial = true;
                }

                foreach ( $materialPhysicalTypeId as $physicalTypeId => $materialPhysical )
                {
                    foreach ( $materialPhysical as $tomo => $volumeArray )
                    {
                        foreach ( $volumeArray as $volume => $exemplaryGroup )
                        {
                            $exemplaryData = null;
                            
                            //Para os casos onde uma obra tem exemplares com o campo Volume ou Tomo preenchido e exemplares sem estes campos
                            if ( $volume || $tomo || $temMaterial )
                            {
                                $temVolumeOuTomo = (strlen($volume) || strlen($tomo));
                                $temVolumeOuTomoCount++; //Conta todos exemplares que tenha tomo/volume/material
                            }
                            
                            //Conta todos os exemplares disponíveis.
                            $exemplaryCount++; 
                            $check = new MCheckBox('reserveFilter[]', implode(',', $exemplaryGroup->itemNumbers));
                            $table = GMaterialDetail::getExemplaryTableByExemplaryObject($exemplaryGroup->exemplaries, SIMPLE_SEARCH_RESERVE_DETAIL_FIELD_LIST);

                            $materialTypeDescription = $busMaterialType->getMaterialType($materialTypeId)->description;
                            $materialPhysicalDescription = $busMaterialPhysicalType->getMaterialPhysicalType($physicalTypeId)->description;

                            $treeData = null;
                            $treeData[0]->title = _M('Exemplares', $module);
                            $treeData[0]->content = $table;
                            $tree = new GTree('treeExemplaryList', $treeData);
                            $tree->setClosed(true); //para iniciar fechado
                            $exemplaryData[] = $tree;

                            $filterData[] = array( $check, $materialTypeDescription, $materialPhysicalDescription, $tomo, $volume, $exemplaryGroup->disponiveis, $exemplaryData );
                        }
                    }
                }
            }

            $fields['doFilter'] = new MTextField('doFilter', 1);
            $fields['doFilter']->addStyle('display', 'none');
        }

        /* SE: 
        * - Não precisar de relação de tipo de material/volume/tomo 
        * - Tiver algum material em estado inicial
        * - Tiver um único exemplar com /tipo/volume/tomo/ definido.
        * - ENTÃO : Passa direto para a finalização da reserva. */
        if ( (!$temVolumeOuTomo && !$temMaterial) || $args->initialStatusConfirmed || ($temVolumeOuTomoCount <= 1 && $exemplaryCount <= $temVolumeOuTomoCount) )
        {
            $this->btn_reserveConfirm_click($args);
        }
        // caso tenha relação, monte tabela, para usuário selecionar os exemplares que quer
        else
        {
            $fields[] = new MDiv('requestError', ' ');

            if ( $filter )
            {
                $fields[] = $table = new MDiv('reserveExemplarList', new MTableRaw('Por favor selecione os exemplares da reserva:', $filterData, array( _M('Ação', $module), 'Tipo do material', 'Tipo físico do material', 'Tomo', 'Volume', 'Disponíveis', _M('Exemplares', $module) ), 'filters'));
                $table->addStyle('width', '100%');
            }

            $fields[] = $reserveLibraryId = new MTextField('reserveLibraryId', $libraryUnitId);
            $reserveLibraryId->addStyle('display', 'none');
            $fields[] = $controlNumberField = new MTextField('controlNumber', $args->controlNumber);
            $controlNumberField->addStyle('display', 'none');

            $buttons[] = new MButton('btn_reserveConfirm', _M('Reserva', $module), ':btn_reserveConfirm_click', $imageReserve);
            $buttons[] = new MButton('btnClose', _M('Fechar', $module), 'javascript:gnuteca.closeAction();', $imageExit);
            $fields[] = new MDiv('', $buttons);

            $container = new GContainer('containerReserve', $fields);

            GForm::injectContent($container->generate(), false, _M('Reserva', $module));
        }
    }

    function getExemplaryGroup($exemplarys)
    {
        if ( !is_array($exemplarys) )
        {
            return array( );
        }

        $filter = null;

        foreach ( $exemplarys as $line => $info )
        {
            $filter[$info->materialTypeId][$info->materialPhysicalTypeId][$info->tomo][$info->volume]->exemplaries[] = $info; //subistitui
            $filter[$info->materialTypeId][$info->materialPhysicalTypeId][$info->tomo][$info->volume]->itemNumbers[] = $info->itemNumber;

            if ( $info->exemplaryStatusId == DEFAULT_EXEMPLARY_STATUS_DISPONIVEL )
            {
                $filter[$info->materialTypeId][$info->materialPhysicalTypeId][$info->tomo][$info->volume]->disponiveis++;
            }
            else if ( !$filter[$info->materialTypeId][$info->materialPhysicalTypeId][$info->tomo][$info->volume]->disponiveis )
            {
                $filter[$info->materialTypeId][$info->materialPhysicalTypeId][$info->tomo][$info->volume]->disponiveis = 0;
            }
        }

        return $filter;
    }
    /*
     * Faz a reserva no banco. ( Única função que instancia a operação );
     *
     * Este processo funciona da seguinte forma:
     * 1. Define unidade e tipo de reserva
     * 2. Define pessoa conforme a situação
     * 3. Faz a reserva pelo controlNumber ou pela lista de itemNumber, conforme a situação
     * 4. Finaliza Operação
     * 5. Mostra resultado da operação para o usuário
     *
     */

    public function getBusOperationReserve($libraryUnitId, $reserveType, $personId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = 'gnuteca3';
        $busOperationReserve = $MIOLO->getBusiness($module, 'BusOperationReserve');
        $busOperationReserve->clear();
        $busOperationReserve->setLibraryUnit($libraryUnitId); //define unidade
        $busOperationReserve->setReserveType($reserveType);
        $busOperationReserve->setPerson($personId);

        return $busOperationReserve;
    }

    public function btn_reserveConfirm_click($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = 'gnuteca3';

        $busAuthenticate = $MIOLO->getBusiness($module, 'BusAuthenticate');

        //Verifica se esta funcao ja foi chamada no mesmo processo (por algum motivo estava sendo chamada 2 vezes)
        if ( $_REQUEST['calledFunctionReserveConfirm'] )
        {
            return;
        }

        $_REQUEST['calledFunctionReserveConfirm'] = TRUE;
        $libraryUnitId = $args->reserveLibraryId ? $args->reserveLibraryId : $args->libraryUnitId; //pega a unidade da biblioteca

        // Se estiver acessando do módulo Circulação de material
        if ( MIOLO::_REQUEST('action') == 'main:materialMovement' )
        {
            $personId = $args->personId;
        }
        else
        {
            $personId = $busAuthenticate->getUserCode();
        }

        if ( $args->reserveFilter && !is_array($args->reserveFilter) )
        {
            $args->reserveFilter = unserialize(urldecode($args->reserveFilter));
        }

        // aqui entra quando tiver vários volumes/tomos
        if ( $args->doFilter || is_array($args->reserveFilter) )
        {
            if ( is_array($args->reserveFilter) )
            {
                //Identifica se há alguma reserva inicial junto com reservas em transição. Este é um caso onde dava problema de 
                //mensagem final quando um matérial estava dispónivel e outro emprestado
                if ( $args->initialStatusConfirmedexemplaryStatusId != 1 )
                {
                    if ( MIOLO::_REQUEST('action') == 'main:materialMovement' )
                    {
                        $reserveType = ID_RESERVETYPE_LOCAL;
                    }
                    else
                    {
                        $reserveType = ID_RESERVETYPE_WEB;
                    }
                    
                    $reserveInInitialLevel = array();
                    
                    foreach ( $args->reserveFilter as $lineR => $info )
                    {
                        
                        $filter = explode(',', $args->reserveFilter[$lineR]);
                        $busOperationReserveTest = $this->getBusOperationReserve($libraryUnitId, $reserveType, $personId);    
                        
                        foreach ( $filter as $line => $info )
                        {
                            $ok = $busOperationReserveTest->addItemNumber($info, $args->initialStatusConfirmed);

                            if ( $ok === 'initial_confirm' )
                            {
                                $reserveInInitialLevel[$lineR] = TRUE;
                                break;
                            }
                            else
                            {
                                $reserveInInitialLevel[$lineR] = FALSE;
                            }
                        }
                    }
                    
                    if ( in_array(TRUE, $reserveInInitialLevel) )
                    {
                        $args->initialStatusConfirmedexemplaryStatusId = 1;
                        $args->reserveInInitialLevel = $reserveInInitialLevel;
                        
                        $this->reserveInInitialStatusConfirm($args);
                        return false;
                    }
                }
                
                $reserveInInitialLevel = unserialize(urldecode($args->reserveInInitialLevel));
                
                $i = 1;
                foreach ( $args->reserveFilter as $lineR => $info )
                {
                    if ( MIOLO::_REQUEST('action') == 'main:materialMovement' )
                    {
                        //Reserva em estado inicial
                        if ( $reserveInInitialLevel[$lineR] )
                        {
                            $reserveType = ID_RESERVETYPE_LOCAL_INITIAL_STATUS;
                        }
                        else
                        {
                            $reserveType = ID_RESERVETYPE_LOCAL;
                        }
                    }
                    else
                    {
                        //Reserva em estado inicial
                        if ( $reserveInInitialLevel[$lineR] )
                        {
                            $reserveType = ID_RESERVETYPE_WEB_ANSWERED;
                        }
                        else
                        {
                            $reserveType = ID_RESERVETYPE_WEB;
                        }
                    }
                    
                    
                    $filter = explode(',', $args->reserveFilter[$lineR]);

                    $busOperationReserve = $this->getBusOperationReserve($libraryUnitId, $reserveType, $personId);

                    // faz a reserva se utilizando do filtro (quer dizer que selecionou exemplares durante o processo
                    foreach ( $filter as $line => $info )
                    {
                        $ok = $busOperationReserve->addItemNumber($info, $args->initialStatusConfirmed);
                    }

                    //Se não estiver bloqueado o processo de reserva, finaliza-a
                    $busOperationReserve->finalize();

                    // se não estiver logado como operador filtra as mensagens, mostrando somente as com código maior que 100
                    $busOperationReserve = $this->filterMessages($busOperationReserve);

                    //pega mensagens da operação e guarda em um array de mensagens concatenando o número da mensagem na frente
                    $messages = $busOperationReserve->getMessages();

                    if ( is_array($messages) )
                    {
                        foreach ( $messages as $line => $info )
                        {
                            $info->setMessage('<b>' . _M('Reserva', $module) . ' ' . $i . ' : ' . '</b>' . $info->getMessage());
                            $msgs[] = $info;
                        }
                    }

                    $i++;
                }

                //cria um bus falso só com as mensagens das operações anteriores
                if ( is_array($msgs) )
                {
                    $busOperationReserve = new GMessages();

                    foreach ( $msgs as $line => $msg )
                    {
                        $busOperationReserve->addMessage(null, $msg);
                    }
                }
            }
            else
            {
                //requestError div aguardando respostas de erro
                $MIOLO->ajax->setResponse(_M('Você precisa selecionar algum exemplar.', $module), 'requestError');
                return false;
            }
        }
        else
        {
            
            if ( MIOLO::_REQUEST('action') == 'main:materialMovement' )
            {
                //Reserva em estado inicial
                if ( $args->initialStatusConfirmedexemplaryStatusId == 1 )
                {
                    $reserveType = ID_RESERVETYPE_LOCAL_INITIAL_STATUS;
                }
                else
                {
                    $reserveType = ID_RESERVETYPE_LOCAL;
                }
            }
            else
            {
                //Reserva em estado inicial
                if ( $args->initialStatusConfirmedexemplaryStatusId == 1 )
                {
                    $reserveType = ID_RESERVETYPE_WEB_ANSWERED;
                }
                else
                {
                    $reserveType = ID_RESERVETYPE_WEB;
                }
            }
                    
            // isso quer dizer que não foi necessário selecionar exemplares então a reserva é feita direto pelo número de control
            $busOperationReserve = $this->getBusOperationReserve($libraryUnitId, $reserveType, $personId);
            $ok = $busOperationReserve->addMaterial($args->controlNumber, null, $args->initialStatusConfirmed);

            //pede confirmação do usuário caso existem exemplares disponiveis
            if ( $ok === 'available_confirm' )
            {
                $args->initialStatusConfirmedexemplaryStatusId = 1;
                $this->reserveInInitialStatusConfirm($args);
                return false;
            }

            //Se não estiver bloqueado o processo de reserva, finaliza-a
            $busOperationReserve->finalize();
            // se não estiver logado como operador filtra as mensagens, mostrando somente as com código maior que 100
            $busOperationReserve = $this->filterMessages($busOperationReserve);
        }

        $table = $busOperationReserve->getMessagesTableRaw();

        //retorna as mensagens da operação para o usuário
        GForm::injectContent($table->generate(), true, _M('Reserva', $module));
        return true;
    }

    /**
     * Filtra as mensagens da reserva, evitando repetição e códigos não necessários para o usuário
     *
     * @param $bus
     * @return unknown_type
     */
    public function filterMessages($bus)
    {
        //caso for operador mostra tudo
        if ( GOperator::isLogged() )
        {
            return $bus;
        }

        $messages = $bus->getMessages();
        $bus->clearMessages();

        //bota todas mensagens iguais em um único indice, reduzindo para uma única
        if ( is_array($messages) )
        {
            foreach ( $messages as $line => $message )
            {
                $tmpMessages[$message->message] = $message;
            }
        }

        $messages = null; //limpa mensagens
        //filtra somente mensagens que tiverem código menor que 100 ou não tiverem código, demais mensagens são excluídas
        if ( is_array($tmpMessages) )
        {
            foreach ( $tmpMessages as $line => $message )
            {
                if ( $message->msgCode < 100 || !$message->msgCode )
                {
                    $bus->addMessage(null, $message);
                }
            }
        }

        return $bus;
    }

    /**
     * Dialogo para confirmar se usuario realmente deseja reservar exemplares no estado inicial
     *
     * @param unknown_type $args
     */
    public function reserveInInitialStatusConfirm($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = 'gnuteca3';
        $busAuthenticate = $MIOLO->getBusiness($module, 'BusAuthenticate');
        $busExemplaryControl = $MIOLO->getBusiness($module, 'BusExemplaryControl');
        $busBond = $MIOLO->getBusiness($module, 'BusBond');
        $busRight = $MIOLO->getBusiness($module, 'BusRight');

        $isMaterialMovement = (MIOLO::_REQUEST('action') == 'main:materialMovement');
        $personId = $args->personId ? $args->personId : $busAuthenticate->getUserCode();
        $libraryUnitId = $args->reserveLibraryId ? $args->reserveLibraryId : $args->libraryUnitIdS;
        $controlNumber = $args->_id ? $args->_id : $args->controlNumber;

        if ( $personId && $libraryUnitId && $controlNumber )
        {
            $exemplares = $busExemplaryControl->getExemplaryOfMaterial($controlNumber, $libraryUnitId);

            if ( $exemplares[0] )
            {
                $materialGenderId = $exemplares[0]->materialGenderId;
                $link = $busBond->getPersonLink($personId);
                $linkId = $link->linkId;

                if ( $isMaterialMovement )
                {
                    $operation = ID_OPERATION_LOCAL_RESERVE_IN_INITIAL_STATUS;
                }
                else
                {
                    $operation = ID_OPERATION_WEB_RESERVE_IN_INITIAL_STATUS;
                }

                $right = $busRight->hasRight($libraryUnitId, $linkId, $materialGenderId, $operation);

                if ( !$right )
                {
                    GForm::error(_M('Este material possui exemplares disponíveis e você não tem permissão para reservá-los.', $module));
                    return false;
                }
            }
        }

        //Observação: não avaliei exatamente o que era necessário,
        //mas estava passando todo o args pela url e isso gerava um problema, por isso passei somente os dados abaixo
        //FIXME: em um segundo momento é possível avaliar melhor o que realmente é necessário
        $argsLink->initialStatusConfirmed = 1;
        $argsLink->initialStatusConfirmedexemplaryStatusId = $args->initialStatusConfirmedexemplaryStatusId;
        //$argsLink->reserveFilter = $args->reserveFilter;
        $argsLink->libraryUnitId = $args->libraryUnitId;
        $argsLink->reserveLibraryId = $args->reserveLibraryId;//unidade seleciona em um segundo momento
        $argsLink->reserveFilter = urlencode(serialize($args->reserveFilter));
        $argsLink->reserveInInitialLevel = urlencode(serialize($args->reserveInInitialLevel));
        $argsLink->_idFixed = $args->controlNumber;
        $argsLink->controlNumber = $args->controlNumber;
        $argsLink->personId = $args->personId;

        GForm::question(MSG_INITIAL_STATUS, GUtil::getAjax('gridReserve', $argsLink));
    }
}
?>