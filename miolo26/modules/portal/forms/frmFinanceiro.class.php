<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2012/09/25
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

class frmFinanceiro extends frmMobile
{
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Financeiro', MIOLO::getCurrentModule()));

        $this->setShowPostButton(FALSE);
    }

    public function defineFields()
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $this->autoSave = false;
        
        $invoiceId = MIOLO::_REQUEST('SinvoiceId');
        
        //Se tiver invoinceId mostrar os titulos em aberto EXPANDIDOS=TRUE caso contratio expandir FALSE
        if ( strlen($invoiceId) > 0 )
        {
            $sections[] = new jCollapsibleSection(_M('Títulos em aberto'), $this->titulosEmAberto($this->personid), TRUE, 'abertos');
        }
        else
        {
            $sections[] = new jCollapsibleSection(_M('Títulos em aberto'), $this->titulosEmAberto($this->personid), FALSE, 'abertos');
        }
        
        $sections[] = new jCollapsibleSection(_M('Títulos fechados'), $this->titulosFechados($this->personid), false, 'fechados');
        
        $fields[] = new jCollapsible('financeiro', $sections);

	parent::addFields($fields);
    }
    
    public function titulosEmAberto($personId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $fields = array();
        
        $MIOLO->uses('classes/prtFinanceiro.class.php', $module);
        $financeiro = new PrtFinanceiro();
        
        $isInadimplente = $financeiro->isInadimplente($personId);
	
        $titulos = $financeiro->obterTitulosEmAberto($personId);

        if ( $isInadimplente )
        {
            $invoiceIds = array();
            
            foreach ( $titulos as $titulo )
            {
                $invoiceIds[] = $titulo[0];
            }
            
            if ( count($invoiceIds) > 0 )
            {
                $primeiroTituloVencido = $financeiro->obterPrimeiroTituloVencido($invoiceIds);
            }
            
            if ( count($invoiceIds) > 1 )
            {
                $fields[] = MMessageInformation::getStaticMessage('msgInfo', _M('Para pagar mais que um título deve ser solicitada negociação.'), MMessage::TYPE_INFORMATION);
            }
        }

        // Varre os títulos em aberto para a obtenção e exibição de seus dados, adicionando as devidas ações conforme confiogurações.
        foreach ( $titulos as $k => $titulo )
        {
            $btnPrint    = false;
            $btnImprimir = null;
            $btnCielo    = null;
            
            // Verifica se poderá ser exibida a ação de impressão do boleto, conforme configurações.
            if ( $isInadimplente && SAGU::getParameter('FINANCE', 'IMPRIMIR_SOMENTE_PRIMEIRO_BOLETO_ATRASADO') == DB_TRUE )
            {
                if ( $titulo[0] == $primeiroTituloVencido )
                {
                    $btnPrint = true;
                }
            }
            else
            {
                $btnPrint = true;
            }
            
            // Se autorizado a exibir a ação de impressão do boleto, checa configurações específicas.
            if ( $btnPrint )
            {
                // Integracao com cielo, ação para pagamento com cartão.
                $btnCielo = $this->obterBotaoDePagamentoComCartao($titulo[0]);
        
                // Ação para impressão do boleto.
                if ( SAGU::getParameter('FINANCE', 'DIA_PARA_DISPONIBILIZAR_IMPRESSAO_DE_TITULOS') != 0 )
                {
                    $dia = SAGU::getParameter('FINANCE', 'DIA_PARA_DISPONIBILIZAR_IMPRESSAO_DE_TITULOS');                
                    $dataHoje = SAGU::getDateNow();

                    $data = explode('/', $dataHoje);

                    $dataLiberacao = $dia.'/'.$data[1].'/'.$data[2];

                    if ( $dataHoje >= $dataLiberacao )
                    {
                        $btnImprimir = $this->obterBotaoDeImpressaoDoBoleto($titulo[0]);
                    }
                }
                else
                {
                    $btnImprimir = $this->obterBotaoDeImpressaoDoBoleto($titulo[0]);
                }
            }
            
            $titulos[$k][10] = 'R$ ' . $titulos[$k][8];
            $titulos[$k][8] = 'R$ ' . $titulos[$k][8];
            $titulos[$k][9] = 'R$ ' . $titulos[$k][9];
            $titulos[$k][11] = 'R$ ' . $titulos[$k][11];
            $titulos[$k][12] = $btnImprimir . $btnCielo;
        }

        $columns[0]  = _M('Título');
        $columns[3]  = _M('Tipo de cobrança');
        $columns[6]  = _M('Emissão');
        $columns[7]  = _M('Vencimento');
        $columns[8]  = _M('Valor');
        $columns[9]  = _M('Aberto');
        $columns[11] = _M('Valor atualizado');
        $columns[12] = 'actions';

        $options = array('title_key' => '10');
                //direcionar pagar taxa do protocolo
        if(strlen(MIOLO::_REQUEST('SinvoiceId')) > 0)
        {
           foreach ( $titulos as $k => $titulo )
           {
               if(MIOLO::_REQUEST('SinvoiceId') == $titulo[0])
               {
                   $titulos = array();
                   $titulos[0] = $titulo;
               }
           }
        }
        $fields[] = $this->listView($id, $title, $columns, $titulos, $options);
        
        return $fields;
    }
    
    public function titulosFechados($personId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busReceivableInvoice = $MIOLO->getBusiness('finance', 'BusReceivableInvoice');
        
        $MIOLO->uses('classes/prtFinanceiro.class.php', $module);
        $financeiro = new PrtFinanceiro();

        $titulos = $financeiro->obterTitulosFechados($personId);

        foreach($titulos as $k=>$titulo)
        {
            $invoiceid = $titulos[$k][0];

            $titulos[$k][8] = 'R$ ' . $titulos[$k][8];
            $titulos[$k][9] = 'R$ ' . $titulos[$k][9];
            $titulos[$k][11] = 'R$ ' . $titulos[$k][11];
          //  $titulos[$k][12] = $busReceivableInvoice->obterFormaDePagamentoDoTitulo($invoiceid);
        }

        $fields = array();
        
        $columns[0] = _M('Título');
        $columns[3] = _M('Tipo de cobrança');
        $columns[6] = _M('Emissão');
        $columns[7] = _M('Vencimento');
        $columns[8] = _M('Valor');
        $columns[9] = _M('Aberto');
        $columns[11] = _M('Valor atualizado');
        $columns[12] = _M('Forma de pagamento');
        
        $options = array('title_key'=>'0');
        
        $fields[] = $this->listView($id, $title, $columns, $titulos, $options);
        
        return $fields;
    }
    
    /**
     * Retorna a ação de impressão do boleto.
     * 
     * @param int $invoiceId
     * @return String html
     */
    private function obterBotaoDeImpressaoDoBoleto($invoiceId)
    {
        $MIOLO = MIOLO::getInstance();
        
        $actionImprimir = $MIOLO->getActionURL('finance', 'main:process:printInvoice', null, array('_invoiceId' => $invoiceId, 'event' => 'submit_button_click'));
        $mButton = new MButton('botaoImprimir', _M('Imprimir boleto'), 'WINDOW:' . $actionImprimir );
        
        return $mButton->generate();
    }
    
    /**
     * Retorna ação de pagamento via cartão.
     * 
     * @param int $invoiceId
     * @return String html
     */
    private function obterBotaoDePagamentoComCartao($invoiceId)
    {
        $btnCielo = null;

        if ( SAGU::getParameter('FINANCE', 'CIELO') == DB_TRUE )
        {   
            $buttonCielo = new MButton('botaoCielo', _M('Pagar com cartão de crédito'), MUtil::getAjaxAction('selecionarBandeira', array('invoiceid' => $invoiceId)));
            $btnCielo = $buttonCielo->generate();
        }
        
        return $btnCielo;
    }  
    
    public function infoTitulo($id)
    {
        $MIOLO = MIOLO::getInstance();
        
        $module = 'finance';
        $MIOLO->uses('classes/finance.class.php', $module);
        
        $businessInvoice = $MIOLO->getBusiness($module, 'BusInvoice');
        
        $invoiceId = $id;
        
        $businessReceivableInvoice = $MIOLO->getBusiness($module, 'BusReceivableInvoice');

        $data = $businessReceivableInvoice->getReceivableInvoice($invoiceId);
        $data->invoiceId = $invoiceId;

        //
        // Show the entries for this titles
        //
        // BEGIN GRID
        $grdReceivableInvoice = $MIOLO->getUI()->getGrid($module, 'GrdReceivableInvoiceConsult', $data);
        $grdReceivableInvoice->setTitle(_M('Lançamentos para título @1', $module, $invoiceId));
        $businessEntry        = $MIOLO->getBusiness($module, 'BusEntry');
        $entryData            = $businessEntry->listEntryData($data->invoiceId, 4);
        
        $isAccounted = $businessInvoice->verifyAccountedEntriesForInvoice($invoiceId);
        
        // Information to return to that page
        $_opts = array('invoiceId'=>$this->getFormValue('invoiceId', $data->invoiceId) ? $this->getFormValue('invoiceId', $data->invoiceId) : MIOLO::_request('invoiceId', 'GET'),
                       'event'=>'btnSearch_click'
                      );        
        $goto = $MIOLO->getActionURL($module, 'main', null, $_opts);
        
        // First button - INSERT ENTRY
        $opts = array('invoiceId'=>$this->getFormValue('invoiceId', $data->invoiceId) ? $this->getFormValue('invoiceId', $data->invoiceId) : MIOLO::_request('invoiceId', 'GET'), 
                      'event'=>'tbBtnNew:click',
                      'function'=>'insert',
                      'costCenterId'=>$data->costCenterId,
                      'goto'=>urlencode($goto)
                     );
        $buttons[] = new MLink('insertEntry', _M('Inserir lançamento', $module), $MIOLO->getActionURL($module, 'main:register:entry', null, $opts));                
        $buttons[] = new MLabel(' - '); 
 
        // BEGIN BUTTONS
        if (MIOLO::_request('updated', 'GET') == true)
        {
            $fields[] = new MText('updateNominalValue', _M('Valor nominal atualizado', $module));
        }
        else
        {
            $opts = array('invoiceId'=>$this->getFormValue('invoiceId', $data->invoiceId) ? $this->getFormValue('invoiceId', $data->invoiceId) : MIOLO::_request('invoiceId', 'GET'), 'returnBankCode'=>$this->getFormValue('returnBankCode', $data->returnBankCode) ? $this->getFormValue('returnBankCode', $data->returnBankCode) : MIOLO::_request('returnBankCode', 'GET'), 'event'=>'updateNominalValue_click');
            $buttons[] = new MLink('updateNominalValue', _M('Atualizar valor nominal', $module), $MIOLO->getActionURL($module, "main:report:receivableInvoiceConsult", null, $opts));
            $buttons[] = new MLabel(' - ');
        }

        if ($isAccounted == false)
        {
            $opts = array("invoiceId"=>$this->getFormValue('invoiceId', $data->invoiceId) ? $this->getFormValue('invoiceId', $data->invoiceId) : MIOLO::_request('invoiceId', 'GET'), 'goto'=>urlencode($goto));
            $closeInvoiceURL = $MIOLO->getActionURL($module, "main:process:closeInvoice", null, $opts);

            $buttons[] = new MLink('closeInvoice', _M('Baixar título', $module), $closeInvoiceURL);
            $buttons[] = new MLabel(' - ');        
        }

        $opts = array('invoiceId'=>$this->getFormValue('invoiceId', $data->invoiceId) ? $this->getFormValue('invoiceId', $data->invoiceId) : MIOLO::_request('invoiceId', 'GET'), 
                      'function'=>'update',
                      'goto'=>str_replace("&amp;", "%26", urlencode($goto)));

        $buttons[] = new MLink('ChangeReceivableInvoice', _M('Alterar título', $module), $MIOLO->getActionURL($module, "main:register:invoice:receivableInvoice", null, $opts));
        $buttons[] = new MLabel(' - ');
        $buttons[] = new MLink('newConsultation', _M('Nova consulta', $module), $MIOLO->getActionURL($module, "main"));

        if( $access != 'true' )
        {
            $buttons[] = new MLabel(' - ');
            $buttons[] = new MLink('Back', _M('Voltar', $module), $MIOLO->getActionURL($module, 'main', null, array('personId'=>$data->personId, 'event'=>'btnSearch_click')));            
        }
        
        $divButtons = new MDiv('divButtons', $buttons, null, 'align="center"');
        
        $fields[]   = $divButtons;
        $fields[]   = new MSeparator('');
        // END BUTTONS -------

        // BEGIN BASE LABEL -------
        $field2[] = new MHiddenField('invoiceId', $this->getFormValue('invoiceId', $data->invoiceId));
        
        // Invoice
        $invoiceIdLabel = new MText('invoiceIdLabel', _M('Código do título', $module).':');
        $invoiceIdLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_CONTAINER_SIZE'));
        $invoiceId      = new MTextLabel('invoiceId', $data->invoiceId ? $data->invoiceId : $this->getFormValue('invoiceId', $data->invoiceId));
        $field2[]       = new MHContainer('hctInvoiceId', array($invoiceIdLabel, $invoiceId));
        
        $personData = $businessInvoice->getPersonDataForInvoice($data->invoiceId ? $data->invoiceId : $this->getFormValue('invoiceId', $data->invoiceId));
       
        // Course
        $courseLabel           = new MText('courseLabel', _M('Curso', $module).':');
        $courseLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_CONTAINER_SIZE'));
        if (strlen($data->courseId)>0)
        {
            $businessCourse          = $MIOLO->getBusiness('academic', 'BusCourse');
            $courseData              = $businessCourse->getCourse($this->getFormValue('courseId', $data->courseId), $this->getFormValue('courseVersion', $data->courseVersion));

            // Course
            $courseId                = new MText('courseId_', $this->getFormValue('courseId', $data->courseId));
            $courseName              = new MText('courseName_', $courseData->shortName);
            $courseTrace             = new MText('courseTrace', '-');

            // Course Version
            $courseVersion           = new MText('courseVersion_', $this->getFormValue('courseVersion',$data->courseVersion));
            $couseVersionDescription = new MText('couseVersionDescription_', _M('Versão', $module).':');
            $hctCourse               = new MHContainer('hctCourse', array($courseLabel, $courseId, $courseTrace, $courseName, $couseVersionDescription, $courseVersion));

        }
        else
        { 
            $courseData->shortName = _M('Não há curso relacionado', $module);
            $courseName = new MText('courseName_', $courseData->shortName);
            $hctCourse = new MHContainer('hctCourse', array($courseLabel, $courseName));
        }
       
        $field2[] = $hctCourse;        
       
        // Unit
        $unitLabel = new MText('unitLabel', _M('Unidade', $module).':');
        $unitLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_CONTAINER_SIZE'));
        if (strlen($data->unitId)>0)
        {
            $unitId          = new MText('unitId_', $this->getFormValue('unitId', $data->unitId));
            $unitTrace       = new MText('unitTrace', '-');
            $businessUnit    = $MIOLO->getBusiness('basic', 'BusUnit');
            $dataUnit        = $businessUnit->getUnit($this->getFormValue('unitId', $data->unitId));
            $unitDescription = new MText('unitDescription_', $dataUnit->description);
            $hctUnit         = new MHContainer('hctUnit', array($unitLabel, $unitId, $unitTrace, $unitDescription));
            $hctUnit->setShowLabel(true);
        }
        else
        {
            $dataUnit->description = _M('Não há unidade relacionada', $module);
            $unitDescription       = new MText('unitDescription_', $dataUnit->description);
            $hctUnit               = new MHContainer('hctUnit', array($unitLabel, $unitDescription));
        }
        $field2[]        = $hctUnit;
 
        // ParcelNumber
        $parcelNumberLabel = new MText('parcelNumberLabel', _M('Número da parcela', $module).':');
        $parcelNumberLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_CONTAINER_SIZE'));
        $parcelNumber      = new MText('parcelNumber_', $this->getFormValue('parcelNumber', $data->parcelNumber));
        $hctParcelNumber   = new MHContainer('hctParcelNumber_', array($parcelNumberLabel, $parcelNumber));
        $field2[]          = $hctParcelNumber;
        
        // EmissionDate
        $emissionDateLabel = new MText('emissionDateLabel', _M('Data de emissão', $module).':');
        $emissionDateLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_CONTAINER_SIZE'));
        $emissionDate      = new MText('emissionDate_', $this->getFormValue('emissionDate', $data->emissionDate));
        $hctEmissionDate   = new MHContainer('hctEmissionDate', array($emissionDateLabel, $emissionDate));
        $field2[]          = $hctEmissionDate;

        // MaturityDate
        $maturityDateLabel = new MText('maturityDateLabel', _M('Data de vencimento', $module).':');
        $maturityDateLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_CONTAINER_SIZE'));
        $maturityDate      = new MText('maturityDate_', $this->getFormValue('maturityDate', $data->maturityDate));
        $hctMaturityDate   = new MHContainer('hctMaturityDate', array($maturityDateLabel, $maturityDate));
        $field2[]          = $hctMaturityDate;
        
        // Value
        $valueLabel = new MText('valueLabel', _M('Valor', $module).':');
        $valueLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_CONTAINER_SIZE'));
        $value      = new MText('value_', SAGU::formatNumber($this->getFormValue('value', $data->value)));
        $hctValue   = new MHContainer('hctValue', array($valueLabel, $value));
        $field2[]   = $hctValue;

        // Automatic debit
        $automaticDebitLabel = new MText('automaticDebitLabel', _M('Débito automático', $module).':');
        $automaticDebitLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_CONTAINER_SIZE'));
        
        if($this->getFormValue('automaticDebit', $data->automaticDebit) == DB_TRUE)
        {
            $debito = _M('Sim');
        }
        else
        {
            $debito = _M('Não');
        }
        
        $automaticDebit      = new MText('automaticDebit_', $debito);
        $hctAutomaticDebit   = new MHContainer('hctAutomaticDebit', array($automaticDebitLabel, $automaticDebit));
        $field2[]            = $hctAutomaticDebit;

        $bgInvoice = new MBaseGroup('bgInvoice', _M('Informações do título', $module), $field2);
        $bgInvoice->setDisposition('vertical');

        $fs[] = $bgInvoice;
        
        foreach($entryData as $k=>$lancamento)
        {
            $entryData[$k][6] = 'R$ '.$entryData[$k][6];
            
            $desc = explode('<br>',$entryData[$k][3]);
            $entryData[$k][3] = $desc[0];
        }
        
        $columns[3] = _M('Operação');
        $columns[4] = _M('Data');
        $columns[6] = _M('Valor');

        $list[] = $this->listView($id, $title, $columns, $entryData, $options);
        
        $fs[] = new MBaseGroup('lancamentos', _M('Lançamentos', $module), $list);
        
        $div = new Mdiv('divInfo',$fs);
        
        return $div;
        
    }
    
    public function visualizarTitulo($args)
    {
        $MIOLO = MIOLO::getInstance();
        $args = MUtil::getAjaxActionArgs();
        
        $fields = array();
        $botoes = array();
        
        $botoes[] = new MButton('botaoFechar', _M('Fechar', $this->modulo), "dijit.byId('dialogoInfo').hide();");
        
        $businessReceivableInvoice = $MIOLO->getBusiness('finance', 'BusReceivableInvoice');
        $data = $businessReceivableInvoice->getReceivableInvoice($args->id);
        
        $fields[] = MUtil::centralizedDiv($botoes);
        
        $fields[] = $this->infoTitulo($args->id);

        $dialog = new MDialog('dialogoInfo', _M('Informação do títulos', $this->modulo), $fields);
        $dialog->show();
    }
    
    public function efetuarPagamentoCielo()
    {
        MDialog::close('dialogoCartaoCredito');
        
        $args = MUtil::getAjaxActionArgs();
        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('classes/cielo.class.php', $module);
        $MIOLO->uses('forms/FrmCreditCardPopup.class.php', 'finance');
        
        $busSpecie = $MIOLO->getBusiness('finance', 'BusSpecies');
        
        $finCieloPedido = new FinCieloPedido();
        
        $data = $this->getAjaxData();
        
        $businessReceivableInvoice = $MIOLO->getBusiness('finance', 'BusReceivableInvoice');
        
        $specie = $busSpecie->getSpecies($args->speciesId);
        $specie instanceof FinSpecies;
        $value = $businessReceivableInvoice->getInvoiceBalanceWithPolicies($args->invoiceid);

         // Valor
        $finCieloPedido->dadosPedidoValor = FinCieloPedido::formatarValorCielo($value);

        // Código da bandeira
        $finCieloPedido->formaPagamentoBandeira = strtolower($specie->bandeira);

        // Num parcelas
        $finCieloPedido->formaPagamentoParcelas = $data->numParcelas;

        // Num cartao
        $finCieloPedido->dadosPortadorNumero = $data->numero;

        // Validade cartao
        $finCieloPedido->dadosPortadorVal = $data->validadeAno . $data->validadeMes;

        // Codigo seguranca cartao
        $finCieloPedido->dadosPortadorCodSeg = $args->codSeguranca;
        if ( $args->codSeguranca == NULL || $args->codSeguranca == '' )
        {
            $finCieloPedido->dadosPortadorInd = "0";
        }
        else
        {
            $finCieloPedido->dadosPortadorInd = "1";
        }        

        // Num Pedido
        $finCieloPedido->dadosPedidoNumero = rand(1000000, 9999999);

        $objRespostaTid = $finCieloPedido->requisicaoTid();

        $finCieloPedido->tid = $objRespostaTid->tid;
        $finCieloPedido->pan = $objRespostaTid->pan;
        $finCieloPedido->status = $objRespostaTid->status;

        $objRespostaAutorizacao = $finCieloPedido->requisicaoAutorizacaoPortador();

        $finCieloPedido->tid = $objRespostaAutorizacao->tid;
        $finCieloPedido->pan = $objRespostaAutorizacao->pan;
        $finCieloPedido->status = $objRespostaAutorizacao->status;

        $urlAutenticacao = 'url-autenticacao';
        $finCieloPedido->urlAutenticacao = $objRespostaAutorizacao->$urlAutenticacao;

        // Serializa Pedido e guarda na SESSION
        $strPedido = $finCieloPedido->toString();
        $finCieloPedido->append($strPedido);
        $erro = utf8_encode($finCieloPedido->erro);

        // Consulta situação da transação
        $objRespostaConsulta = $finCieloPedido->requisicaoConsulta();

        // Atualiza status
        $finCieloPedido->status = $objRespostaConsulta->status;

        if ( $finCieloPedido->status == FinCieloPedido::STATUS_AUTORIZADO )
        {
            // Regitra transação
            $cielotransactionid = $finCieloPedido->registrarTransacao(utf8_encode($objRespostaAutorizacao->asXML()));
            
            //Comentário do lançamento
            if( $specie->modalidade = 'C' )
            {
                $modalidade = 'crédito, parcelamento em '.$data->numParcelas.'x.';
            }
            else
            {
                $modalidade = 'débito.';
            }
            $comments = _M('Pago no portal do aluno: Cartão @1. Modalidade @2', $module, strtoupper($specie->bandeira), $modalidade);
            $ok = $businessReceivableInvoice->closeInvoiceByCielo($cielotransactionid, $args->invoiceid, $value, date(SAGU::getParameter('BASIC', 'MASK_DATE_PHP')), $comments);
            
            $fields[] = new MLabel('Pagamento efetuado com sucesso.');
            $botoes[] = new MButton('botaoFechar', _M('Fechar', $this->modulo), "location.reload();");
        }
        else
        {
            $fields[] = new MLabel('O pagamento não foi autorizado. Motivo: '.$erro);
            $botoes[] = new MButton('botaoFechar', _M('Fechar', $this->modulo), "dijit.byId('dialogoMensagem').hide();");
        }

        $fields[] = MUtil::centralizedDiv($botoes);

        $dialog = new MDialog('dialogoMensagem', _M('Informação do títulos', $this->modulo), $fields);
        $dialog->show();
    }
    
    public function selecionarBandeira($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $busSpecie = $MIOLO->getBusiness('finance', 'BusSpecies');
        $ui = $MIOLO->getUI();
        
        //Busca todas espécies de Cartão de crédito
        $filters = new stdClass();
        $filters->speciesTypeId = SAGU::getParameter('FINANCE', 'CREDIT_CARD_SPECIESTYPE_ID');
        $filters->isEnabled = DB_TRUE;
        $species = $busSpecie->searchSpecies($filters);
        
        parse_str($args, $data);
        
        $ok = false;
        foreach( $species as $cod=>$specie )
        {
            $specie = $busSpecie->getSpecies($specie[0]);
            //Visa
            if( $specie->bandeira == 'visa' )
            {
                $ok = true;
                $vtcBandeiras[] = new MImageButton('', $this->alt, MUtil::getAjaxAction('pagamentoCielo', array('speciesId' => $specie->speciesId, 'invoiceid' => $data['invoiceid'], 'bandeira' => $specie->bandeira)), $ui->getImageTheme($module, $specie->bandeira.'.png'));
                $vtcBandeiras[] = new MText('', $specie->description);
                $vtcBandeiras[] = new MSpacer();
            }
            //Mastercard
            if( $specie->bandeira == 'mastercard' )
            {
                $ok = true;
                $vtcBandeiras[] = new MImageButton('', $this->alt, MUtil::getAjaxAction('pagamentoCielo', array('speciesId' => $specie->speciesId, 'invoiceid' => $data['invoiceid'], 'bandeira' => $specie->bandeira)), $ui->getImageTheme($module, $specie->bandeira.'.png'));
                $vtcBandeiras[] = new MText('', $specie->description);
                $vtcBandeiras[] = new MSpacer();
            }
            //Elo
            if( $specie->bandeira == 'elo' )
            {
                $ok = true;
                $vtcBandeiras[] = new MImageButton('', $this->alt, MUtil::getAjaxAction('pagamentoCielo', array('speciesId' => $specie->speciesId, 'invoiceid' => $data['invoiceid'], 'bandeira' => $specie->bandeira)), $ui->getImageTheme($module, $specie->bandeira.'.png'));
                $vtcBandeiras[] = new MText('', $specie->description);
                $vtcBandeiras[] = new MSpacer();
            }
        }
        
        //Caso nenhuma espécie de cartão encontrada
        if( !$ok )
        {
            $vtcBandeiras[] = MMessageInformation::getStaticMessage('msgInfo', _M('Pagamento por cartão de crédito indisponível no momento. Tente novamente mais tarde'), MMessage::TYPE_INFORMATION);
        }
        
        $dialog = new MDialog('dialogoCartaoCredito', _M('Selecione seu cartão', $this->modulo), $vtcBandeiras);
        $dialog->addStyle('text-align', 'center');
        $dialog->show();
    }
    
    public function pagamentoCielo()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $args = MUtil::getAjaxActionArgs();
        
        $busSpecies = $MIOLO->getBusiness('finance', 'BusSpecies');
        $busInvoice = $MIOLO->getBusiness('finance', 'BusInvoice');
        $specie = $busSpecies->getSpecies($args->speciesId);
        $specie instanceof FinSpecies;

        // Dados do cartão
        $form = new MFormContainer('frmCreditCard', self::obterCamposCartaoDeCredito());
        $bg = new MBaseGroup('bgCreditCard', _M('Informe os dados do cartão'), array($form));
        $fields[] = new MDiv('divCreditCard', array($bg));

        if ( $specie->modalidade == 'D' )
        {
            $parcelas[0][0] = 1;
            $parcelas[0][1] = 'Débito';

            $parcelasLabel = new MText('parcelasLabel', _M('Número de parcelas', $module) . ':');
            $parcelasLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_CONTAINER_SIZE'));
            $parcelas = new MSelection('numParcelas', 1, null, $parcelas, false, _M('Selecione o número de parcelas desejadas.'));
            $parcelas->setReadOnly(TRUE);
            $parc[] = new MHContainer('parcelasHC', array($parcelasLabel, $parcelas));
        }
        else
        {
            $valor = $busInvoice->getInvoiceBalanceWithPolicies($args->invoiceid);
            
            // Número de parcelas
            for ( $i = 1; $i <= $specie->numParcelas; $i++ )
            {
                $valorParcela = $valor / $i;

                $parcelas[$i][0] = $i;
                $parcelas[$i][1] = $i == 1 ? _M('Crédito à vista') : $i . 'x - R$ ' . number_format($valorParcela, 2, ',', '.');
            }

            $parcelasLabel = new MText('parcelasLabel', _M('Número de parcelas', $module) . ':');
            $parcelasLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_CONTAINER_SIZE'));
            $parcelas = new MSelection('numParcelas', 1, null, $parcelas, false, _M('Selecione o número de parcelas desejadas.'));
            $parc[] = new MHContainer('parcelasHC', array($parcelasLabel, $parcelas));
        }

        $fields[] = new MBaseGroup('bgCondicoes', _M('Condições de pagamento'), $parc);

        $botoes[] = new MButton('botaoSair', _M('Confirmar pagamento', $this->modulo), ':efetuarPagamentoCielo');
        $botoes[] = new MButton('botaoCancelar', _M('Cancelar', $this->modulo), "dijit.byId('dialogoCartaoCredito').hide();");
        $fields[] = MUtil::centralizedDiv($botoes);
        
        $fields[] = $fldInvoice = new MTextField('invoiceid', $args->invoiceid);
        $fldInvoice->addStyle('display', 'none');
        $fields[] = $fldSpecie = new MTextField('speciesId', $args->speciesId);
        $fldSpecie->addStyle('display', 'none');
        
        $dialog = new MDialog('dialogoCartaoCredito', _M('Dados do cartão de crédito', $this->modulo), $fields);
        $dialog->show();
    }
    
    
    public static function obterCamposCartaoDeCredito()
    {
        $args = MUtil::getAjaxActionArgs();
        
        $MIOLO = MIOLO::getInstance();
        $ui = $MIOLO->getUI();
        $module = MIOLO::getCurrentModule();
        $flds[] =  new MImage('img_bandeira', null, $ui->getImageTheme($module, $args->bandeira.'.png'));
        
        // Numero
        $numeroLabel = new MText('numeroLabel', _M('Número', $module) . ':');
        $numeroLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_CONTAINER_SIZE'));
        $numero = new MTextField('numero', '', null, 20, _M('Informe o número do cartão'));
        
        $length    = SAGU::getParameter('FINANCE', 'CREDIT_CARD_LENGTH');
        $ano       = date('Y');
        $anoFim    = date('Y') + 20;
        $anoSeculo = substr($ano, 0, 2);
        
        $numero->addAttribute('onkeyup', "
            if ( this.value.length == parseInt($length) )
            {
                var charSeparator = this.value.indexOf('=');
                var num = this.value.substr(1,charSeparator-1);
                var ano = this.value.substr(charSeparator+1, 2);
                var mes = this.value.substr(charSeparator+3, 2);
                
                ano = '$anoSeculo' + ano;

                this.value = num;
                document.getElementById('validadeAno').value = ano;
                document.getElementById('validadeMes').value = mes;

                document.getElementById('codSeguranca').value = '';
                document.getElementById('codSeguranca').focus();
            }
            else
            {            
                if ( this.value.indexOf(':') > -1 )
                {                    
                    alert('Erro na leitura do cartão. Por favor tente novamente!');
                    document.getElementById('validadeAno').value = '';
                    document.getElementById('validadeMes').value = '';
                    this.value = null;
                }
            }
            
            if ( this.value.substr(0, 1) != 'ç' )
            {
                if ( this.value.match( /[^\d]/g ) )
                {
                    this.value = this.value.replace( /[^\d]/g, '' );
                }
            }
        ");
        
        $flds[] = new MHContainer('numeroHC', array($numeroLabel, $numero, $numHidden));
        
        // Validade
        $validadeLabel = new MText('validadeLabel', _M('Validade', $module) . ':');
        $validadeLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_CONTAINER_SIZE'));
        
        for ( $i = $ano; $i < $anoFim; $i++ )
        {
            $anos[$i] = $i;
        }        
        $validadeAno = new MSelection('validadeAno', date('Y'), null, null, null, _M('ano'));
        $validadeAno->options = $anos;
        $validadeAno->addAttribute('style', 'width:80px');
        
        $meses['01'] = '01';
        $meses['02'] = '02';
        $meses['03'] = '03';
        $meses['04'] = '04';
        $meses['05'] = '05';
        $meses['06'] = '06';
        $meses['07'] = '07';
        $meses['08'] = '08';
        $meses['09'] = '09';
        $meses['10'] = '10';
        $meses['11'] = '11';
        $meses['12'] = '12';
        $validadeMes = new MSelection('validadeMes', date('m'), null, null, null, _M('mês'));
        $validadeMes->options = $meses;
        $validadeMes->addAttribute('style', 'width:50px');
        
        $flds[] = new MHContainer('validadeHC', array($validadeLabel, $validadeMes, $validadeAno));
        
        // Cód. segurança
        $segurancaLabel = new MText('segurancaLabel', _M('Cód segurança', $module) . ':');
        $segurancaLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_CONTAINER_SIZE'));
        $seguranca = new MIntegerField('codSeguranca', '', null, 3, _M('Informe o código de segurança do cartão'));
        
        $seguranca->addAttribute('onkeyup', "
            if ( this.value.length < 3 )
            {
                if ( this.value.substr(0, 1) != 'ç' )
                {
                    if ( this.value.match( /[^\d]/g ) )
                    {
                        this.value = this.value.replace( /[^\d]/g, '' );
                    }
                }
                else
                {
                    var numero = document.getElementById('numero');
                    numero.value = this.value;
                    numero.onkeyup();
                }
            }
            else
            {
                this.value = this.value.substr(0, 3);
                
                if ( !parseInt(this.value) )
                {
                    this.value = '';
                }
            }
        ");
        
        $flds[] = new MHContainer('codSegurancaHC', array($segurancaLabel, $seguranca));
        
        return $flds;
    }
}

?>
