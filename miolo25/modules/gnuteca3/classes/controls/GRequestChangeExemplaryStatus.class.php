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
 * Popup de congelamento utilizado na pesquisa simples.
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
 * Class created on 06/08/2008
 *
 **/
class GRequestChangeExemplaryStatus
{
    public function mainFields()
    {
        $args = (object) $_REQUEST;
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $loginArgs->loginType = LOGIN_TYPE_USER_AJAX;
        $frmLogin  = $MIOLO->getUI()->getForm($module, 'FrmLogin', $loginArgs );
        $busAuthenticate = $MIOLO->getBusiness($module, 'BusAuthenticate');
        $busLibraryUnit = $MIOLO->getBusiness($module, 'BusLibraryUnit');
        $busExemplaryControl = $MIOLO->getBusiness($module, 'BusExemplaryControl');
        $busMaterialPhysicalType = $MIOLO->getBusiness($module, 'BusMaterialPhysicalType');

        //$args->personId = MIOLO::_REQUEST('uid');
        $isMaterialMovement = (MIOLO::_REQUEST('action') == 'main:materialMovement');

        //força o id da libraryUnitId para o selecionado no diálogo caso o original não esteja selecionado
        if ( !$args->libraryUnitId )
        {
            $args->libraryUnitId = $args->reserveLibraryId;
        }

    	$controlNumber = MIOLO::_REQUEST('_id');
    	$title = _M('Requisição de alteração de estado do exemplar', $module );

        //Se estiver acessando pelo Circulação de material, pega o código do usuário
        if ($isMaterialMovement)
        {
            $logged = $frmLogin->isAuthenticated();
            $args->personId = MIOLO::_REQUEST('uid') ? MIOLO::_REQUEST('uid') : $args->personId;
            //Deve sempre pedir código do usuário, mesmo que tenha alguém logado na Minha biblioteca
            if (!$args->personId)
            {
                unset($logged);
            }
        }
        //Se estiver acessando por outros módulos, como as Pesquisas; pega o código do usuário
        else
        {
            $args->personId = $busAuthenticate->getUserCode();
            $logged         = $busAuthenticate->checkAcces();
        }

        if ( !$logged && !$args->personId)
        {
            $container = new MVContainer('containerReserve', $frmLogin->getLoginFields());
            GForm::injectContent( $container , $logged, $title,'600px');
            return false;
        }

        //Verifica se o usuário pertence a algum grupo que tem permissão para congelar materiais
        $busBond = $MIOLO->getBusiness($module, 'BusBond');
        $allPersonLink = $busBond->getAllPersonLink($args->personId);

        if ($allPersonLink)
        {
            $busReqChanExeStsAccess = $MIOLO->getBusiness($module, 'BusRequestChangeExemplaryStatusAccess');

            foreach ($allPersonLink as $key => $link)
            {
                $links[] = $link->linkId;
            }

            $accessLink = $busReqChanExeStsAccess->getRequestAccessForBasLinkId($links, true);

            if ($accessLink)
            {
                $statusAccess = $accessLink;
            }
        }
        else
        {
       	    GForm::error( _M('Falhou a operação de requisição do exemplar. <br> Grupo inválido.', $module ) );
       	    return false;
        }

        if(!$statusAccess)
        {
        	//FIXME Essa verificação não era pra ser feita no bussiness??? Foi criado todo um sistema de mensagens exatamente pra isso
       	    GForm::error( _M('Falhou a operação de requisição do exemplar.<br> Você não tem permissão para esta operação.', $module )  );
       	    return false;
        }

       	$busLibraryUnit->onlyWithAccess = true;
        $librarys   = $busLibraryUnit->listLibraryUnit(true);
        //o terceiro paramatro força tentar pegar os exemplares do pai
       	$exemplarys = $busExemplaryControl->getExemplaryOfMaterialByLibrary($controlNumber,null, true);
        //verifica se o estado do exemplar não esta na lista de ignorados
       	$exemplarys = FrmSimpleSearch::checkExemplarysInclude($exemplarys);

  		$libraryArray      = null;
   		$libraryArrayName  = null;

   		//avisa que não tem exemplares para congelar
   		//FIXME também deveria ser na operação??
   		if (!is_array($librarys) || !is_array($exemplarys) )
   		{
       	    GForm::error( _M('Falhou a operação de requisição do exemplar.<br> Não há exemplares.', $module ) );
       	    return false;
   		}

    	//monta array simples com relação de ids das bibliotecas
	    foreach ($librarys as $line => $info)
        {
             $libraryArray[$info->option]       = $info->option;
             $libraryArrayName[$info->option]   = $info->description;
        }

		foreach ($exemplarys as $line => $info)
		{
			//Quando estiver pesquisando em uma única biblioteca, pegá-la para não selecioná-la quando obra tem exemplares de várias unidades.
			if ( ($args->libraryUnitId) && (!stripos($args->libraryUnitId, ",")) )
            {
                $libraryArrays[] = $args->libraryUnitId;
                if ( in_array( $line, $libraryArrays) )
                {
                    $finalLibraryList[$line] = $libraryArrayName[$line];
                }
            }
            else
            {
	            if ( in_array( $line, $libraryArray) )
	            {
	                $finalLibraryList[$line] = $libraryArrayName[$line];
	            }
            }
		}

		//se só tiver uma possível marca ela automaticamente
		if ( count( $finalLibraryList ) == 1 )
		{
			//Quando estiver pesquisando em uma única biblioteca
			if ($libraryArrays)
			{
				$args->reserveLibraryId = $args->libraryUnitId;
			}
			else
			{
                $args->reserveLibraryId = $line;
			}
		}

        //trabalha de forma diferenciada caso exista uma unidade selecionada, pula seleção de unidade
        if ($args->reserveLibraryId)
        {
            if (!array_key_exists($args->reserveLibraryId, $finalLibraryList) )
            {
                GForm::error( 'Falhou a operação de requisição do exemplar. A biblioteca selecionada não tem exemplares.' );
                return false;
            }

            //Obtem intervalo de renovação
            $busRequestChangeExemplaryStatus = $MIOLO->getBusiness($module, 'BusRequestChangeExemplaryStatus');
            $periodInterval = $busRequestChangeExemplaryStatus->getPeriodInterval();

            //Se tiver datas deixa os campos de data readOnly com as datas da preferencia
            if ( $periodInterval->beginDate && $periodInterval->finalDate )
            {
                $datesReadOnly = true;
                $sDate = $periodInterval->beginDate;
                $fDate = $periodInterval->finalDate;
            }

            GForm::jsSetFocus('discipline', false);
            $fields[] = $text = new MTextField('controlNumber' , $controlNumber);
            $text->addStyle('display','none');
            $fields[] = $text = new MTextField('personId' , $busAuthenticate->getUserCode());
            $text->addStyle('display','none');
            $fields[] = $libraryId =  new MTextField('reserveLibraryId' , $args->reserveLibraryId);
            $libraryId->addStyle('display', 'none');

            $fields[] = new GSelection('exemplaryStatus', $exemplaryStatus->value, _M('Estado futuro', $module) , $statusAccess, null, null, null, true);

            if ( $datesReadOnly )
            {
                $fields[] = new MTextField('date', $sDate->getDate(GDate::MASK_DATE_USER), _M('Data', $module), FIELD_DATE_SIZE ,null, null, true);
                $fields[] = new MTextField('finalDate', $fDate->getDate(GDate::MASK_DATE_USER) , _M('Data final', $module), FIELD_DATE_SIZE, null, null, true );
            }
            else
            {
                $fields[] = new MCalendarField('date', GDate::now()->getDate(GDate::MASK_DATE_DB), _M('Data', $module) );
                $fields[] = new MCalendarField('finalDate', GDate::now()->getDate(GDate::MASK_DATE_DB),_M('Data final', $module));
            }
         
            $fields[] = new MTextField('discipline', null, _M('Disciplina', $module), FIELD_DESCRIPTION_SIZE);

           //alinha array de exemplares para array simples
            $exemplaryByStatusId  = $exemplarys[$args->reserveLibraryId];
            unset($exemplarys);

            //na busca de exemplares por unidade ela vem alinhando por estado, neste caso transformamos o array de exemplares num array simples
            if ( is_array( $exemplaryByStatusId ) )
            {
            	foreach ( $exemplaryByStatusId as $exemplaryStatusId => $exemplares )
            	{
            		if ( is_array( $exemplares ) )
            		{
            			foreach ( $exemplares as $line => $exemplar )
            			{
                            $exemplarys[] = $exemplar;
            			}
            		}
                }
            }

            $exemplaryData  = array();
            $filter = null;
            $itens  = null;
            
            if ( is_array($exemplarys) )
            {
                foreach ( $exemplarys as $line => $info )
                {
                    $info->volume                    = strlen($info->volume) ? $info->volume : '-';
                    $info->tomo                      = strlen($info->tomo)   ? $info->tomo   : '-';

                    $filter[$info->materialTypeId][$info->materialphysicaltypeid][$info->tomo][$info->volume][] = $info;
                }
                
                $line = 0;
                
                foreach ($filter as $materialTypeId => $materialPhysicalTypeId)
                {
                    foreach ($materialPhysicalTypeId as $key => $materialPhysical)
                    {
                        foreach ($materialPhysical as $tomo => $volumeArray)
                        {
                            foreach ($volumeArray as $volume => $objExemplary)
                            {
                                $itemArray = null;

                                foreach ($objExemplary as $it)
                                {
                                    $itens[$it->materialType][$it->materialPhysicalTypeId][$it->tomo][$it->volume].= "$it->itemNumber,";
                                    $controNumber = $it->controlNumber;
                                }

                                $itens[$it->materialType][$it->materialPhysicalTypeId][$it->tomo][$it->volume] = substr($itens[$it->materialType][$it->materialPhysicalTypeId][$it->tomo][$it->volume],0,-1);

                                //$itemTable = GMaterialDetail::getExemplaryTable($controNumber, null, $itemArray);
                                $itemTable = GMaterialDetail::getExemplaryTableByExemplaryObject( $objExemplary, SIMPLE_SEARCH_RESERVE_DETAIL_FIELD_LIST);

                                $treeData               = null;
                                $treeData[0]->title     = _M('Exemplares', $module);
                                $treeData[0]->content   = $itemTable;
                                $tree                   = new GTree('treeExemplaryList', $treeData);

                                $materialPhysicalDescription= $busMaterialPhysicalType->getMaterialPhysicalType($it->materialPhysicalTypeId)->description;
                                $exemplaryData[$line][] = new MCheckBox("listItemNumber[]", $itens[$it->materialType][$it->materialPhysicalTypeId][$it->tomo][$it->volume]);
                                $exemplaryData[$line][] = $objExemplary[0]->materialTypeDescription;
                                $exemplaryData[$line][] = $materialPhysicalDescription;
                                $exemplaryData[$line][] = $tomo;
                                $exemplaryData[$line][] = $volume;
                                $exemplaryData[$line][] = $tree->generate();
                                $line ++;
                            }
                        }
                    }
                }
            }

            $columns = array
            (
                 _M("Selecione", $module),
                 _M("Tipo do material", $module),
                 _M("Tipo físico do material", $module),
                 _M("Tomo", $module),
                 _M("Volume", $module),
                 _M("números dos exemplares", $module),
            );

            $fields['exemplaries'] = new MTableRaw(_M('Exemplares do material', $module), $exemplaryData, $columns, 'filters');
            $fields['exemplaries']->addAttribute('width', '100%');
            $fields[] = $text = new MTextField('personId', $args->personId);
            $text->addStyle('display','none');

            $buttons['print'] = new MButton('btnFinalizeRequest', _M("Finalizar requisição", $module), ':finalizeRequest', GUtil::getImageTheme('accept-16x16.png'));
            $buttons['close'] = GForm::getCloseButton();

            $fields[]   = new MDiv('containerRequestButtons', $buttons);
            $container  = new MFormContainer('containerRequestFields', GForm::accessibility( $fields ) );

            $f = array
            (
                MMessage::getMessageContainer(),
                new MDiv('divGeralRequest', $container->generate())
            );

            //para funcionar os mcalendarfield
            $MIOLO->page->onload("dojo.parser.parse();");

            GForm::injectContent($f , false, $title);
            return false;
        }

        // GERA SELECT PARA UNIDADES DE BIBLIOTECA
        $cnArgs = array('controlNumber' => $controlNumber , '_id' => $controlNumber);

        $fields['personId'] = $personId = new MTextField( 'personId',$args->personId ); //adiciona o campo, para manter o dados no form
        $personId->addStyle('display','none'); //esconde o campo
        $fields[] = new MDiv('', '<b>' . _M('Por favor selecione um unidade de biblioteca, ou pressione Enter para confirmar', $module) . '</b>');
		$fields[] = $reserveLibraryId = new GSelection('reserveLibraryId', null, null ,$finalLibraryList, null, null, null, TRUE);
        $reserveLibraryId->addAttribute('onchange','javascript:' . GUtil::getAjax('gridRequestChangeExemplaryStatus',$cnArgs));
        $reserveLibraryId->addAttribute('onPressEnter', GUtil::getAjax('gridRequestChangeExemplaryStatus',$cnArgs));
        GForm::jsSetFocus('reserveLibraryId');
        $ajax = GUtil::getAjax('gridRequestChangeExemplaryStatus',$cnArgs);
        $buttons[] = new MButton('btngridRequestChangeExemplaryStatus', _M('Próximo', $module) , $ajax, GUtil::getImageTheme('next-16x16.png'));
        $buttons[] = GForm::getCloseButton();
        $fields[] = new MDiv('', $buttons);

   	    GForm::injectContent( GUtil::alinhaForm($fields) , null, $title);
    }

