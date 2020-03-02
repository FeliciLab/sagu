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
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 21/10/2008
 *
 **/
class FrmPersonConfig extends GSubForm
{
    public $MIOLO;
    public $module;
    public $action;
    public $business;
    public $grid;
    public $function;
    public $busAthenticate;
    public $busLibraryUnit;


    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->action   = MIOLO::getCurrentAction();
        $this->function = MIOLO::_REQUEST('function');
        $this->business = $this->MIOLO->getBusiness( $this->module, 'BusPersonConfig');
        $this->busAthenticate = $this->MIOLO->getBusiness( $this->module, 'BusAuthenticate');
        $this->busLibraryUnit = $this->MIOLO->getBusiness( $this->module, 'BusLibraryUnit');
        parent::__construct( _M('Configurações pessoais', $this->module) );
        
    }


    public function createFields()
    {
    	//Mensagem a ser mostrada no topo da tela
        $fields[] = new MDiv('', LABEL_PERSON_CONFIG );
        $data = $this->business->getParseValuesPersonConfig( BusinessGnuteca3BusAuthenticate::getUserCode(), true);
        $user_config = explode ("\n", USER_CONFIG);
        
        foreach ( (array)$user_config as $v )
        {
            $field = null;
        	//Separa a legenda do parâmetro
            list($var1, $label) = explode('|', $v);
            //Parâmetro vai em $var e valor em $perm
            list($var, $perm) = explode('=', $var1);
            $id = strtoupper(trim($var)); //id do campo
            $perm = strtoupper(trim($perm)); //permissão

            if (!($var) || !($perm))
            {
                continue;
            }

            $field = null;

            switch ($id)
            {
                case 'USER_SEND_DELAYED_LOAN':
                    $field[] = $sel = new GSelection($id, $data->$id, _M('Enviar e-mail de atraso', $this->module), GUtil::listYesNo(), NULL, NULL, NULL, TRUE);

                    break;

                case 'USER_DELAYED_LOAN':
                    $field[] = new MLabel( _M('Empréstimo atrasado', $this->module) );
                    $field[] = $lblQuant = new MDiv('',_M('Quantidade', $this->module) . ':');
                    $field[] = $Quant = new MTextField('quantidade', $data->quantidade, '', FIELD_ID_SIZE);
                    $Quant->setReadOnly( ($perm == 'W') ? FALSE : TRUE );
                    $field[] = $lblPer = new MDiv('',_M('Período', $this->module) . ':');
                    $field[] = $Per = new MTextField('periodo', $data->periodo, '', FIELD_ID_SIZE);
                    $Per->setReadOnly( ($perm == 'W') ? FALSE : TRUE );

	                break;

                case 'USER_SEND_NOTIFY_AQUISITION':
                    $field[] =  $sel = new GSelection($id, $data->$id, _M('Enviar e-mail de novas aquisições', $this->module), GUtil::listYesNo(), NULL, NULL, NULL, TRUE);

                    break;

                case 'USER_NOTIFY_AQUISITION':
                    $field[] = $sel = new MTextField($id, $data->$id, _M('Notificar aquisições', $this->module), FIELD_DESCRIPTION_SIZE);

                    break;

                case 'USER_SEND_DAYS_BEFORE_EXPIRED':
                    $field[] =  $sel = new GSelection($id, $data->$id, _M('Enviar e-mail antes do vencimento', $this->module), GUtil::listYesNo(), NULL, NULL, NULL, TRUE);
                    break;

                case 'USER_DAYS_BEFORE_EXPIRED':
                    $field[] = $sel = new MTextField($id, $data->$id, _M('Dias antes do vencimento', $this->module), FIELD_DESCRIPTION_SIZE);

                    break;

                case 'CONFIGURE_RECEIPT_LOAN':
                    $opts = $this->business->listReceiptConfigure();
                    $field[] =  $sel = new GSelection($id, $data->$id,_M('Comprovantes de empréstimo', $this->module), $opts, NULL, NULL, NULL, TRUE);

                    break;

                case 'CONFIGURE_RECEIPT_RETURN':
                    $opts = $this->business->listReceiptConfigure();
                    $field[] = $sel = new GSelection($id, $data->$id, _M('Comprovantes de devolução', $this->module), $opts, NULL, NULL, NULL, TRUE);

                    break;

                case 'USER_SEND_RECEIPT_RENEW_WEB':
                    $field[] = $sel = new GSelection($id, $data->$id, _M('Enviar comprovante de renovação web', $this->module), GUtil::listYesNo(), NULL, NULL, NULL, TRUE);

                    break;
            }

            $field[] = $img = new MImage('imgHelp', null, GUtil::getImageTheme('help-16x16.png') );
            $img->addAttribute('title',$label );
            $fld = new GContainer('', $field );

            switch ($perm)
            {
                case 'R':
                    $sel->setReadOnly(true);
                    $fields[] = $fld;
                    break;
                case 'W':
                    $fields[] = $fld;
                    break;
                case 'I':
                default:
            }
        }

        if ( strpos(strtoupper(USER_CONFIG), '=W') )
        {
            $btnSave = new MButton('btnSave', _M('Salvar',$this->module), ':savePersonConfig' , GUtil::getImageTheme( 'save-16x16.png' ) );
            $fields[] = new MDiv('divSave', $btnSave);
        }
        
        $this->setFields( GUtil::alinhaForm($fields) );
    }

    public function savePersonConfig($args)
    {
    	$configData = (object)$_REQUEST;
        $configData->USER_DELAYED_LOAN = $configData->quantidade . ';' . $configData->periodo;
        //Verifica se os campos digitados são numéricos
        if ( !(is_numeric($configData->USER_DAYS_BEFORE_EXPIRED)) || !(is_numeric($configData->USER_NOTIFY_AQUISITION)) || !(is_numeric($configData->quantidade)) || !(is_numeric($configData->periodo)) )
        {
            GForm::error(_M('O conteúdo do campo precisa ser numérico!', $this->module) );
            return false;
        }
       
        //Verifica se o valor esta entre 0 e configurado pelo sistema dias
        if ( ($configData->USER_NOTIFY_AQUISITION < 0 || $configData->USER_NOTIFY_AQUISITION > USER_NOTIFY_AQUISITION) )
        {
            GForm::error(_M('Por favor insira um valor entre 0 e @1 dias no campo notificar aquisições', $this->module, USER_NOTIFY_AQUISITION) );
        	return false;
        }
        
        //Verifica se o valor está entre 0 e o limite definido na preferência LIMIT_DAYS_BEFORE_EXPIRED
        if ( ($configData->USER_DAYS_BEFORE_EXPIRED < 0 || $configData->USER_DAYS_BEFORE_EXPIRED > LIMIT_DAYS_BEFORE_EXPIRED) )
        {
            GForm::error(_M('Por favor digite um valor entre 0 e @1 dias no campo Dias antes do vencimento', $this->module, LIMIT_DAYS_BEFORE_EXPIRED) );
            return false;
        }
                
        $generalPreferences = explode(';',USER_DELAYED_LOAN);
        $quant = $generalPreferences[0];
        $period = $generalPreferences[1];
        
        //Verifica se valor de período esta entre 0 e o especidificado pelo sistema
        if ( $configData->periodo == 0 || $configData->periodo > $period )
        {
        	GForm::error(_M('Por favor entre com um valor entre 1 e @1 - Período', $this->module, $period));
        	return false;
        	
        }
        //Verifica se valor de quantidade esta entre 0 e o especificado pelo sistema
        if ( $configData->quantidade == 0 || $configData->quantidade > $quant )
        {
            GForm::error(_M('Por favor entre com um valor entre 1 e @1 - Quantidade', $this->module, $quant));
        	return false;
        }
        else 
        {
            //para compatibilidade com miolo novo
            $MIOLO		= MIOLO::getInstance();
            $form       = $MIOLO->page->getFormId();
            $postBack   = $form . '__ISPOSTBACK';
            $submit     = $form . '__FORMSUBMIT';
            $event      = $form . 'EVENTTARGETVALUE';

            //Limpa variaveis para não salvar na base
            $configData->quantidade                      = NULL;
            $configData->periodo                         = NULL;
            $configData->module                          = NULL;
            $configData->action                          = NULL;
            $configData->cpaint_response_type            = NULL;
            $configData->$postBack                       = NULL;
            $configData->$submit                         = NULL;
            $configData->$event                          = NULL;
            $configData->__ISAJAXCALL                    = NULL;
            $configData->__FORMSUBMIT                    = NULL;
            $configData->PHPSESSID                       = NULL;
            $configData->_position                       = NULL;
            $configData->GRepetitiveField          = NULL;

            //Unset readonly fields for not save on gtcPersonConfig
            foreach ($configData as $key => $val)
            {
            	if ( $this->fields[$key]->readonly )
            	{
            		unset($configData->$key);
            	}
            }

            $this->business->personId   = BusinessGnuteca3BusAuthenticate::getUserCode();
            //ajusta configurações do recibo de empréstimo
			if ( $value = $configData->CONFIGURE_RECEIPT_LOAN )
            {  
                $value = $this->business->getPrintAndSendReceiptConfigure($value);
                $configData->MARK_PRINT_RECEIPT_LOAN = $value->print;
                $configData->MARK_SEND_LOAN_MAIL_RECEIPT = $value->send;
                unset($configData->CONFIGURE_RECEIPT_LOAN);
            }
			//ajusta configurações do recibo de devolução
			if ( $value = $configData->CONFIGURE_RECEIPT_RETURN )
			{
				$value = $this->business->getPrintAndSendReceiptConfigure($value);
			    $configData->MARK_PRINT_RECEIPT_RETURN = $value->print;
                $configData->MARK_SEND_RETURN_MAIL_RECEIPT = $value->send;
			    unset($configData->CONFIGURE_RECEIPT_RETURN);
             }
            
            $this->business->configData = $configData;
            $this->business->insertPersonConfig();
            GForm::information( MSG_RECORD_UPDATED );
        }
    }
}
?>
