<?php

/**
 * <--- Copyright 2005-2012 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * @author Nataniel Ingor da Silva [nataniel@solis.coop.br]
 *
 * @since
 * Class created on 08/04/2014
 *
 */

$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('classes/prtCommonForm.class.php', $module);
$MIOLO->uses('classes/prtUsuario.class.php', $module);

class frmTrocaDeSenha extends frmMobile
{
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Troca de senha', MIOLO::getCurrentModule()));

        $this->setShowPostButton(FALSE);
    }
    
    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = SAGU::getFileModule();
        
        $this->setFormValue('senha', null);
        $this->setFormValue('senhaConfirmacao', null);
        $this->setFormValue('concorda', null);
        
        $senhaADExpirada = $MIOLO->session->getValue("senhaADExpirada");
        
        if ( $senhaADExpirada == DB_TRUE )
        {
            $flds[] = MMessage::getStaticMessage('msgSenhaExpirada', _M('Sua senha está expirada, escolha uma nova senha antes de prosseguir.'), MMessage::TYPE_WARNING);
        }
        
        $busConfiguracaoTrocaDeSenha = new BusinessBasicBusConfiguracaoTrocaDeSenha();
        $configuracaoTrocaDeSenhaId = $busConfiguracaoTrocaDeSenha->verficaConfiguracao(SAGU::getDateNow());
        
        $data = $busConfiguracaoTrocaDeSenha->getConfiguracaoTrocaDeSenha($configuracaoTrocaDeSenhaId->configuracaoTrocaDeSenhaId);
        
        $flds[] = new SHiddenField('configuracaoTrocaDeSenhaId', $configuracaoTrocaDeSenhaId->configuracaoTrocaDeSenhaId);
        
        
        
        $mensagem = new MLabel($data->mensagem);
        
        $flds[] = $mensagem;
        
        //Exibe o botão caso tenha concordado com os termos
        if( $data->concordarParaProsseguir == DB_TRUE )
        {
            //Termos da mensagem
            $concordaLabel = new MText('concordaLabel', _M('Li e concordo', $module) . ':');
            $concordaLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_LABEL_SIZE'));
            $concorda = new MCheckBox('concorda', DB_TRUE, null, $this->getFormValue('concorda', $this->concorda) == DB_TRUE);
            $concorda->setAttribute('onChange', MUtil::getAjaxAction('mostraBotao'));
            $concorda->addStyle('margin-left', '-60px');
            $flds[] = new MHContainer('hctConcordaComOsTermos', array($concordaLabel,$concorda), 'horizontal');        
        }
        
        $flds[] = new MSeparator();
        
        $max = SAGU::getParameter('BASIC', 'PASSWORD_MAX_SIZE');
        $min = SAGU::getParameter('BASIC', 'PASSWORD_MIN_SIZE');
        
        //Botões troca de senha
        $flds[] = new MDiv('divTrocarSenha');
        $aviso = new MText('aviso', _M('<br> Sua senha deve ser de no mínimo '. $min . ' caracteres e de no máximo '. $max . '.<br><br>'));
        $aviso->addBoxStyle('text-align', 'center');
        $flds[] = $aviso;
        
        $senhaAtualLabel = new MText('senhaAtualLabel', _M('Senha atual', $module).':');
        $senhaAtualLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_LABEL_SIZE'));
        $senhaAtual = new MPasswordField('senhaAtual', $this->getFormValue('senhaAtual'));        
        $hctSenhaAtual = new MHContainer('hctSenhaAtual', array($senhaAtualLabel, $senhaAtual));
        $flds[] = $hctSenhaAtual;
        
        $senhaLabel = new MText('senhaLabel', _M('Nova senha', $module).':');
        $senhaLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_LABEL_SIZE'));
        $senha = new MPasswordField('senha', $this->getFormValue('senha'));        
        $hctSenha = new MHContainer('hctSenha', array($senhaLabel, $senha));
        $flds[] = $hctSenha; 
                
        $senhaConfirmacaoLabel = new MText('senhaConfirmacaoLabel', _M('Confirmar nova senha', $module).':');
        $senhaConfirmacaoLabel->setWidth(SAGU::getParameter('BASIC', 'FIELD_LABEL_SIZE'));
        $senhaConfirmacao = new MPasswordField('senhaConfirmacao', $this->getFormValue('senhaConfirmacao'));        
        $hctSenhaConfirmacao = new MHContainer('hctSenhaConfirmacao', array($senhaConfirmacaoLabel, $senhaConfirmacao));
        $flds[] = $hctSenhaConfirmacao;
        
        $flds[] = $div;
        
        //Exibe o botão para salvar e continuar
        if( $data->concordarParaProsseguir == DB_FALSE || $senhaADExpirada == DB_TRUE )
        {
            $btTrocarSenha = new MButton('trocarSenha', _M('Trocar senha e prosseguir', $module), MUtil::getAjaxAction('trocarSenha'));
            $btTrocarSenha->addBoxStyle('text-align', 'center');
        }
        
        $flds[] = new MDiv('divConcorda', $btTrocarSenha);
        
        $baseGroup = new MBaseGroup('baseTrocaSenha', _M('Trocar senha'), $flds, 'vertical');
        $baseGroup->addStyle('text-align', 'center');
        $baseGroup->addStyle('width', '600px');
        $buttons[] = $baseGroup;
        
        $fields[] = MUtil::centralizedDiv($buttons);
                
        parent::addFields($fields);
        
    }
    
    public function mostraBotao($args)
    {
        if( $args->concorda == DB_TRUE )
        {
            $btTrocarSenha = new MButton('trocarSenha', _M('Trocar senha e prosseguir', $module));
            $btTrocarSenha->addEvent('click', MUtil::getAjaxAction('trocarSenha'));
        }
        
        $this->setResponse(array($btTrocarSenha), 'divConcorda');
    }
    
    public function trocarSenha($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = SAGU::getFileModule();
        
        $max = SAGU::getParameter('BASIC', 'PASSWORD_MAX_SIZE');
        $min = SAGU::getParameter('BASIC', 'PASSWORD_MIN_SIZE');
        
        if ( !prtUsuario::verificaSenhaDoUsuario($args->senhaAtual) )
        {
            $msg = _M('A senha atual está incorreta. Verifique o valor informado e tente novamente.');
            $prompt = MMessage::getStaticMessage('infoAlerta', _M($msg), MMessage::TYPE_ERROR);
            
            $this->setResponse(array($prompt), 'divTrocarSenha');
            
            return false;
        }        
        elseif( $args->senha != $args->senhaConfirmacao )
        {
            $msg =_M('O conteúdo dos campos "Nova senha" e "Confirmar nova senha" devem ser iguais.', $module);
            $prompt = MMessage::getStaticMessage('infoAlerta', _M($msg), MMessage::TYPE_ERROR);
            
            $this->setResponse(array($prompt), 'divTrocarSenha');
            
            return false;
        }
        elseif( strlen($args->senha) > $max || strlen($args->senha) < $min || strlen($args->senhaConfirmacao) > $max || strlen($args->senhaConfirmacao) < $min )
        {
            $msg = _M('Sua senha deve ser de no mínimo '. $min . ' caracteres e de no máximo '. $max . '.');
            $prompt = MMessage::getStaticMessage('infoAlerta', _M($msg), MMessage::TYPE_ERROR);
            
            $this->setResponse(array($prompt), 'divTrocarSenha');
            
            return false;
        }
        else
        {
            try
            {
                $busUser = new prtUsuario();
                $loginUser = $MIOLO->getLogin();

                $idUser = $loginUser->idkey;
                $senha = $args->senha;
                
                $ok = $busUser->trocarSenhaUsuario( $idUser, $senha, true );
                
                $data->configuracaoTrocaDeSenhaId = $args->configuracaoTrocaDeSenhaId;
                $data->userId = $loginUser->idkey;
                $data->concordo = $args->concorda;

                if( $ok == DB_TRUE )
                {   
                    if ( strlen($data->configuracaoTrocaDeSenhaId) > 0 )
                    {
                        $busConfiguracaoTrocaDeSenha = new BusinessBasicBusConfiguracaoTrocaDeSenha();
                        $busConfiguracaoTrocaDeSenha->salvaAlteracaoDeSenha($data);
                    }
                    
                    $goto = $this->verificaPermissaoUsuario();
                    
                    $MIOLO->session->set('passwordChanged', DB_TRUE);
                    $MIOLO->session->set('countDefine', null);
                    $MIOLO->page->redirect($goto);
                }
                else
                {   
                    $msg = _M('Não foi possível alterar sua senha.');
                    $prompt = MMessage::getStaticMessage('infoAlerta', _M($msg), MMessage::TYPE_ERROR);
                }
            }
            catch ( Exception $e )
            {
                $prompt = MMessage::getStaticMessage('infoAlerta', $e->getMessage(), MMessage::TYPE_ERROR);
            }
        }
        
        $fields[] = $prompt;
        
        $this->setResponse($fields, 'divTrocarSenha');
    }
    
    /*
     * Função que verifica os grupos que o usuário que permissão para redirecionar ao lgoar no sistema
     */
    public function verificaPermissaoUsuario()
    {   
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $return_to = $this->getFormValue('return_to');

        if ( $return_to == 'AVALIACAO' )
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
        
        return $url;
    }
}

?>
