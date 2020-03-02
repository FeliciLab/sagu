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
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */
$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('classes/prtReciboProtocolo.class.php', $module);
$MIOLO->uses('types/AcpInscricao.class', 'pedagogico');
$MIOLO->uses('types/AcpCoordenadores.class', 'pedagogico');
$MIOLO->uses('types/PtcSubject.class', 'protocol');

class frmProtocolo extends frmMobile
{
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        $this->autoSave = false;
        parent::__construct(_M('Solicitações de protocolo', MIOLO::getCurrentModule()));
    }
    
    public function defineFields()
    {          
        $busAssunto = new BusinessProtocolBusSubject();
        $filtros = new stdClass();
        $filtros->isActive = array(BusinessProtocolBusSubject::TIPO_ATIVO_PORTAL, BusinessProtocolBusSubject::TIPO_ATIVO_AMBOS);        
        $filtros->availableTo = prtUsuario::obterTipoDeAcesso();
        $assuntos = $busAssunto->searchSubject($filtros);

        $fields[] = new MDiv();
        $fields[] = new SHiddenField('personId', $this->personid);
        
        $label = new MLabel(_M('Selecione o assunto:'));
        $label->addStyle('margin-left', '35px');
        $label->addStyle('width', '150px');        
        // Para não mostrar 'Sim' e 'Não' como opções do selection.
        if ( is_null($assuntos) )
        {
            $assuntos = array();
        }
        $selAssunto = new MSelection('selAssunto', '', '', $assuntos);
        $selAssunto->addAttribute('onchange', MUtil::getAjaxAction('selAssuntoChange'));        
        $fldAssunto[] = new MHContainer('contAssunto', array($label, $selAssunto));
        
        $fldAssunto[] = new MHContainer('divDescricao');
        $fields[] = new MDiv('teste', new MBaseGroup('grpAssunto', _M('Assunto'), $fldAssunto));
        $fields[] = new MDiv('divDocumentos');
        
        foreach($fields as $field)
        {
            $field->addStyle('margin-left', '5%');
            $field->addStyle('margin-right', '5%');
        }
        
        $fields[] = new MDiv();
        $btnVoltar = new MButton('btnVoltar', _M('Voltar'));
        $btnFinalizar = new MButton('btnFinalizar', _M('Finalizar'));        
        $fields[] = MUtil::centralizedDiv(array($btnVoltar, $btnFinalizar), 'divBtns');
        
	parent::addFields($fields);
    }
    
    public function selAssuntoChange($args)
    {
        $busSubjectSector = new BusinessProtocolBusSubjectSector();
        $sectorsList = $busSubjectSector->getSubjectSectorByLevel($args->selAssunto, 1);

        $busAssunto = new BusinessProtocolBusSubject();
        $assunto    = $busAssunto->getSubject($args->selAssunto);

        $contractFilters = new stdClass();
        $contractFilters->personId = $args->personId;

        if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO )
        {
            $busContract = new BusinessAcademicBusContract();
            $contract = $busContract->searchContract($contractFilters);
        }

        $response = array();

        $separator = new MSeparator();
        $separator->addStyle('margin-left', '30px');
        $response[] = $separator;
      
        if ( count($contract) > 1 )
        {
            $listContracts = $busContract->listContracts($args->personId);
            $contractsLabel = new MLabel('Selecione o contrato:');
            $contractsLabel->addStyle('margin-left', '30px');
            $contractsLabel->addStyle('width', '150px'); 
            $contracts = new MSelection('contractId', '', '', $listContracts);
            $response[] = new MHContainer('contContrato', array($contractsLabel, $contracts));
        }
        elseif ( !is_null($contract) )
        {
            $response[] = new SHiddenField('contractId', $contract[0][0]);
        }

        $busDocumento = new BusinessProtocolBusRequiredDocument();
        $documentos   = $busDocumento->listRequiredDocument($assunto->subjectId);
        
        if ( $assunto->showDescription == DB_TRUE )
        {
            $label = new MLabel(_M('Descrição:'));
            $label->addStyle('margin-left', '30px');
            $label->addStyle('width', '150px');
            $descricao = new MMultiLineField('description', '', '', 20, 5, 80);            
            $response[] = new MHContainer('divDescricao', array($label, $descricao));
        }
        else
        {
            $response[] = new MDiv('divDescricao');
        }

        if ( count($sectorsList) > 1 )
        {
            $sectorsLabel = new MLabel('Encaminhar para:');
            $sectorsLabel->addStyle('margin-left', '30px');
            $sectorsLabel->addStyle('width', '150px'); 
            $sectors = new MSelection('currentSectorId', '', '', $sectorsList);
            $response[] = new MHContainer('contSector', array($sectorsLabel, $sectors));
        }
        elseif ( count($sectorsList) == 1 )
        {
            $response[] = new MHiddenField('currentSectorId', $sectorsList[0][0]);
            $label = new MLabel(_M('Encaminhar para:'));
            $label->addStyle('margin-left', '30px');
            $label->addStyle('width', '150px');
            $currentSector = new MTextField('currentSectorName', $sectorsList[0][1], '', 80);
            $response[] = new MHContainer('divDescricao', array($label, $currentSector));
            $currentSector->setReadOnly(true);
        }
        else
        {            
            new MMessageWarning(_M('Nenhum encaminhamento cadastrado para o assunto informado.'));
            $response = NULL;
        }
        
        //
        // campos personalizados
        //
        $subjectId = $args->selAssunto;
        $customFields = $this->loadCustomFields($subjectId);
        
        if ( count($customFields) > 0 )
        {
            $vct = new MVContainer('vctCF', $customFields, MControl::FORM_MODE_SHOW_SIDE);
            $response[] = new MBaseGroup('divCFields', _M('Informações extras'), array($vct));
        }
        
        $this->setResponse($response, 'divDescricao');
        
        if ( is_array($documentos) )
        {
            unset($docs);
            foreach ( $documentos as $documento )
            {
                $lblRequired = ($documento[3] == DB_TRUE) ? '<font color="red"><strong> (descrição origatória)</strong></font>' : '';
                $label = new MLabel($documento[1] . $lblRequired . ':');
                $label->addStyle('margin-left', '30px');
                $label->addStyle('width', '200px');
                
                $lblRequired = ($documento[2] == DB_TRUE) ? '<font color="red"><strong> (descrição origatória)</strong></font>' : '';
                $upload = new MFileField("upload[{$documento[0]}]", null, null, $lblRequired);
                $uploadDescription = new MTextField("uploadDescription[{$documento[0]}]", '', '', 50);
                $uploadDescription->addStyle('margin-top', '-2px');
                $uploadDescription->setJsHint('Descrição do documento');
                
                $docs[] = new MHContainer('contUpload' . $documento[0], array($label, $uploadDescription, $upload));
            }
            
            $this->setResponse(new MBaseGroup('divDocumentos', _M('Documentos'), $docs), 'divDocumentos');
        }
        else
        {
            $this->setResponse(new MDiv('divDocumentos'), 'divDocumentos');
        }

        $this->setNullResponseDiv();
    }
    
    public function btnFinalizar_click($args)
    {
        $MIOLO = MIOLO::getInstance();
        
        if ( !$args->selAssunto )
        {
            new MMessageWarning(_M('Você deve selecionar o assunto.'));
        }
        elseif ( !$args->currentSectorId )
        {
            new MMessageWarning(_M('Você deve selecionar o encaminhamento.'));
        }
        else
        {
            $busAssunto = new BusinessProtocolBusSubject();
            $busDocumentoRequerido = new BusinessProtocolBusRequiredDocument();
            $busRequest = new BusinessProtocolBusRequestPtc();
            $busRequest = new BusinessProtocolBusRequestPtc();
            $busDocumento = new BusinessProtocolBusDocumentPtc();
            $busContract = new BusinessAcademicBusContract();
            
            if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO )
            {
                $contractFilters = new stdClass();
                $contractFilters->personId = $args->personId;
                $contract = (array) $busContract->searchContract($contractFilters);

                if ( count($contract) <= 0 )
                {
                    $inscricoes = AcpInscricao::buscarInscricoes($contractFilters);
                }
            }
            
            // Validar contrato.
            if ( !strlen($args->contractId) > 0 && count($contract) > 0 )
            {
                new MMessageWarning(_M('O campo \'Contrato\' é obrigatório.'));
                return;
            }

            $assunto = $busAssunto->getSubject($args->selAssunto);
            $documentos = $busDocumentoRequerido->listRequiredDocument($assunto->subjectId);
            
            // Validar descrição.
            if ( $assunto->showDescription == DB_TRUE && $assunto->descriptionRequired == DB_TRUE && !($args->description) )
            {
                new MMessageWarning(_M('O campo \'Descrição\' é obrigatório.'));
                return;
            }
            
            // Validar documentos.
            foreach( $documentos as $documento )
            {
                if ( $documento[2] == DB_TRUE )
                {
                    if ( !$args->upload[$documento[0]] )
                    {
                        new MMessageWarning(_M("É necessário selecionar um arquivo para o documento '{$documento[1]}'."));
                        return;
                    }
                }
                
                if ( $documento[3] == DB_TRUE )
                {
                    if ( !$args->uploadDescription[$documento[0]] )
                    {
                        new MMessageWarning(_M("É necessário informar uma descrição para o documento '{$documento[1]}'."));
                        return;
                    }
                }
            }
            
            $requestData = new stdClass();
            $requestData->personId = $this->personid;
            $requestData->subjectId = $assunto->subjectId;
            $requestData->description = $args->description;
            $requestData->contractId = $args->contractId;
            $requestData->perfil = prtUsuario::obterTipoDeAcesso();

            //Se for aluno
            if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO )
            {
                //Acadêmico
                if ( strlen(prtUsuario::obterContratoAtivo()) > 0 )
                {
                    $requestData->contractId = prtUsuario::obterContratoAtivo();
                    
                }//Pedagógico
                elseif ( strlen(prtUsuario::obterInscricaoAtiva()) > 0 )
                {
                    $requestData->inscricaoId = prtUsuario::obterInscricaoAtiva();
                }
            }
            
            $cobraTaxa = PtcSubject::cobrarTaxaPrimeiraSolicitacao($requestData);
            
            // Se tem taxa o status da solicitação deve ser 'Aberta', senão 'Em andamento'.
            if ( ($assunto->taxValue <= 0) || ($cobraTaxa == DB_FALSE && ($assunto->taxValue > 0)) )
            {
                // HARDCODE
                $requestData->statusId = 2;
            }
            else
            {
                // HARDCODE
                $requestData->statusId = 1;
            }            
            $requestData->sectorId = $assunto->sectorId;
            $requestData->currentSectorId = $args->currentSectorId;
            $requestData->currentSubjectId = $assunto->subjectId;
            $requestData->currentLevel = 1;
            
            // valida campos personalizados requeridos
            $data = $this->getData();
            
            if ( strlen($requestData->subjectId) > 0 )
            {
                $this->loadCustomFields($requestData->subjectId);
                
                foreach ( (array) $this->mioloCustomFields as $cf )
                {
                    if ( $cf->required == DB_TRUE )
                    {
                        $inputId = $cf->getInputId();
                        $value = $data->$inputId;
                        
                        if ( strlen($value) == 0 )
                        {
                            new MMessageWarning("O campo \"{$cf->label}\" é obrigatório");
                            return;
                        }
                    }
                }
            }
            
            $requestData = $this->encaminhamentoParaCoordenadores($requestData);
            
            $request = $busRequest->insertRequest($requestData);
            
            if ( is_array($documentos) )
            {
                $upload = MFileField::uploadFiles($MIOLO->getConf('home.html') . "/files/tmp/");
            }
                        
            if ( $request )
            {
                $busFile = $MIOLO->getBusiness('basic', 'BusFile');
                foreach ( $documentos as $documento )
                {
                    $fileId = NULL;
                    $filePath = $MIOLO->getConf('home.html') . "/files/tmp/" . $args->upload[$documento[0]];
                    if( is_file($filePath) )
                    {
                        $fdata = new stdClass();            
                        $fdata->uploadFileName = $filePath;
                        $fdata->contentType = mime_content_type($filePath);

                        $fileId = $busFile->insertFile($fdata, $filePath);
                        
                        $documentData = new stdClass();
                        $documentData->requestId = $request;
                        $documentData->description = $args->uploadDescription[$documento[0]];
                        $documentData->fileId = $fileId;

                        $busDocumento->insertDocument($documentData);
                    }
                }
                
                // Salva campos personalizados
                $this->saveCustomFields($request);
                
                // Verifica se é do tipo 'solicitação de reposição de aula' e se tem coordenador para mandar email.
                if ( $requestData->subjectId == SAGU::getParameter('PROTOCOL', 'TIPO_PROTOCOLO_REPOSICAO_AULA') )
                {
                    $busCourseCoordinator = new BusinessAcademicBusCourseCoordinator();
                    $coordenadores = array();
                    $isContract = false;
                    
                    if ( strlen($contract[0][0]) > 0 )
                    {
                        $coordenadores = $busCourseCoordinator->obterCoordenadoresPeloContrato($contract[0][0]);
                        $isContract = true;
                    }
                    else if ( strlen($inscricoes[0]->inscricaoid) > 0 )
                    {
                        $coordenadores = AcpCoordenadores::obterCoordenadoresPorInscricao($inscricoes[0]->inscricaoid);
                    }
                    
                    foreach ( $coordenadores as $coordenador )
                    {
                        if ( !$isContract || ( $isContract && $busCourseCoordinator->isCourseCoordinator($coordenador[0]) ) )
                        {
                            $preferencias = $busCourseCoordinator->obterPreferenciasDoCoordenador($coordenador[0]);

                            if ( $preferencias[2] == DB_TRUE )
                            {
                                $busPerson = new BusinessBasicBusPhysicalPerson();
                                $person = $busPerson->getPhysicalPerson($coordenador[0]);
                                $personName = $person->name;
                                $personEmail = $person->email;

                                $tags = array( '$DESCRIPTION' => $requestData->description );

                                // Business email and company
                                $busEmail = new BusinessBasicBusEmail();
                                $busCompany = new BusinessBasicBusCompany();
                                $emailId = SAGU::getParameter('PROTOCOL', 'REPOSICAO_AULA_EMAIL_ID');

                                if ( $emailId != 0 )
                                {
                                    $dataEmail = $busEmail->getEmail($emailId);
                                    $dataCompany = $busCompany->getCompany(SAGU::getParameter('BASIC', 'DEFAULT_COMPANY_CONF'));

                                    // Parameters
                                    $from = strtolower($dataEmail->from);
                                    $fromName = $dataCompany->acronym;
                                    $recipient[$personName] = strtolower($personEmail);
                                    $subject = $dataEmail->subject;
                                    $body = strtr($dataEmail->body, $tags);
                                    $mail = new sendEmail($from, $fromName, $recipient, $subject, $body, array());
                                    $mail->sendEmail();
                                }
                            }
                        }
                    }
                }
                
                $this->setResponse(MUtil::centralizedDiv(array(new MButton('btnVoltar', _M('Voltar'))), 'divBtns'), 'divBtns');
                
                // Popup com as informações da solicitação                
                $recibo = new prtReciboProtocolo($request, $this->personid);
                $txtInfo = $recibo->gerarRecibo();
                
                // Campos personalizados
                $customFieldIds = BasCustomField::getCustomFieldIdsBySubject($requestData->subjectId);
                $values = BasCustomField::getLabelAndValues($customFieldIds, $request);

                foreach ( $values as $label => $value )
                {
                    $label = strtoupper($label);
                    $txtInfo .= "\n{$label}: {$value}";
                }
                
                $dlgFields[] = $fldTxt = new MMultiLineField('txtInfo', $txtInfo, '', 50, 12, 60);
                $fldTxt->setReadOnly(TRUE);
                
                $link = $MIOLO->getConf('home.url') . "/download.php?filename={$recibo->obterArquivo()}";
                $linkArquivo = new MText('lnkArquivo', '<a href="' . $link . '" target="_blank">' . _M('Imprimir comprovante') . '</a>');
                $linkArquivo->addStyle('font-size', '18px');

                $dlgFields[] = MUtil::centralizedDiv($linkArquivo);                
                $dlgFields[] = MUtil::centralizedDiv(array(new MButton('btnVoltar', _M('Fechar'))), 'divBtns');
                
                $dialog = new MDialog('dlgInfo', _M('Solicitação efetuada com sucesso'), $dlgFields);
                $dialog->show();
            }
            else
            {
                new MMessageError(_M('Erro ao realizar a solicitação de protocolo.'));
            }
        }

        $this->setNullResponseDiv();
    }
    
    /**
     * Caso os encaminhamentos do assunto selecionado estejam marcados para coordenadores.
     * 
     * @param stdClass $args
     */
    public function encaminhamentoParaCoordenadores($data)
    {
        $filters = new stdClass();
        $filters->subjectId = $data->subjectId;

        // Busca todos encaminhamentos do assunto.
        $busSubjectSector = new BusinessProtocolBusSubjectSector();
        $subjectSectors = $busSubjectSector->searchSubjectSector($filters);

        foreach ( $subjectSectors as $subjectSector  )
        {
            // Se a pessoa solicitante possuir contrato.
            if ( !is_null($data->contractId) )
            {
                $busContract = new BusinessAcademicBusContract();
                $contract = $busContract->getContract($data->contractId);

                $data->courseId = $contract->courseId;
                $data->courseVersion = $contract->courseVersion;
                $data->turnId = $contract->turnId;
                $data->unitId = $contract->unitId;

                break;
            }
        }
        
        return $data;
    }
    
    public function btnVoltar_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $this->page->redirect($MIOLO->getActionURL($module, 'main:protocolo', NULL, NULL));
    }
    
}

?>
