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
 * InterchangeSearch form
 *
 * @author Moises Heberle [moises@solis.coop.br]
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
 * Class created on 20/02/2009
 *
 **/
class FrmInterchangeSearch extends GForm
{
	public $MIOLO;
	public $module;
	public $busExemplaryControl;
	public $busInterchange;
	public $busInterchangeItem;
	public $busInterchangeType;
	public $busInterchangeStatus;
    public $busSearchFormat;
	public $busSupplierTypeAndLocation;
    public $mail;

    public function __construct()
    {
    	$this->MIOLO  = MIOLO::getInstance();
    	$this->module = MIOLO::getCurrentModule();

        $this->MIOLO->getClass($this->module, 'GPDF');
        $this->MIOLO->getClass($this->module, 'report/rptInterchangeLetterSend');
        $this->MIOLO->getClass($this->module, 'GSendMail');
        $this->mail = new GSendMail();

        $this->busExemplaryControl        = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busInterchangeItem         = $this->MIOLO->getBusiness($this->module, 'BusInterchangeItem');
    	$this->busInterchangeType         = $this->MIOLO->getBusiness($this->module, 'BusInterchangeType');
    	$this->busInterchangeStatus       = $this->MIOLO->getBusiness($this->module, 'BusInterchangeStatus');
    	$this->busSearchFormat            = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
    	$this->busSupplierTypeAndLocation = $this->MIOLO->getBusiness($this->module, 'BusSupplierTypeAndLocation');

        $this->setAllFunctions('Interchange', array('interchangeIdS', 'supplierIdS'), array('interchangeId'));
        $this->busInterchange = $this->business; //define nome BusInterchange para padronizacao

        parent::__construct();
    }


    public function mainFields()
    {
        $defaultTypeId  = ($v = MIOLO::_REQUEST('interchangeTypeId')) ? $v : 1;
        $fields[]       = new MTextField('interchangeIdS', NULL, _M('Código', $this->module), FIELD_ID_SIZE);
        $fields[]       = $type = new GSelection('typeS', $defaultType, _M('Tipo', $this->module), $this->business->listTypes(), null, null, null, FALSE);
        $type->addAttribute('onchange', GUtil::getAjax('changeType'));
        
        $fields[] = $supplier =  new MDiv('divSupplier', $this->getSupplierField($defaultType));
        $supplier->addStyle('float','left');

        $lblDate        = new MLabel(_M('Data', $this->module) . ':');
        $beginDateS     = new MCalendarField('beginDateS', $this->beginDateS->value, null, FIELD_DATE_SIZE);
        $endDateS       = new MCalendarField('endDateS', $this->endDateS->value, null, FIELD_DATE_SIZE);
        $fields[]       = new GContainer('hctDates', array($lblDate, $beginDateS, $endDateS));

        $fields[]       = $interchangeTypeId = new GSelection('interchangeTypeIdS', NULL, _M('Tipo de permuta', $this->module), $this->busInterchangeType->listInterchangeType(), null, null, null, FALSE);
        $interchangeTypeId->addAttribute('onchange', "javascript:".GUtil::getAjax('changeStatus'));
        $interchangeTypeId->addAttribute('onkeyup', "javascript:".GUtil::getAjax('changeStatus'));

        $fields[]       = new GContainer('status', array($this->getInterchangeStatusField($defaultTypeId)));
        
        $this->setFields($fields);

        $validators[] = new MIntegerValidator('interchangeIdS');
        
        $this->setValidators($validators);
    }


    public function changeStatus($sender)
    {
        $this->setResponse($this->getInterchangeStatusField($sender->interchangeTypeIdS), 'status');
        $this->jsSetFocus('interchangeTypeIdS');
    }


    public function getInterchangeStatusField($interchangeTypeId)
    {
    	$lblES = new MLabel( _M('Estado', $this->module) . ':' );
    	$lblES->setWidth(FIELD_LABEL_SIZE);
        $interchangeStatusId = new GSelection('interchangeStatusIdS', null, null, $this->busInterchangeStatus->listInterchangeStatus($interchangeTypeId));
        $hct = new GContainer('hctInterchangeStatus', array($lblES, $interchangeStatusId));
        $hct->addStyle('width', '100%');
        return $hct;
    }


