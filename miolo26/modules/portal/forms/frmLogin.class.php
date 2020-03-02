<?php

/**
 * Formulário de autenticação.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
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
class frmLogin extends MForm
{
    public $auth;

    public function frmLogin()
    {
        parent::__construct(_M('Autenticação do SAGU'));
        $this->addStyle('width', '50% !important');
        $this->addStyle('margin', 'auto');
        
        $this->addStyle('padding-top', '20%');
        
        $this->addStyle('text-align', 'center');
        $this->setIcon($this->manager->getUI()->getImageTheme('portal', 'login-16x16.png'));
        if ( $this->page->isPostBack() )
        {
            $this->eventHandler();
        }
    }

    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $this->auth = $this->manager->auth;
        $return_to = $this->getFormValue('return_to', MIOLO::_Request('return_to'));

        if ( !$return_to )
        {
            $return_to_module = $this->manager->getConf('options.common');
            $return_to = $MIOLO->getActionURL($return_to_module, 'main');
        }
        
        $fields[] = MMessage::getMessageContainer();

        $inputs[] = new MTextField('uid', $this->auth->login->iduser, _M('Usuário', $module), 20);
        $inputs[] = new PasswordField('pwd', '', _M('Senha', $module), 20);
        
        if ( sMultiUnidade::estaHabilitada() )
        {
            $unit = sMultiUnidade::obterCombo(null, true);
            //$unit->setIsRequired(true);
            $inputs[] = $unit;
            $validators[] = new MRequiredValidator('unitId', _M("Unidade"));
        }
        
        $fields[] = new MFormContainer('mioloFrmLogin', $inputs);

        $fields[] = new TextLabel('username', $this->auth->login->user, _M('Nome', $module), 40);
        $fields[] = new HiddenField('return_to', $return_to);

        $fields[] = new MSeparator();
        if ( !$this->isAuthenticated() )
        {
            $buttons[] = new FormButton('btnLogin', _M('Entrar', $module));
        }
        else
        {
            $buttons[] = new FormButton('btnLogout', _M('Sair', $module));
        }

        $fields[] = MUtil::centralizedDiv($buttons);

        $this->setFields($fields);
        $this->setShowPostButton(FALSE);

        $this->setFieldAttr('uid', 'readonly', $this->isAuthenticated());
        $this->setFieldAttr('pwd', 'visible', !$this->isAuthenticated());
        $this->getField('uid')->setClass('mTextUserField mTextField');
        $this->getField('pwd')->setClass('mTextPasswdField mTextField');

        $this->setFieldAttr('username', 'visible', $this->isAuthenticated());

        $this->setFocus('uid');

        // Connect enter event
        $event = MUtil::getAjaxAction('btnLogin_click', NULL);
        $this->page->onload("handleEnterLogin = dojo.connect(dojo.byId('mioloFrmLogin'), 'onkeypress', function (event) { if (event.keyCode==dojo.keys.ENTER) { event.preventDefault(); dojo.disconnect(handleEnterLogin); {$event}; }});");
    }

    public function btnLogin_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $MIOLO->uses('/security/mauthmiolo.class.php');        
        $saguAuth = new MAuthMIOLO();
 
        $this->getData();

        $uid = $this->getFormValue('uid');
        $pwd = $this->getFormValue('pwd');
        $unitId = $this->GetFormValue('unitId');
        
        $busPerson = $MIOLO->getBusiness('basic', 'BusPerson');
        $person = $busPerson->getPersonByMioloUserName($uid, true);

        $MIOLO->logMessage('[LOGIN] Validating login information: ' . $uid);

        // Multiunidade
        if ( sMultiUnidade::estaHabilitada() && !sMultiUnidade::loginTemPermissao($uid, $unitId) && SAGU::allIsFilled($uid) )
        {
            $busUnit = new BusinessBasicBusUnit();
            $desc = $busUnit->getUnit($unitId)->description;
            
            if(!$desc)
            {
                return new MMessageWarning(_M('É necessário informar a unidade', $module));
            }

            return new MMessageWarning(_M('O usuário não possui permissão de acesso na unidade @1.', $module, $desc));
        }
        
        if ( SAGU::authenticate($person->personId, $pwd) )
        {
            $login = new MLogin($uid, $pwd, null, $person->mioloIdUser);
            
            $MIOLO->auth->setLogin($login);

            if ( sMultiUnidade::estaHabilitada() )
            {
                sMultiUnidade::definirUnidadeLogada( $unitId );
            }
            
            $botao = '<a href="#" data-role="button" data-theme="c" data-icon="delete" data-iconpos="notext" class="ui-btn-right" onclick="miolo.doPostBack(\'confirmarSair\',\'\',\'__mainForm\'); return false;"></a>';
            $this->setResponse($botao, 'divBotaoSair');
            
            $return_to = $this->getFormValue('return_to');

            if ( $return_to )
            {
                $url = $return_to;
            }
            else
            {
                $url = $MIOLO->getActionURL('portal', 'main');
            }

            $this->page->redirect($url);
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
        $this->page->redirect($MIOLO->getActionURL($module, 'logout'));
    }

    public function isAuthenticated()
    {
        return $this->auth->isLogged();
    }
}

?>
