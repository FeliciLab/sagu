<?
class frmLoginMD5 extends MForm
{
    public function __construct()
    {   
        parent::__construct('Login MD5');
        $this->page->addScript('md5.js');
        $this->addJsCode($this->doChallengeResponse());
        $this->onSubmit('doChallengeResponse()');
        $this->eventHandler();
    }

    public function createFields()
    {
        $login = $this->manager->getLogin();
        $challenge = uniqid(rand());
        $fields = array(
            new MTextField('uid',$login->iduser,'Login',20),
            new MPasswordField('pwd','',_M('Password'),20),
            new MTextLabel('username',$login->user,'Nome',40),
            new MLink('mail','Email para contato','mailto:siga@ufjf.edu.br','siga@ufjf.edu.br'),
            new MHiddenField('challenge', $challenge),
            new MHiddenField('response', ''),
            new MHiddenField('tries', ''),
            new MHiddenField('return_to', '')
        );
        $this->setFields($fields);
        $buttons = array(
            new MButton('btnLogin', 'Login'),
            new MButton('btnLogout', 'Logout')
        ); 
        $this->setButtons($buttons);
        $this->btnLogin->visible = !$this->isAuthenticated();
        $this->btnLogout->visible = $this->isAuthenticated();
        $this->uid->readonly = $this->isAuthenticated();
        $this->pwd->visible = !$this->isAuthenticated();
        $this->username->visible = $this->isAuthenticated();
    }

    public function btnLogin_click()
    {   
        $this->manager->logMessage('[LOGIN] Validating login information');
        
        // Max login tryes
        $max_tries = 3;
        
        // autenticar usuÃ¡rio e obter dados do login
        $uid = $this->getFormValue('uid');
        $pwd = $this->getFormValue('pwd');
        $challenge = $this->getFieldValue('challenge');
        $response = $this->getFieldValue('response');

        if ( $this->loginPermitted($uid) )
        {
           if ( $this->manager->getAuth()->authenticate($uid, $challenge, $response) )
           {
               $return_to = $this->getFormValue('return_to');
               // ToDo: voltar para onde estava...
               if ( $return_to )
               {
                  $url = $return_to;
               }
               else
               {
                  $url = $MIOLO->getActionURL('admin','login');
               }
               $this->page->redirect($url);
            }
            else
            {      
               $tries = $this->getFormValue('tries');
               if ( $tries < $max_tries )
               {
                  $err = 'Erro na identificaÃ§Ã£o do usuÃ¡rio!' . ' - Restam ' . ( $max_tries - $tries) . 
                         ' ' . 'tentativa(s).';
                  $this->setFormValue('tries',$tries++);
                  $pwd = null;
                  $this->addError($err);
               }
               else
               {
                   throw new ELoginException('Erro na identificaÃ§Ã£o do usuÃ¡rio!');
               } 
            }
        }
        else
        {
            throw new ELoginException('Login denied');
        }
    }

    public function btnLogout_click()
    {
        $this->page->redirect($this->manager->getActionURL('main','logout'));
    }

    public function loginPermitted($uid)
    {  
       return true;
    }

    public function isAuthenticated()
    {
        return $this->manager->getAuth()->isLogged();
    }

    public function doChallengeResponse()
    {
       $code = "function doChallengeResponse() { \n".
               "  form = document.$this->name;\n". 
               "  str = form.uid.value + \":\" + \n" .
               "        MD5(form.pwd.value) + \":\" + \n" .
               "        form.challenge.value; \n".
               "  form.pwd.value = \"\";\n".
               "  form.response.value = MD5(str);\n".
               "  return true;\n".
               "}\n";
       return $code;
    }
}

?>