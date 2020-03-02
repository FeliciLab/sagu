<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Bruno Edgar Fuhr [bruno@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2013/09/06
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de SoluÃ§Ãµes Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de SoluÃ§Ãµes Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */
$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('classes/prtTableRaw.class.php', $module);
$MIOLO->uses('classes/prtReciboProtocolo.class.php', $module);

class frmProtocoloEncaminhamento extends frmMobile
{
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        $this->autoSave = false;
        
        parent::__construct(_M('Encaminhamento de protocolo', MIOLO::getCurrentModule()));
    }
    
    public function defineFields()
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busRequest = new BusinessProtocolBusRequestPtc();

        $colunas = array('Ação', 'Nº Solicitação', 'Assunto', 'Data', 'Situação');
        $imgParecer = $MIOLO->getUI()->getImageTheme($module, 'parecer.png');
        $imgInfo = $MIOLO->getUI()->getImageTheme($module, 'info.png');
        
        // Solicitações em aberto no setor da pessoa
        $filtros = new stdClass();
        $filtros->requestStatusId = array(
            BusinessProtocolBusRequestStatus::STATUS_SOLICITACAO_AGUARDANDO_PAGAMENTO,
            BusinessProtocolBusRequestStatus::STATUS_SOLICITACAO_EM_ANDAMENTO
        );
        $filtros->personId = $this->personid;
        
        $busEmployee = new BusinessBasicBusEmployee();
        $funcionario = $busEmployee->getEmployeeForPersonId($this->personid);
        $filtros->currentSectorId = $funcionario->sectorId;
        
        $solicitacoesAbertas = $busRequest->searchDiverseConsultationRequest($filtros, true);
        
        $row = NULL;
        foreach( $solicitacoesAbertas as $key => $solicitacaoAberta)
        {
            $parecerAction = MUtil::getAjaxAction('parecer', $solicitacaoAberta[0]);
            $linkParecer = new MImageLink('lnkParecer_' . $key, _M('Enviar parecer'), NULL, $imgParecer);
            $linkParecer->setJsHint(_M('Enviar parecer'));
            $linkParecer->addEvent('click', $parecerAction);
            
            $infoAction = MUtil::getAjaxAction('visualizar', $solicitacaoAberta[0]);
            $linkInfo = new MImageLink('lnkInfo_' . $key, _M('Visualizar'), NULL, $imgInfo);
            $linkInfo->setJsHint(_M('Visualizar'));
            $linkInfo->addEvent('click', $infoAction);
            
            $row[$key][] = $linkParecer . '&nbsp;&nbsp;&nbsp;' . $linkInfo;
            $row[$key][] = new MLabel($solicitacaoAberta[1], '', true);
            $row[$key][] = new MLabel($solicitacaoAberta[3], '', true);
            $row[$key][] = new MLabel($solicitacaoAberta[8], '', true);
            $row[$key][] = new MLabel($solicitacaoAberta[6], 'green', true);
        }
        
        $tableAbertas = new prtTableRaw('', $row, $colunas);
        foreach ( $row as $key => $line )
        {
            $tableAbertas->addCellAttributes($key, 0, array('align' => 'center', 'width' => '10%'));
            $tableAbertas->addCellAttributes($key, 1, array('align' => 'center', 'width' => '15%'));
            $tableAbertas->addCellAttributes($key, 2, array('width' => '50%'));
            $tableAbertas->addCellAttributes($key, 3, array('align' => 'center', 'width' => '10%'));
            $tableAbertas->addCellAttributes($key, 4, array('align' => 'center', 'width' => '15%'));
        }
        $tableAbertas->addStyle('width', '100%');
        $divAbertas = new MDiv('divAbertas', $tableAbertas);
        $divAbertas->addStyle('width', '100%');
        $fields[] = new MBaseGroup('grpAbertas', _M('Solicitações em aberto'), array($divAbertas));
        
        // Solicitações finalizadas no setor da pessoa
        $filters = new stdClass();
        $filters->requestStatusId = array(
            BusinessProtocolBusRequestStatus::STATUS_SOLICITACAO_FECHADA,
            BusinessProtocolBusRequestStatus::STATUS_SOLICITACAO_CANCELADA
        );
        $filters->personId = $this->personid;
        $filtros->currentSectorId = $funcionario->sectorId;
        
        $solicitacoesFechadas = $busRequest->searchDiverseConsultationRequest($filters, true);
        
        $row = NULL;
        foreach( $solicitacoesFechadas as $key => $solicitacaoFechada)
        {
            $infoAction = MUtil::getAjaxAction('visualizar', $solicitacaoFechada[0]);
            $linkInfo = new MImageLink('lnkInfoFechada_' . $key, _M('Visualizar'), NULL, $imgInfo);
            $linkInfo->addEvent('click', $infoAction);
            
            $row[$key][] = $linkInfo;
            $row[$key][] = new MLabel($solicitacaoFechada[1], '', true);
            $row[$key][] = new MLabel($solicitacaoFechada[3], '', true);
            $row[$key][] = new MLabel($solicitacaoFechada[8], '', true);
            $row[$key][] = new MLabel($solicitacaoFechada[6], 'red', true);
        }
        
        $tableFechadas = new prtTableRaw('', $row, $colunas);
        foreach ( $row as $key => $line )
        {
            $tableFechadas->addCellAttributes($key, 0, array('align' => 'center', 'width' => '10%'));
            $tableFechadas->addCellAttributes($key, 1, array('align' => 'center', 'width' => '15%'));
            $tableFechadas->addCellAttributes($key, 2, array('width' => '50%'));
            $tableFechadas->addCellAttributes($key, 3, array('align' => 'center', 'width' => '10%'));
            $tableFechadas->addCellAttributes($key, 4, array('align' => 'center', 'width' => '15%'));
        }
        $tableFechadas->addStyle('width', '100%');
        $divFechadas = new MDiv('divFechadas', $tableFechadas);
        $divFechadas->addStyle('width', '100%');
        $fields[] = new MBaseGroup('grpFinalizadas', _M('Solicitações finalizadas'), array($divFechadas));
        
	parent::addFields($fields);
    }
    
    public function parecer($solicitacao)
    {
        $MIOLO  = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        
        if ( $solicitacao )
        {
            try
            {
                $busRequest = new BusinessProtocolBusRequestPtc();
                $busSubjectRequest = new BusinessProtocolBusSubjectSector();
                $dataRequest = $busRequest->getRequestComplete($solicitacao);
                
                if ( !$dataRequest->currentLevel )
                {
                    $subjectSector = new stdClass();
                    $subjectSector->subjectId = $dataRequest->subjectId;
                    $subjectSector->sectorId = $dataRequest->sectorId;
                    $subjectSector->level = 1;
                    
                    if ( $busSubjectRequest->insertSubjectSector($subjectSector) )
                    {
                        $dataRequest->currentLevel = 1;
                    }
                }
                
                $numSolicitacaoLabel = new MText('numSolicitacaoLabel', _M('Número da solicitação:', $module), null, true);
                $numSolicitacao = new MText('number', $dataRequest->number);
                $fields[] = new MHContainer('hcNumSolicitacao', array($numSolicitacaoLabel, $numSolicitacao));
                
                $assuntoLabel = new MText('assuntoLabel', _M('Assunto:', $module), null, true);
                $assunto = new MText('subject', $dataRequest->subjectDescription);
                $fields[] = new MHContainer('hcAssunto', array($assuntoLabel, $assunto));
                
                $setorLabel = new MText('setorLabel', _M('Setor atual:', $module), null, true);
                $setor = new MText('currentSector', $dataRequest->currentSectorName);
                $fields[] = new MHContainer('hcNumSetor', array($setorLabel, $setor)); 
                
                // Campos hidden
                $fields[] = $requestIdField = new MTextField('requestId', $dataRequest->requestId);
                $requestIdField->addBoxStyle('display', 'none');
                
                $fields[] = $sectorIdField = new MTextField('sectorId', $dataRequest->currentSectorId);
                $sectorIdField->addBoxStyle('display', 'none');
                
                $fields[] = $subjectIdField = new MTextField('subjectId', $dataRequest->currentSubjectId);
                $subjectIdField->addBoxStyle('display', 'none');
                
                $fields[] = $levelField = new MTextField('level', $dataRequest->currentLevel);
                $levelField->addBoxStyle('display', 'none');
                
                $fields[] = $forwardedSubjectIdField = new MTextField('forwardedSubjectId', $dataRequest->currentSubjectId);
                $forwardedSubjectIdField->addBoxStyle('display', 'none');
                
                $fields[] = $forwardedLevelField = new MTextField('forwardedLevel', $dataRequest->currentLevel + 1);
                $forwardedLevelField->addBoxStyle('display', 'none');

                $fields[] = new MSeparator();

                //Field description
                $description = new MMultiLineField('description', null, _M('Parecer', $module), 20, 5, SAGU::getParameter('BASIC', 'FIELD_DESCRIPTION_SIZE'));
                $description->setJsHint(_M('Digite o parecer',$module));
                $fields[] = $description;
                $validators[] = new MRequiredValidator('description', _M('Parecer', $module));

                $busSubjectSector = new BusinessProtocolBusSubjectSector();
                $sectorsList = $busSubjectSector->getSubjectSectorByLevel($dataRequest->subjectId, ($dataRequest->currentLevel + 1));

                $busDispatch = new BusinessProtocolBusDispatch();
                $lastDispatch = $busDispatch->getRequestLastDispatch($dataRequest->requestId);

                if ( strlen($lastDispatch->requestId) > 0 )
                {
                    $fields[] = $replySectorIdField = new MTextField('replySectorId', $lastDispatch->sectorId);
                    $replySectorIdField->addBoxStyle('display', 'none');
                    
                    $fields[] = $replyLevelField = new MTextField('replyLevel', $lastDispatch->level);
                    $replyLevelField->addBoxStyle('display', 'none');

                    $btnReply = new MButton('btnReply', 'Responder');

                    $btn[] = $btnReply;
                }

                $btnSend = new MButton('btnSend', 'Enviar');

                if ( count($sectorsList) > 1 )
                {
                    $fields[] = new MSelection('forwardedSectorId', $this->getFormValue('forwardedSectorId'), _M('Para', $module), $sectorsList);
                    $validators[] = new MRequiredValidator('forwardedSectorId', _M('Encaminhar para', $module));
                }
                elseif ( count($sectorsList) == 1 )
                {
                    $fields[] = $forwardedSectorIdField = new MTextField('forwardedSectorId', $sectorsList[0][0]);
                    $forwardedSectorIdField->addBoxStyle('display', 'none');
                    $fields[] = $currentSector = new MTextField('forwardedSectorName', $sectorsList[0][1], _M('Para'), SAGU::getParameter('BASIC', 'FIELD_DESCRIPTION_SIZE'));
                    $currentSector->setReadOnly(true);
                }
                else
                {
                    $btnSend = new MButton('btnSend', 'Finalizar');
                    $fields[] = $closeRequestField = new MTextField('closeRequest', SAGU::getParameter('BASIC', 'DB_TRUE'));
                    $closeRequestField->addBoxStyle('display', 'none');
                }

                $btn[] = $btnSend;

                $hctButtons = new MHContainer('hctButtons', $btn);
                $hctButtons->addAttribute('style', 'width:500px');

                $fields[] = $hctButtons;
            }
            catch (Exception $e)
            {
                $this->addError($e->getMessage());
            }
            
            $dialog = new MDialog('dlgInfo', 'Enviar parecer', $fields);
            $dialog->show();
        }
    }
    
    public function visualizar($solicitacao)
    {
        if ( $solicitacao )
        {
            $MIOLO = MIOLO::getInstance();
            $busFile = $MIOLO->getBusiness('basic', 'BusFile');
            
            // Obtém a pessoa solicitante para montar o comprovante 
            $busRequest = new BusinessProtocolBusRequestPtc();
            $request = $busRequest->getRequest($solicitacao);
            
            $recibo = new prtReciboProtocolo($solicitacao, $request->personId);
            $txtInfo = $recibo->gerarRecibo();
            
            $dlgFields[] = $txtField =  new MMultiLineField('txtInfo', $txtInfo, '', 50, 12, 60);
            $txtField->setReadOnly(TRUE);

            // Documentos da solicitação
            $busDocumentPtc = new BusinessProtocolBusDocumentPtc();
            $documentosSolicitacao = $busDocumentPtc->searchRequestDocument($solicitacao);
            
            if ( count($documentosSolicitacao) > 0 )
            {
                foreach  ( $documentosSolicitacao as $docRequest )
                {
                    $busFile = new BusinessBasicBusFile();
                    
                    $file = $busFile->getFile($docRequest[3]);
                    $name = basename($file->uploadFileName);
                    $link = $MIOLO->getConf('home.url') . "/download.php?filename={$file->absolutePath}&contenttype={$file->contentType}&name={$name}";
                    
                    $anexoLinks[] = new MText('lnk_' . $docRequest[3], '<a href="' . $link . '" target="_blank">' . $docRequest[2] . '</a>');
                }
                
                $dlgFields[] = new MBaseGroup('hctDocuments', _M("Documentos anexados"), $anexoLinks, 'vertical');
            }
                        
            $link = $MIOLO->getConf('home.url') . "/download.php?filename={$recibo->obterArquivo()}";
            $linkArquivo = new MText('lnkArquivo', '<a href="' . $link . '" target="_blank">' . _M('Imprimir comprovante') . '</a>');
            $linkArquivo->addStyle('font-size', '18px');
            
            $dlgFields[] = MUtil::centralizedDiv($linkArquivo);
            $dlgFields[] = MUtil::centralizedDiv(new MButton('btnNao', _M('Fechar')));
            
            $dialog = new MDialog('dlgInfo', 'Informações da solicitação', $dlgFields);
            $dialog->show();
        }
    }
    
    public function btnSim_click($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busRequest = new BusinessProtocolBusRequestPtc();
        $solicitacao = $args->solicitacaoId;

        // HARDCODE
        if ( $busRequest->updateRequestStatus($solicitacao, 4) )
        {
            $url = $MIOLO->getActionURL($module, 'main:protocoloencaminhamento');
            $this->page->redirect($url);
        }
    }
    
    public function btnNao_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $url = $MIOLO->getActionURL($module, 'main:protocoloencaminhamento');
        $this->page->redirect($url);
    }

    public function novaSolicitacao($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $this->page->redirect($MIOLO->getActionURL($module, 'main:protocolo', NULL, array('new' => 1)));
    }
    
    public function btnReply_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = SAGU::getFileModule(__FILE__);

        $data = $this->getData();
        $dispatchData = new stdClass();

        $dispatchData->requestId = $data->requestId;
        $dispatchData->description = $data->description;
        $dispatchData->subjectId = $data->subjectId;
        $dispatchData->sectorId = $data->sectorId;
        $dispatchData->level = $data->level;
        $dispatchData->forwardedSubjectId = $data->subjectId;
        $dispatchData->forwardedSectorId = $data->replySectorId;
        $dispatchData->forwardedLevel = $data->replyLevel;

        try
        {
            $busRequest = new BusinessProtocolBusRequestPtc();
            $busRequest->beginTransaction();

            $busDispatch = new BusinessProtocolBusDispatch();
            $busDispatch->insertDispatch($dispatchData);
            $busRequest->updateRequestSector($dispatchData->requestId, $dispatchData->forwardedSectorId, $dispatchData->forwardedLevel);

            $busRequest->commit();
            SAGU::information(SAGU::getParameter('BASIC', 'MSG_RECORD_INSERTED_INFO'), $MIOLO->getActionURL($module, 'main:process:request', null, array('function'=>SForm::FUNCTION_UPDATE, 'requestId'=>$dispatchData->requestId)));
        }
        catch (Exception $e)
        {
            $this->addError($e->getMessage());
        }
    }

    public function btnSend_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = SAGU::getFileModule(__FILE__);

        $data = $this->getData();
        $dispatchData = new stdClass();
        
        if ( strlen($data->description) > 0 )
        {
            $dispatchData->requestId = $data->requestId;
            $dispatchData->description = $data->description;
            $dispatchData->subjectId = $data->subjectId;
            $dispatchData->sectorId = $data->sectorId;
            $dispatchData->level = $data->level;

            if ( $data->closeRequest != SAGU::getParameter('BASIC', 'DB_TRUE') )
            {
                $dispatchData->forwardedSubjectId = $data->subjectId;
                $dispatchData->forwardedSectorId = $data->forwardedSectorId;
                $dispatchData->forwardedLevel = $data->forwardedLevel;
            }

            try
            {
                $busRequest = new BusinessProtocolBusRequestPtc();
                $busRequest->beginTransaction();

                $busDispatch = new BusinessProtocolBusDispatch();
                $busDispatch->insertDispatch($dispatchData);

                if ( $data->closeRequest == SAGU::getParameter('BASIC', 'DB_TRUE') )
                {
                    $busRequest->updateRequestStatus($dispatchData->requestId, PtcRequest::STATUS_CLOSED);
                }
                else
                {
                    $busRequest->updateRequestSector($dispatchData->requestId, $dispatchData->forwardedSectorId, $dispatchData->forwardedLevel);
                }

                $busRequest->commit();
                //SAGU::information(SAGU::getParameter('BASIC', 'MSG_RECORD_INSERTED_INFO'), $MIOLO->getActionURL($module, 'main:process:request', null, array('function'=>SForm::FUNCTION_UPDATE, 'requestId'=>$dispatchData->requestId)));

                $url = $MIOLO->getActionURL($module, 'main:protocoloencaminhamento');
                $this->page->redirect($url);
            }
            catch (Exception $e)
            {
                new MMessageError($e->getMessage());
            }
        }
        else
        {
            $this->page->onLoad("alert('Informe a descrição do parecer.');");
            $this->setNullResponseDiv();
        }
    }
    
}

?>
