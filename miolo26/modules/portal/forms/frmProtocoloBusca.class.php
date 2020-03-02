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
$MIOLO->uses('classes/prtTableRaw.class.php', $module);
$MIOLO->uses('classes/prtReciboProtocolo.class.php', $module);

class frmProtocoloBusca extends frmMobile
{
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        $this->autoSave = false;
        
        parent::__construct(_M('Solicitações de protocolo', MIOLO::getCurrentModule()));
    }
    
    public function defineFields()
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busRequest = new BusinessProtocolBusRequestPtc();

        $fields[] = $this->button('btnAddSolicitacao', _M('Adicionar solicitação'), null, MUtil::getAjaxAction('novaSolicitacao'));

        $colunas = array('Ação', 'Nº Solicitação', 'Assunto', 'Data', 'Situação');
        $imgCancelar = $MIOLO->getUI()->getImageTheme($module, 'cancel.png');
        $imgInfo = $MIOLO->getUI()->getImageTheme($module, 'info.png');
        $imgPrint = $MIOLO->getUI()->getImageTheme('modern', 'bf-imprimir-on.png');
        $imgPay = $MIOLO->getUI()->getImageTheme('modern', 'financialSituation-20x20.png');
        $imgPayNO = $MIOLO->getUI()->getImageTheme('modern', 'financialSituation-20x20NO.png');
        
        
        // Solicitações abertas
        $filtros = new stdClass();
        // HARDCODE
        $filtros->requestStatusId = array(1,2);
        $filtros->personId = $this->personid;
        $solicitacoesAbertas = $busRequest->searchDiverseConsultationRequest($filtros);
        
        $row = NULL;
        
        foreach( $solicitacoesAbertas as $key => $solicitacaoAberta)
        {
            $actions = array();
            
            // Cancelar
            $cancelarAction = MUtil::getAjaxAction('cancelar', $solicitacaoAberta[0]);
            $linkCancelar = new MImageLink('lnkCancelar_' . $key, _M('Cancelar'), NULL, $imgCancelar);
            $linkCancelar->addEvent('click', $cancelarAction);
            $linkCancelar->addAttribute('title','Cancelar');
            $actions[] = $linkCancelar;
            
            // Visualizar
            $infoAction = MUtil::getAjaxAction('visualizar', $solicitacaoAberta[0]);
            $linkInfo = new MImageLink('lnkInfo_' . $key, _M('Visualizar'), NULL, $imgInfo);
            $linkInfo->addEvent('click', $infoAction);
            $linkInfo->addAttribute('title','Visualizar');
            $actions[] = $linkInfo;
            
            // Passa o parametro "number" para o documento
            if ( SAGU::getParameter('PORTAL', 'IMPRIMIR_COMPROVANTE_PROTOCOLO') == DB_TRUE )
            {
                $infoPrint = MUtil::getAjaxAction('gerarDocumento', $solicitacaoAberta[1]);
                $linkPrint = new MImageLink('lnkPrint_' . $key, _M('Imprimir'), null, $imgPrint);
                $linkPrint->addEvent('click', $infoPrint);
                $linkPrint->addAttribute('title','Imprimir');
                $actions[] = $linkPrint;
            }
            if($solicitacaoAberta[7] == 1)
            {
                // Pagar taxa
                $payAction = MUtil::getAjaxAction('pagarTaxa', $solicitacaoAberta[0]);
                $linkPay = new MImageLink('lnkPay_' . $key, _M('Pagar taxa'), NULL, $imgPay);
                $linkPay->addEvent('click', $payAction);
                $linkPay->addAttribute('title','Pagar taxa');
                $actions[] = $linkPay;
            }
            else
            {
                $linkPay = new MImageLink('lnkPay_' . $key, _M('Pagar taxa'), NULL, $imgPayNO);
                $linkPay->addAttribute('title','Pagar taxa');
                $actions[] = $linkPay;
            }
            
            $row[$key][] = implode('&nbsp;', $actions);
            $row[$key][] = new MLabel($solicitacaoAberta[1], '', true);
            $row[$key][] = new MLabel($solicitacaoAberta[3], '', true);
            $row[$key][] = new MLabel($solicitacaoAberta[8], '', true);
            $row[$key][] = new MLabel($solicitacaoAberta[6], 'green', true);
        }
        
        $tableAbertas = new prtTableRaw('', $row, $colunas);
        foreach ( $row as $key => $line )
        {
            $tableAbertas->addCellAttributes($key, 0, array('align' => 'center', 'width' => '10%', 'nowrap' => 'true'));
            $tableAbertas->addCellAttributes($key, 1, array('align' => 'center', 'width' => '15%'));
            $tableAbertas->addCellAttributes($key, 2, array('width' => '50%'));
            $tableAbertas->addCellAttributes($key, 3, array('align' => 'center', 'width' => '10%'));
            $tableAbertas->addCellAttributes($key, 4, array('align' => 'center', 'width' => '15%'));
        }
        $tableAbertas->addStyle('width', '100%');
        $divAbertas = new MDiv('divAbertas', $tableAbertas);
        $divAbertas->addStyle('width', '100%');
        $fields[] = new MBaseGroup('grpAbertas', _M('Solicitações em aberto'), array($divAbertas));
        // -----------------------
        
        // Solicitações finalizadas
        $filtros = new stdClass();
        // HARDCODE
        $filtros->requestStatusId = array(3,4);
        $filtros->personId = $this->personid;
        $solicitacoesFechadas = $busRequest->searchDiverseConsultationRequest($filtros);
        
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
        // -------------------
        
	parent::addFields($fields);
    }
    
    public function cancelar($solicitacao)
    {
        if ( $solicitacao )
        {            
            $infoText = new MText('txtCancelamento', _M('Deseja cancelar esta solicitação de protocolo?'));
            $infoText->addStyle('font-weight', 'bold');
            $infoText->addStyle('font-size', '18px');
            $dlgFields[] = MUtil::centralizedDiv($infoText);
            $dlgFields[] = $solField = new MTextField('solicitacaoId', $solicitacao);
            $solField->setVisibility(false);
            
            $buttons[] = new MButton('btnSim', _M('Sim'));
            $buttons[] = new MButton('btnNao', _M('Não'));
            $dlgFields[] = MUtil::centralizedDiv($buttons);
            
            $dialog = new MDialog('dlgConfirmaCancelamento', 'Confirme o cancelamento', $dlgFields);
            $dialog->setWidth('40%');
            $dialog->show();
        }
    }
    
    public function visualizar($solicitacao)
    {
        if ( $solicitacao )
        {
            $MIOLO = MIOLO::getInstance();
            $busFile = $MIOLO->getBusiness('basic', 'BusFile');
            $busRequest = $MIOLO->getBusiness('protocol', 'BusRequestPtc');
                        
            // Popup com as informações da solicitação
            $recibo = new prtReciboProtocolo($solicitacao, $this->personid);
            $txtInfo = $recibo->gerarRecibo();

            // Campos personalizados
            $request = $busRequest->getRequest($solicitacao);
            $customFieldIds = BasCustomField::getCustomFieldIdsBySubject($request->subjectId);
            $values = BasCustomField::getLabelAndValues($customFieldIds, $solicitacao);
            
            foreach ( $values as $label => $value )
            {
                $label = strtoupper($label);
                $txtInfo .= "\n{$label}: {$value}";
            }

            $dlgFields[] = $txtField =  new MMultiLineField('txtInfo', $txtInfo, '', 50, 12, 60);
            $txtField->setReadOnly(TRUE);
            
            $link = $MIOLO->getConf('home.url') . "/download.php?filename={$recibo->obterArquivo()}";
            $linkArquivo = new MText('lnkArquivo', '<a href="' . $link . '" target="_blank">' . _M('Imprimir comprovante') . '</a>');
            $linkArquivo->addStyle('font-size', '18px');
            
            $divLink = new MDiv('divLink',$linkArquivo);
            $divLink->addStyle('text-align', 'center');
            $dlgFields[] = $divLink;
            
            $divButton = new MDiv('divButton',new MButton('btnNao', _M('Fechar')));
            $divButton->addStyle('text-align', 'center');
            $dlgFields[] = $divButton;
            
            $dialog = new MDialog('dlgInfo', 'Informações da solicitação', $dlgFields);
            $dialog->show();
        }
    }
    
    public function gerarDocumento($solicitacao)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $filePath = $MIOLO->getModulePath('portal', 'reports/comprovanteProtocolo.jrxml');
        $number = strlen($solicitacao) > 0 ? $solicitacao : MIOLO::_REQUEST('solicitacao');

        $report = new MJasperReport();
        $report->executeJRXML('portal', $filePath, array(
            'str_SAGU_PATH' => $this->getSaguReportPath(),
            'number' => $number,
        ));

        $this->setNullResponseDiv();
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
            $url = $MIOLO->getActionURL($module, 'main:protocolo');
            $this->page->redirect($url);
        }
    }
    
    public function btnNao_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $url = $MIOLO->getActionURL($module, 'main:protocolo');
        $this->page->redirect($url);
    }

    public function novaSolicitacao($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $this->page->redirect($MIOLO->getActionURL($module, 'main:protocolo', NULL, array('new' => 1)));
    }   
    
    public function pagarTaxa($requestId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $data = new stdClass();
        $data->requestId = $requestId;
        $invoiceId = $this->closeRequest($data);

        $this->page->redirect($MIOLO->getActionURL($module, 'main:financeiro', NULL, array('SinvoiceId' => $invoiceId)));
    }
    
    public function closeRequest($data)
    {
        $MIOLO = MIOLO::getInstance();
        $module = SAGU::getFileModule(__FILE__);

        $busInvoice = new BusinessFinanceBusReceivableInvoice();
        $busDefaultOperations = new BusinessFinanceBusDefaultOperations();
        $busOperation = new BusinessFinanceBusOperation();
        $busEntry = new BusinessFinanceBusEntry();
        
        //ObtÃ©m a operaÃ§Ã£o padrÃ£o para taxas de protocolo
        $defaultOperations = $busDefaultOperations->getDefaultOperations();
        $operation = $busOperation->getOperation($defaultOperations->protocolOperation);

        //ObtÃ©m os dados da solicitaÃ§Ã£o
        $busRequest = new BusinessProtocolBusRequestPtc();
        $requestData = $busRequest->getRequestComplete($data->requestId);

        if(strlen($requestData->invoiceId) == 0)
        {
            //Dados para o tÃ­tulo referente a taxa
            $invoiceData = new FinReceivableInvoice();
            $invoiceData->personId = $requestData->personId;
            $invoiceData->accountSchemeId = SAGU::getParameter('protocol', 'PROTOCOL_DEFAULT_ACCOUNT_SCHEME_ID');
            $invoiceData->costCenterId = SAGU::getParameter('protocol', 'PROTOCOL_DEFAULT_COST_CENTER');
            $invoiceData->parcelNumber = 1;
            $invoiceData->emissionDate = SAGU::getDateNow();
            $invoiceData->maturityDate = SAGU::getDateNow();
            $invoiceData->emissionTypeId = SAGU::getParameter('basic', 'DEFAULT_EMISSION_TYPE_ID');
            $invoiceData->value = SAGU::formatNumber($requestData->taxValue);
            $invoiceData->policyId = SAGU::getParameter('protocol', 'PROTOCOL_DEFAULT_POLICY_ID');
            $invoiceData->bankAccountId = SAGU::getParameter('basic', 'DEFAULT_BANK_ACCOUNT_ID');
            $invoiceData->comments = $operation->description;
            $invoiceData->incomeSourceId = SAGU::getParameter('protocol', 'PROTOCOL_INCOME_SOURCE_ID');
            $invoiceData->referenceMaturityDate = SAGU::getDateNow();


            $data->invoiceId = $busInvoice->insertReceivableInvoice($invoiceData);
            $invoiceData->invoiceId = $data->invoiceId;

            //LanÃ§amento da taxa
            $entry = new FinEntry();
            $entry->invoiceId = $invoiceData->invoiceId;
            $entry->operationId = $operation->operationId;
            $entry->entryDate = SAGU::getDateNow();
            $entry->value = $requestData->taxValue;
            $entry->costCenterId = SAGU::getParameter('protocol', 'PROTOCOL_DEFAULT_COST_CENTER');
            $entry->comments = _M('Gerado a partir da solicitaÃ§Ã£o @1', $module, $requestData->number);

            $busRequest->updateRequestInvoice($requestData->requestId, $data->invoiceId);

            $busInvoice->insertEntry( $entry );
        }
        return SAGU::NVL($invoiceData->invoiceId, $requestData->invoiceId);
    }
}
?>