    public function changeType($sender)
    {
        $fields[] = $this->getSupplierField($sender->typeS);
        $this->setResponse($fields, 'divSupplier');
        $this->jsSetFocus('typeS');
    }


    public function getSupplierField($type = NULL)
    {
        $lbl = new MLabel (_M('Fornecedor:',$this->module));
        $lbl->width = FIELD_LABEL_SIZE;
        $supplierIdS = new GLookupTextField ('supplierIdS','','',FIELD_LOOKUPFIELD_SIZE);
        $supplierIdS->setContext($this->module, $this->module, 'supplierType', 'filler', 'supplierIdS,supplierIdDescriptionS', '', true);
        $supplierIdS->baseModule = 'gnuteca3';
        $supplierIdDescriptionS = new MTextField ('supplierIdDescriptionS','',null, FIELD_DESCRIPTION_LOOKUP_SIZE);
        $supplierIdDescriptionS->setReadOnly(true);
        $supplierIdContainerS = new GContainer('supplierIdContainerS', array ($lbl, $supplierIdS, $supplierIdDescriptionS,));
        return $supplierIdContainerS;
    }

    /**
     * Gera o formulário de geração de cartas
     */
    public function generateLetterSend()
    {
    	$this->busInterchange->interchangeIdS = MIOLO::_REQUEST('interchangeId');
    	$result = $this->busInterchange->searchInterchange(TRUE);
    	$args->supplier                = $result[0];
    	$args->supplierTypeAndLocation = $this->busSupplierTypeAndLocation->getSupplierTypeAndLocationValueForm($args->supplier->supplierId, $args->supplier->type);

    	$report  = new rptInterchangeLetterSend($args);
        $content = strtr($report->readModelFile(), array('$MATERIALS'=> str_replace("\n", "<br>", $this->getFormatedMaterials()), ));

    	$fields[] = $editor = new MEditor('reportContent', $content);
        $editor->disableElementsPath(); //desabilita linha inferior com caminhos dos elementos
    	$fields[] = new MSeparator();
        $fields[] = new MHiddenField('interchangeId');

        $btn[] = new MButton('btnGenerate', _M('Gerar PDF', $this->module), GUtil::getAjax('btnLetterSend_generate'), GUtil::getImageTheme('document-16x16.png'));
        $btn[] = GForm::getCloseButton();
        $fields[] = new MDiv('btns', $btn) ;

        $this->injectContent($fields, false, _M('Gerar envio da carta', $this->module) );
    }

    public function btnLetterSend_generate($args)
    {
    	if (!$args->reportContent)
    	{
            GForm::error( _M('É necessário ter algum conteúdo para gerar a carta.', $this->module) );
            return false;
    	}
        
        $this->business->getInterchange( MIOLO::_REQUEST('interchangeId') );
        $supplier = $this->busSupplierTypeAndLocation->getSupplierTypeAndLocationValueForm( $this->business->supplierId, $this->business->type );

    	if (!$errors)
    	{
            //Generate PDF
            $args->supplier = $supplier;
            $report         = new rptInterchangeLetterSend($args);
            
            if ($report->generate())
            {
                $url  = $report->getDownloadURL();
                $link = new MLink('lnkDownload', _M('Clique aqui', $this->module), $url, null, '_blank');
                $link->setGenerateOnClick(false);
                $link->setTarget(MLink::TARGET_BLANK);
                $args->message  = _M("Arquivo gerado. @1 para fazer o download", $this->module, $link->generate());
                $args->message .= '<br><br>' . _M('Mudar para o estado Carta Enviada?', $this->module);
                $args->changeStatusIdTo = INTERCHANGE_STATUS_LETTER_SENT;
                $this->confirm($args);
            }
            else
            {
                GForm::error( _M('Erro ao gerar relatório', $this->module) );
            }
    	}
    }

    public function btnLetterSend_changeStatus()
    {
        $interchangeId = MIOLO::_REQUEST('interchangeId');

        //Define estado como CARTA ENVIADA
        $this->business->getInterchange( $interchangeId );
        $this->business->interchangeStatusId = INTERCHANGE_STATUS_LETTER_SENT;
        $this->business->updateInterchange();

        $this->setResponse(null, 'divResponse');
    }