    /**
     * Finaliza o processo de solicitação de congelamento pelo usuário
     */
    public function finalize()
    {
        $MIOLO = MIOLO::getInstance();
        $module = 'gnuteca3';
        $busExemplaryControl = $MIOLO->getBusiness($module, 'BusExemplaryControl');
        $args = (object) $_REQUEST;

        $busOperationRequestChangeExemplaryStatus = $MIOLO->getBusiness($module, 'BusOperationRequestChangeExemplaryStatus');
        $busOperationRequestChangeExemplaryStatus->clean();
        $busOperationRequestChangeExemplaryStatus->setFutureStatusId( $args->exemplaryStatus );
        $busOperationRequestChangeExemplaryStatus->setPersonId( $args->personId );

        $insert    = false;
        $listItens = null;

        //caso possa finalizar
        if ( $busOperationRequestChangeExemplaryStatus->checkAccess() )
        {
            foreach ($args->listItemNumber as $cont)
            {
                $it = explode(",", $cont);
                
                foreach ($it as $itenN)
                {
                    $exemplaryStatusId  = $busExemplaryControl->getExemplaryControl($itenN)->exemplaryStatusId;
                    //Não requisita materiais desaparecidos, danificados e descartados
                    if ( ($exemplaryStatusId != DEFAULT_EXEMPLARY_STATUS_DESAPARECIDO) 
                            && ($exemplaryStatusId != DEFAULT_EXEMPLARY_STATUS_DANIFICADO)
                            && ($exemplaryStatusId != DEFAULT_EXEMPLARY_STATUS_DESCARTADO) )
                    {
                        $composition = new stdClass();
                        $composition->itemNumber = $itenN;
                        $composition->confirm = DB_FALSE;
                        $composition->delete = false;
                        $composition->update = false;
                        $composition->insert = true;
                        $composition->exemplaryStatusId = $exemplaryStatusId;

                        $listItens[] = $composition;
                    }
                    else
                    {
                        //Se nenhum exemplar for escolhido, mostra mensagem avisando que sera necessario escolher um.
                        $busOperationRequestChangeExemplaryStatus->addError(100);
                    }
                }
            }

            $args->aproveJustOne = DB_TRUE;
            $busOperationRequestChangeExemplaryStatus->setLibraryUnit($args->libraryUnitId ? $args->libraryUnitId : $args->reserveLibraryId );
            $busOperationRequestChangeExemplaryStatus->setDate( $args->date );
            $busOperationRequestChangeExemplaryStatus->setFinalDate( $args->finalDate );
            $busOperationRequestChangeExemplaryStatus->setAproveJustOne( $args->aproveJustOne );
            $busOperationRequestChangeExemplaryStatus->setDiscipline( $args->discipline );
            $busOperationRequestChangeExemplaryStatus->checkComposition( $listItens );
            $insert = $busOperationRequestChangeExemplaryStatus->insertRequest();
        }

        if ( $insert )
        {
            GForm::information( _M("Requisição solicitada com sucesso!", $module) );
        }
        else
        {
            //define o foco na mensagem para leitura de accessibilidade
            GForm::jsSetFocus( MMessage::MSG_DIV_ID );
            $msg = implode(". <br/>", $busOperationRequestChangeExemplaryStatus->getErrors() );
            $box= MMessage::getStaticMessage( MMessage::MSG_DIV_ID, $msg , MMessage::TYPE_WARNING );
            $box->addAttribute('alt',$msg);
            $box->addAttribute('title',$msg);
            $MIOLO->ajax->setResponse( $box, MMessage::MSG_CONTAINER_ID);
        }
    }
}
?>