<?php

/**
 * Formulário de autenticação do cliente LaSalle
 *
 * @author Luís Augusto Weber Mercado [luis_augusto@solis.com.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2012/06/20
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class frmLoginLaSalle extends MForm
{
    public $auth;

    public function frmLoginLaSalle()
    {
        $tituloLogin = SAGU::getUnitParameter('AVINST', 'TITULO_LOGIN_DA_AVALIACAO_INSTITUCIONAL', SAGU::getParameter('BASIC', 'DEFAULT_UNIT_ID'));

        $label = new MLabel($tituloLogin);
        $label->addStyle('font-weight', 'bold');
        $label->addStyle('font-size', '20px');
        $label->addStyle('margin-left', '12px');
        $label->addStyle('height', '60px');
        $label->addStyle('color', '#1F72BF');
        
        parent::__construct($label);
        
        MIOLO::getInstance()->page->addStyleURL(MIOLO::getInstance()->getThemeURL("frmloginlasalle.css"));
        
        $this->setClass('frmLoginLaSalle');
        
        $this->setIcon($this->manager->getUI()->getImage('admin', 'login-16x16.png'));
        
        if ( $this->page->isPostBack() )
        {
            $this->eventHandler();
        }
    }

    public function createFields()
    {
        $this->auth = $this->manager->auth;
        
        $this->page->addJsCode('
            function mascara(o,f){
                v_obj=o
                v_fun=f
                setTimeout("execmascara()",1)
            }

            function execmascara(){
                v_obj.value=v_fun(v_obj.value)
            }

           function cpf_mask(v){
                v=v.replace(/\D/g,"")                 //Remove tudo o que não é dígito
                v=v.replace(/(\d{3})(\d)/,"$1.$2")    //Coloca ponto entre o terceiro e o quarto dígitos
                v=v.replace(/(\d{3})(\d)/,"$1.$2")    //Coloca ponto entre o setimo e o oitava dígitos
                v=v.replace(/(\d{3})(\d)/,"$1-$2")   //Coloca ponto entre o decimoprimeiro e o decimosegundo dígitos
                return v
           }

           function returnNumbers(str)
           {
               var rs=\'\';

               for ( var i=0; i<str.length; i++)
               {
                   var chr = str.charAt(i);
                   if ( isDigit(chr) )
                   {
                       rs += chr;
                   }
               }

               return rs;
           }

           function isDigit(chr)
           {
               return "0123456789".indexOf(chr) != -1;
           }

           function MIOLO_Validate_Check_CPF(value)
           {
               var i;
               var c;

               var x = 0;
               var soma = 0;
               var dig1 = 0;
               var dig2 = 0;
               var texto = "";
               var numcpf1="";
               var numcpf = "";

               var numcpf = returnNumbers(value);

               if ( ( numcpf == \'00000000000\') ||
                    ( numcpf == \'11111111111\') ||
                    ( numcpf == \'22222222222\') ||
                    ( numcpf == \'33333333333\') ||
                    ( numcpf == \'44444444444\') ||
                    ( numcpf == \'55555555555\') ||
                    ( numcpf == \'66666666666\') ||
                    ( numcpf == \'77777777777\') ||
                    ( numcpf == \'88888888888\') ||
                    ( numcpf == \'99999999999\')  )
               {                    
                   return false;
               }

           /*    for (i = 0; i < value.length; i++) 
               {
                   c = value.substring(i,i+1);
                   if ( isDigit(c) )
                   {
                       numcpf = numcpf + c;
                   }
               }
           */    
               if ( numcpf.length != 11 ) 
               {
                   return false;
               }

               len = numcpf.length;x = len -1;

               for ( var i=0; i <= len - 3; i++ ) 
               {
                   y     = numcpf.substring(i,i+1);
                   soma  = soma + ( y * x);
                   x     = x - 1;
                   texto = texto + y;
               }

               dig1 = 11 - (soma % 11);
               if (dig1 == 10) 
               {
                   dig1 = 0 ;
               }

               if (dig1 == 11) 
               {
                   dig1 = 0 ;
               }

               numcpf1 = numcpf.substring(0,len - 2) + dig1 ;
               x = 11;soma = 0;
               for (var i=0; i <= len - 2; i++) 
               {
                   soma = soma + (numcpf1.substring(i,i+1) * x);
                   x = x - 1;
               }

               dig2 = 11 - (soma % 11);

               if (dig2 == 10)
               {
                   dig2 = 0;
               }
               if (dig2 == 11) 
               {
                   dig2 = 0;
               }
               if ( (dig1 + "" + dig2) == numcpf.substring(len,len-2) ) 
               {
                   return true;
               }

               return false;
           }

           /**
           * Valida onBlur se o cpf é válido ou n?o, 
           * caso seja digitado um.
           * 
           * param input element.
           */
          function validateOnBlurCPF(element)
          {
              var cpf = element.value;
              var len = element.value.length;

              if ( len > 0 )
              {
                   if ( len == 11 ) // Sem máscara
                   {
                       if ( parseInt(element.value) )
                       {
                           var maskcpf = cpf.substring(3,0) + "." + cpf.substring(3,6) + "." + cpf.substring(6,9) + "-" + cpf.substring(9,11);

                           if ( MIOLO_Validate_Check_CPF(maskcpf) )
                           {
                               element.value = maskcpf;
                           }
                           else
                           {
                                alert("O CPF informado não é válido.");
                                element.value = "";
                           }
                       }
                       else
                       {
                            alert("O CPF informado não é válido.");
                            element.value = "";
                       }
                   }
                   else if ( len == 14 ) // Com máscara.
                   {
                       if ( cpf.replace("-", "") )
                       {
                           cpf = cpf.replace("-", "");
                           var splt = cpf.split(".");

                           if ( splt.length == 3 )
                           {
                               if ( !MIOLO_Validate_Check_CPF(element.value) )
                               {
                                   alert("CPF Inválido");
                                   element.value = "";
                               }
                           }
                       }
                   }
                   else
                   {
                        alert("O CPF informado não é válido.");
                        element.value = "";
                   }
               }                    
          }
       ');
        
        $campos = $validadores = $inputs = array();
        
        $campos[] = $divErro = new MDiv("divErroConexao", "","mMessage mMessage Error");
        $divErro->addAttribute("style", "display:none");
        
        $this->verificarNavegador();

        $campos[] = MMessage::getMessageContainer();
        
        $imagemLogin = SAGU::getUnitParameter("avinst", "URL_LOGO_DO_FORMULARIO_DE_LOGIN_PERSONALIZADO", SAGU::getParameter("BASIC", "DEFAULT_UNIT_ID"));
        
        if( strlen($imagemLogin) > 0 )
        {
            $img = new MImage("imagemLogin", "", $imagemLogin);
            
            $campos["logo"] = new MDiv('', array($img));
        }

        $inputs[] = $this->obterCampoUsuario();
        $inputs[] = $this->obterCampoSenha();
        
        if ( sMultiUnidade::estaHabilitada() )
        {   
            $inputs[] = $this->obterCampoUnidade();
            $validadores[] = new MRequiredValidator('unitId', _M("Unidade"));
        }
        
        $campos[] = new MDiv("divCamposAutenticacao", $inputs);
        
        $campos[] = new HiddenField("return_to", $this->obterURLDeRetorno());
        
        if ( !$this->isAuthenticated() )
        {
            $buttons[] = new MButton('btnLogin', _M("Entrar"), null, $this->manager->getUI()->getImageTheme('modern', 'enviar_direita.png'));
        }
        else
        {
            $buttons[] = new MButton("btnLogout", _M("Sair"));
        }

        $campos[] = new MDiv("divBotoes", $buttons);
        
        $this->setFields($campos);
        $this->setShowPostButton(FALSE);

        $this->setFieldAttr('uid', 'readonly', $this->isAuthenticated());
        $this->setFieldAttr('pwd', 'visible', !$this->isAuthenticated());
        
        $this->setFocus('uid');
        
        $event = MUtil::getAjaxAction('btnLogin_click', NULL);
        $this->page->onload("handleEnterLogin = dojo.connect(dojo.byId('divCamposAutenticacao'), 'onkeypress', function (event) { if (event.keyCode==dojo.keys.ENTER) { event.preventDefault(); dojo.disconnect(handleEnterLogin); {$event}; }});");
        
        // Validar hash de autenticação do webServicesBasic, função wsLogin
        if ( SAGU::validarHashDeAutenticacao() ) 
        {
            $this->btnLogin_click();
        }
    }
    
    private function verificarNavegador()
    {
        $MIOLO = MIOLO::getInstance();
        
        // Verifica se está habilitada preferência para verificar e bloqueiar o acesso caso navegador não seja homologado
        $validaNavegador = SAGU::getParameter('BASIC', 'VALIDACAO_NAVEGADORES_HOMOLOGADOS');
        $alerta = SAGU::getParameter('BASIC', 'MENSAGEM_NAVEGADORES_NAO_HOMOLOGADOS');
        
        $browser = MUtil::getBrowser();  
        
        if ( $browser != 'Firefox' && $browser != 'Google Chrome' && $browser != 'Android' && $validaNavegador == 2 )
        {
            $MIOLO->error(_M($alerta));
        }
        else if ( $browser != 'Firefox' && $browser != 'Google Chrome' && $browser != 'Android' && $validaNavegador == 1 )
        {
            $fields[] = MMessage::getStaticMessage('infoAlerta', _M($alerta), MMessage::TYPE_INFORMATION);
            $fields[] = new MDiv();
        }
        
    }
    
    private function obterURLDeRetorno()
    {
        $return_to = $this->getFormValue('return_to', MIOLO::_Request('return_to'));

        if ( !$return_to )
        {
            $return_to_module = $this->manager->getConf('options.common');
            $return_to = MIOLO::getInstance()->getActionURL($return_to_module, 'main');
        }
        
        return $return_to;
    }
    
    private function obterCampoUsuario()
    {
        $uid = new MTextField("uid", $this->auth->login->iduser, _M("Login"), 20);
        $uid->setAttribute("placeholder", _M("CPF"));
        
        $uid->addAttribute("onBlur", "validateOnBlurCPF(this);");
        $uid->addAttribute("onKeyPress", "mascara(this, cpf_mask);");
        $uid->setJsHint(_M('Digite apenas os números do CPF.'));
        
        $label = new MLabel(_M("Usuário"));
        
        return new MDiv("divContainerCampoUsuario", array($label, $uid));

    }

    private function obterCampoSenha()
    {
        $pwd = new MCalendarField("pwd", "", _M("Data de nascimento"), 20);
        $pwd->setAttribute("placeholder", _M("Data de nascimento"));
        
        $label = new MLabel(_M("Data de nascimento"));
        
        return new MDiv("divContainerCampoSenha", array($label, $pwd));

    }
    
    private function obterCampoUnidade()
    {
        $label = new MLabel(_M("Unidade"));
        $unit = sMultiUnidade::obterCombo(null, false, true, 'm-caption-required', true);
        
        return new MDiv("divContainerCampoUnidade", array($label, $unit));
    }
    
    public function btnLogin_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $url = $MIOLO->getCurrentURL();

        $MIOLO->uses('/security/mauthmiolo.class.php');        
        $saguAuth = new MAuthMIOLO();
 
        $this->getData();

        $uid = str_replace(array("-", "."), "", $this->getFormValue('uid'));
        $pwd = $this->getFormValue('pwd');
        $unitId = $this->GetFormValue('unitId');
        
        // Seta dados quando há um hash válido
        if ( SAGU::validarHashDeAutenticacao() )
        {
            $userInformation = SAGU::obterDadosDeLoginAPartirDoHash();
            
            $uid = str_replace(array("-", "."), "", $userInformation->login);
            $pwd = $userInformation->password;
            $unitId = $userInformation->unitId;
        }

	## set pwd in session
	$MIOLO->getSession()->setValue('pwd', $pwd);
        
        $busPerson = $MIOLO->getBusiness('basic', 'BusPerson');
        $person = $busPerson->getPersonByMioloUserName($uid, true);
        $user = $person->personId;

        $MIOLO->logMessage('[LOGIN] Validating login information: ' . $uid);

        // Multiunidade
        if ( sMultiUnidade::estaHabilitada() && !sMultiUnidade::loginTemPermissao($uid, $unitId) && SAGU::allIsFilled($uid) )
        {
            if( !$unitId )
            {
                return new MMessageWarning(_M('Deve ser selecionado uma unidade.', $module));
            }

            $busUnit = new BusinessBasicBusUnit();
            $desc = $busUnit->getUnit($unitId)->description;

            return new MMessageWarning(_M('O usuário ou a senha ou a unidade está incorreta.', $module));
        }

        if ( SAGU::getParameter('BASIC', 'AUTH_METHOD') == 'LDAP' )
        {
            $user = $uid;
        }

        if ( SAGU::authenticate($user, $pwd) )
        {
            $MIOLO->session->set("loginFrom", $module);
            $MIOLO->session->set("senhaADExpirada", DB_FALSE);
            $ldif = SAGU::getParameter('BASIC', 'LDIF_ATUALIZA_SENHA_DO_USUARIO');

            if ( strlen($ldif) > 0 )
            {
                $sAuthLdap = new sAuthLdap();

                if ( $sAuthLdap->verificaSeSenhaDoUsuarioExpirou($uid, $pwd) )
                {
                    $MIOLO->session->set("senhaADExpirada", DB_TRUE);
                }
            }
            
            $busGroup = $MIOLO->getBusiness('base', 'group');
            $login = new MLogin($uid, $pwd, null, $person->mioloIdUser);
            $login->setGroups($busGroup->getGroups($uid));
            
            $MIOLO->auth->setLogin($login);

            if ( sMultiUnidade::estaHabilitada() )
            {
                sMultiUnidade::definirUnidadeLogada( $unitId );
            }
            
            $botao = '<a href="#" data-role="button" data-theme="c" data-icon="delete" data-iconpos="notext" class="ui-btn-right" onclick="miolo.doPostBack(\'confirmarSair\',\'\',\'__mainForm\'); return false;"></a>';
            $this->setResponse($botao, 'divBotaoSair');
            
            $return_to = $this->getFormValue('return_to');
            
            if ( $return_to == 'AVALIACAO' || (substr_count($url, 'module=avinst') > 0 || substr_count($url, 'avaliacao') > 0) )
            {
                $url = $MIOLO->getActionURL('avinst', 'main');
            }
            else
            {
                if ( $return_to )
                {
                    $url = $return_to;
                }
                else
                {
                    $url = $MIOLO->getActionURL('portal', 'main');
                }
            }
                        
            //Verifica se existe uma configuração para troca de senha e redireciona para tela de troca
            if( BusinessBasicBusConfiguracaoTrocaDeSenha::verificaTrocaDeSenha() == DB_TRUE )
            {
                $url = $MIOLO->getActionURL('portal', 'main');
            }
            
            // Redireciona para a url pós login
            if ( $this->manager->getIsAjaxCall() )
            {   
                $MIOLO->page->addJsCode("location.href = '{$url}';");
            }
            else
            {
                header("Location:" . $url);
            }
            
        }
        else
        {

            if ( $this->auth->errors )
            {
                $err = new MExpandDiv(NULL, _M('O usuário ou a senha está incorreta.') . '<br/>' . $this->auth->errors);
            }
            else
            {
                $err = _M('O usuário ou a senha está incorreta.');
            }

            new MMessageWarning($err);
        }
    }

    public function btnLogout_click()
    {
        $MIOLO = MIOLO::getInstance();
        $this->page->redirect($MIOLO->getActionURL("base", 'logout'));
    }

    public function isAuthenticated()
    {
        return $this->auth->isLogged();
    }
}

?>
