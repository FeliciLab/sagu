<?php

class frmLogin extends MForm
{
    const MAX_ATTEMPTS = 3;

    public $auth;

    public function frmLogin()
    {
        parent::__construct(_M('Login'));
        $this->setWidth('60%');
        $this->setIcon($this->manager->getUI()->getImage('admin', 'login-16x16.png'));
        if ($this->page->isPostBack())
        {
            $this->eventHandler();
        }
    }

    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        
        $ui = $MIOLO->getUI();
        
        $this->auth = $this->manager->auth;
        $return_to = $this->getFormValue('return_to', MIOLO::_Request('return_to'));
        
        if (! $return_to)
        {
            $return_to_module = $this->manager->getConf('options.common');
            $return_to = $MIOLO->getActionURL($return_to_module, 'main');
        }
        
        $imgLogin = new MImage('imgLogin', _M('Inform the username and password'), $ui->getImage($module, 'attention.png'));
        
        $fields[] = MMessage::getMessageContainer();

        $inputs[] = new MTextField('uid', $this->auth->login->iduser, 'Login', 20);
        $inputs[] = new PasswordField('pwd', '', _M('Password'), 20);
        $fields[] = new MFormContainer('mioloFrmLogin', $inputs);

        $fields[] = new TextLabel('username', $this->auth->login->user, 'Nome', 40);
        $fields[] = new HyperLink('mail', 'Email para contato', 'mailto:' . $this->manager->getConf('theme.email'), $this->manager->getConf('theme.email'));
        $fields[] = new HiddenField('return_to', $return_to);

        $this->setFields($fields);
        
        $this->addButton(new FormButton('btnLogin', _M('Login', $module)));
        $this->addButton(new FormButton('btnLogout', _M('Logout', $module)));
        //$help = $MIOLO->getActionURL('admin','loginhelp',null,null,'popup.php');
        $helpMsg = _M('You need to inform a valid username and password in order to login.');
        $this->addButton(new FormButton('btnHelp', _M('Help', $module), "alert('$helpMsg');"));
        
        $this->setButtonAttr('btnLogin', 'visible', ! $this->isAuthenticated());
        $this->setButtonAttr('btnLogout', 'visible', $this->isAuthenticated());
        $this->setFieldAttr('uid', 'readonly', $this->isAuthenticated());
        $this->setFieldAttr('pwd', 'visible', ! $this->isAuthenticated());
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
        
        $this->getData();

        $attempts = 0;
        if ( $this->manager->getSession()->getValue('mioloLoginAttempts') !== NULL )
        {
             $attempts = $this->manager->getSession()->getValue('mioloLoginAttempts');
        }
        
        // get form data
        $uid = $this->getFormValue('uid');
        $pwd = $this->getFormValue('pwd');
        
        $MIOLO->logMessage('[LOGIN] Validating login information: ' . $uid);
        
        if (! $this->loginPermitted($uid))
        {
            $err = _M('Acess not allowed!', MIOLO::getCurrentModule() );
        }
        else
        {
            // Authentica the user
            if ($this->auth->authenticate($uid, $pwd))
            {
                $return_to = $this->getFormValue('return_to');

                if ($return_to)
                {
                    $url = $return_to;
                }
                else
                {
                    $url = $MIOLO->getActionURL('admin', 'login');
                }
                $this->page->redirect($url);
            }
            else
            {
                if ( $attempts >= self::MAX_ATTEMPTS )
                {
                    $MIOLO->error( _M('Error identifying user!', MIOLO::getCurrentModule()) );
                }
                else
                {
                    $left = self::MAX_ATTEMPTS - $attempts;

                    $err  = _M('Error validating user! ');
                    $err .= _M('@1 tries left.',MIOLO::getCurrentModule(), $left);
                    $err .= '<br/>' . $this->auth->errors;

                    $attempts++;
                    $this->manager->getSession()->setValue('mioloLoginAttempts', $attempts);

                    $pwd = null;
                    
//                    if ($err)
//                    {
//                        $this->addError($err);
//                    }
                    
                    new MMessage($err, MMessage::TYPE_ERROR);
                }
            }
        }
    }

    public function btnLogout_click()
    {
        $MIOLO = MIOLO::getInstance();
        $this->page->redirect($MIOLO->getActionURL($module, 'logout'));
    }

    public function loginPermitted($uid)
    {
        $MIOLO = MIOLO::getInstance();
        
        $ok = true;
        return $ok;
    }

    public function isAuthenticated()
    {
        return $this->auth->isLogged();
    }

}
?>
