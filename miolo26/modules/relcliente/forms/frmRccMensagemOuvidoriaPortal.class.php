<?php

/**
 * <--- Copyright 2011-2012 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Fermilab é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * Formulário de gerenciamento de mensagem de ouvidoria.
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 10/09/2012
 */

$MIOLO->uses('forms/frmDinamico.class.php', 'base');
$MIOLO->uses('tipos/rccMensagemOuvidoriaPortal.class.php', 'relcliente');
$MIOLO->uses('classes/rccEmail.class.php', 'relcliente');
class frmRccMensagemOuvidoriaPortal extends frmDinamico
{
    
    public function __construct() {
        parent::__construct(array(
            'modulo' => 'relcliente',
            'tipo' => 'rccMensagemOuvidoria'
        ), _M('Ouvidoria'));
}
    
    /**
     * Método reescrito para tratar os campos. 
     */
    public function definirCampos()
    {
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
        
        // Esconde banner.
        bJavascript::esconderElemento('m-container-top');
        
        // Esconde migalha.
        bJavascript::esconderElemento('__mainForm_bottom');
        
        
        // Obtém mensagem inicial.
        $tipoBasconfig = bTipo::instanciarTipo('basConfig');
        $tipoBasconfig->moduleconfig = 'RELCLIENTE';
        $tipoBasconfig->parameter = 'OUVIDORIA_MENSAGEM_INICIAL';
        $tipoBasconfig->popular();
        
        $mensagem = MMessage::getStaticMessage('divMensagem', $tipoBasconfig->value, MMessage::TYPE_INFORMATION);
        $this->addField($mensagem);
        $this->addField(new MDiv());    
        
        parent::definirCampos(FALSE, FALSE);
        
        // Esconde banner.
        bJavascript::esconderElemento('__mainForm_container_top');
        
        // Esconde migalha.
        bJavascript::esconderElemento('__mainForm_navbar');
        
        $camposEValidadores = $this->gerarCampos();
        $camposDinamicos = $camposEValidadores[0];
        $validadores = $camposEValidadores[1];
        
        // Omite campos que não devem aparecer no formulário.
        unset($camposDinamicos['mensagemouvidoriaid']);
        unset($camposDinamicos['estacancelada']);
        unset($camposDinamicos['origemdecontatoid']);
        unset($camposDinamicos['motivocancelamento']);
        unset($camposDinamicos['datahora']);
        unset($camposDinamicos['matricula']);
        unset($camposDinamicos['mensagem']);
        
        $campos[] = $camposDinamicos['nome'];
        $campos[] = $camposDinamicos['email'];
        $campos[] = $camposDinamicos['telefone'];
        $matricula = new MTextField('matricula', '', _M('CPF'), 20, _M("CPF deve ser válido"));
        $matricula->addAttribute("onBlur", "validateOnBlurCPF(this);");
        $matricula->addAttribute("onKeyPress", "mascara(this, cpf_mask);");
        $campos[] = $matricula;
        $campos[] = $camposDinamicos['vinculodecontatoid'];
        $campos[] = $camposDinamicos['tipodecontatoid'];
        $campos[] = $camposDinamicos['assuntodecontatoid'];
        $campos[] = new MMultiLineField('mensagem', '', _M('Mensagem'), 40, 8, 54);
        
        // Adiciona campos novos.
        $campos[] = new MCheckBox('enviarCopia', DB_TRUE, _M('Enviar uma cópia para meu e-mail') );
        $captchaId = md5(uniqid(time()));
        $campos[] = new MCaptchaField('captcha', _M('Digite o texto ao lado para confirmar'));
       
        // Limpa campo de capcha
        $this->page->onload(MCaptchaField::getRefreshCode('captcha') . bJavascript::definirValor('captcha', ''));
       
        // Omite campo "está cancelada".
        $campos[] = $estaCancelada = new MTextField('estacancelada', DB_FALSE);
        $estaCancelada->addStyle('display', 'none');
        
        // Omite campo origemdecontatoid.
        $campos[] = $estaCancelada = new MTextField('origemdecontatoid', rccMensagemOuvidoriaPortal::ORIGEM_MENSAGEM_OUVIDORIA);
        $estaCancelada->addStyle('display', 'none');
        
        // Omite campo origemdecontatoid.
        $campos[] = $estaCancelada = new MTextField('datahora', date("d/m/Y H:i:s"));
        $estaCancelada->addStyle('display', 'none');
        
        $campos[] = MUtil::centralizedDiv(array(new MDiv('btnNovo')), 'divBtnNovo');
        
        $this->addFields($campos);
        $this->setValidators($validadores);
    }
    
    /**
     * Método reescrito para obter os botões do formulário.
     */
    protected function obterBotoesPadrao()
    {
        return parent::obterBotoesPadrao(FALSE); 
    }
    
    /**
     * Método reescrito para fazer o strip_tags da mensagem.
     * 
     * @return stdClass Objeto com dados do formulário. 
     */
    public function getData()
    {
        $dados = parent::getData();
        $dados->mensagem = strip_tags($dados->mensagem);
        
        return $dados;
    }

    /**
     * Método reescrito para testar se foi inserido um email ou telefone.
     */
    public function botaoSalvar_click()
    {
        $dados = $this->getData();        
        
        if ( MCaptchaField::validate($dados->captcha) )
        {
            if ( $this->validate() )
            {
                $this->tipo->enviarCopia = $dados->enviarCopia;                
                parent::botaoSalvar_click();
                
                if ( $dados->enviarCopia == DB_TRUE && strlen($dados->email) > 0 )
                {
                    $rccEmail = new rccEmail(rccEmail::EMAIL_OUVIDORIA);
                    $rccEmail->definirAssunto(_M('Ouvidoria'));
                    
                    $conteudoEmail = "<html>
                        Sua mensagem foi enviada com sucesso. Confira abaixo os dados de sua solicitação:<br>
                        Nome: {$dados->nome}<br>
                        Email: {$dados->email}<br>
                        Telefone: {$dados->telefone}<br>
                        Mensagem: {$dados->nome}<br>
                    </html>";
                        
                    $rccEmail->definirConteudo($conteudoEmail);
                    $rccEmail->enviar();
                }
                
                new MMessageSuccess(_M('Mensagem enviada com sucesso.'));
                $btn = new MButton('btnNovo', _M('Nova mensagem'));

                $this->setResponse(new MDiv('divBtnNovo', $btn), 'divBtnNovo');
            }
            else
            {
                new MMessageWarning(_M('Verifique os dados informados.'));
                
                $this->addJsCode(MCaptchaField::getRefreshCode('captcha'));
            }
        }
        else
        {
            new MMessageWarning(_M('O código de verificação não é válido.'));
            
            $this->addJsCode(MCaptchaField::getRefreshCode('captcha'));
        }   
    }
    
    public function btnNovo_click()
    {
        $MIOLO = MIOLO::getInstance();
        $url = $MIOLO->getCurrentURL();
        
        $this->page->redirect($url);
    }
    
}

?>