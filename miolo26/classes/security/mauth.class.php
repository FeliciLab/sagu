<?php
class MAuth extends MService
{
    public $login;  // objeto Login
    public $iduser; // iduser do usuario corrente
    public $module; // authentication module;

    public function __construct()
    {
        parent::__construct();
        $this->module = $this->manager->getConf('login.module');
    }

    public function setLogin($login)
    {
        $this->manager->session->setValue('login', $login);
        $this->login = $GLOBALS['login'] = $this->manager->login = $login;
        $this->iduser = ($this->login instanceof MLogin ? $this->login->id : NULL);
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function checkLogin()
    {
        $this->manager->logMessage('[LOGIN] CheckLogin');

        // we have a session login?
        $login = $this->manager->session->getValue('login');

        if ($login->id)
        {
            $this->manager->logMessage('[LOGIN] Using session login');
            $this->setLogin($login);
            return true;
        }

        // if not checking logins, we are done
        if ( (!MUtil::getBooleanValue($this->manager->getConf('login.check'))) )
        {
            $this->manager->logMessage('[LOGIN] Not checking login');
            return true;
        }

        // if we have already a login, assume it is valid and return
        if ($this->login instanceof MLogin)
        {
            $this->manager->logMessage('[LOGIN] Using existing login:' . $this->login->id);
            return true;
        }

        // still no login -- should we do an automatic login?
        if ( $auto = MUtil::getBooleanValue($this->manager->getConf('login.auto')) && $this->manager->getConf("login.$auto.id") )
        {
            $this->manager->logMessage('[LOGIN] Using automatic login ' . $auto);

            $login = new MLogin($this->manager->getConf("login.$auto.id"),
                               $this->manager->getConf("login.$auto.password"),
                               $this->manager->getConf("login.$auto.name"),
                               0);

            $this->setLogin($login);
            return true;
        }

        $this->manager->logMessage('[LOGIN] No Login but Login required');
        if ($this->isLogging())
        {
            return true;
        }
        else
        {
            $this->manager->invokeHandler($this->manager->getConf('login.module'),'login');
            return false;
        }
    }

    public function authenticate($user, $pass)
    {
        return false;
    }

    public function isLogged()
    {
        return ($this->login->id != NULL);
    }

    public function isLogging()
    {
        $context = $this->manager->getContext();

        $isLogging = false;

        if ( ($context->module == $this->module) && 
             in_array($context->action, array('login', 'auth', 'lostpass')) )
        {
            $isLogging = true;
        }

        return $isLogging;
    }

    public function logout($forced = '')
    {
        if ($this->manager->getConf("options.dbsession"))
        {
            $session = $this->manager->getBusinessMAD('session');
            $session->registerOut($this->getLogin());
        }

        $this->setLogin(NULL);
        $this->manager->getSession()->destroy();
    }
}
?>