    /**
     * Enter description here...
     *
     * @param unknown_type $args
     */
    public function sendMail($args)
    {
        //Define se é para abrir janela de detalhes do e-mail
        if (MUtil::getBooleanValue(INTERCHANGE_MAIL_RECEIPT_AUTOSEND) == TRUE) //Auto send mail
        {
            $this->sendReceiptMail();
            return;
        }

        $fields[] = new MMultiLineField('content', INTERCHANGE_MAIL_RECEIPT_CONTENT, _M('Conteúdo', $this->module), NULL, 15, 50);;
        //$fields[] = new MHiddenField('enableEnter'); //Enable ENTER keyboard
        $fields[] = new MSeparator();
        $fields[] = new MDiv('divMessages');

        $btn[] = new MButton('btnSend', _M('Enviar', $this->module), ':sendReceiptMail', GUtil::getImageTheme('save-16x16.png'));
        $btn[] = GForm::getCloseButton();
        $fields[] = new MDiv('btns', $btn) ;

        $this->injectContent($fields, false, _M('Enviar e-mail de permuta', $this->module) );
    }

    public function sendReceiptMail($args = NULL)
    {
        $result = $this->mail->sendMailToSupplierInterchengeReceipt(MIOLO::_REQUEST('interchangeId'), $this->getFormatedMaterials(), $args->content);

        switch ($result)
        {
        	case 1:
                $this->error(_M('O e-mail do fornecedor não foi encontrado.', $this->module), $this->getGotoCurrentActionAndSearchEventUrl());
                return false;

        	case 2:
                $this->error(_M('Erro ao enviar e-mail para @1', $this->module, $this->mail->mailTo), $this->getGotoCurrentActionAndSearchEventUrl());
                return false;

        	default:
                $args->message  = _M('E-mail foi enviado com sucesso para fornecedor', $this->module) . " ({$this->mail->mailTo}).";
                $args->message .= '<br>' . _M('Gostaria de alterar o estado para @1?', $this->module, '<b>' . _M('Agradecido', $this->module) . '</b>');
                $args->changeStatusIdTo = INTERCHANGE_STATUS_GRATEFUL;
                $this->confirm($args);
                return true;

        }
    }

    public function getFormatedMaterials()
    {
        $id = $this->business->getInterchange( MIOLO::_REQUEST('interchangeId') );
        $this->busInterchangeItem->interchangeIdS = $id->interchangeId;
        $search = $this->busInterchangeItem->searchInterchangeItem(TRUE);

        $materials = array();
        
        if ($search)
        {
            foreach ($search as $s)
            {
                $materials[] = $this->busSearchFormat->getFormatedString($s->controlNumber, INTERCHANGE_SEARCH_FORMAT_ID);
            }
        }
        
        $materials = str_replace("<br/>", "\n", $materials);
        return strip_tags(implode("\n", $materials));
    }

    public function confirm($args = null)
    {
        $msg              = $args->message ? $args->message : _M('Realmente confirmar?', $this->module);
        $event            = 'confirm_finish';
        $changeStatusIdTo = $args->changeStatusIdTo ? $args->changeStatusIdTo : INTERCHANGE_STATUS_CONFIRMED;
        $interchangeId    = MIOLO::_REQUEST('interchangeId');

        $gotoYes = $this->MIOLO->getActionURL($this->module, $this->action, null, compact('event', 'interchangeId', 'changeStatusIdTo'));
        $this->question($msg, $gotoYes);
    }

    public function confirm_finish()
    {
        $interchangeId    = MIOLO::_REQUEST('interchangeId');
        $changeStatusIdTo = MIOLO::_REQUEST('changeStatusIdTo');
        $mensage = $changeStatusIdTo == INTERCHANGE_STATUS_LETTER_SENT ? _M('Carta enviada') : _M('Confirmado');

        if ($interchangeId && $changeStatusIdTo && $this->business->updateStatus($interchangeId, $changeStatusIdTo))
        {
            $this->information(_M('@1 com sucesso', $this->module, $mensage), $this->getGotoCurrentActionAndSearchEventUrl());
        }
        else
        {
            $this->error(_M(MSG_RECORD_ERROR, $this->module), null, null, null, true);
        }
    }
}
?>