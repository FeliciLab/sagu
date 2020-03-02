<?php

/**
 *
 * @author Fabiano da Silva Fernandes [contato@fabianofernandes.adm.br]
 *
 * @since
 * Class created on 08/10/2013
 */


$MIOLO->uses('forms/frmMobile.class.php', $module);

class frmAcessoMoodle extends frmMobile
{
    
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Acessar Moodle', MIOLO::getCurrentModule()));

      
        $this->eventHandler();
        $this->setShowPostButton(FALSE);
        
    }
    
    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();

        // Obtém os dados do form
        $personId = $this->personid;
       
	// Instancia os business
        $busPerson = $MIOLO->getBusiness('basic', 'BusPerson');
        // Obtém os dados da pessoa
        $person = $busPerson->getPerson($personId);  
        
        
        $uid = $person->mioloLogin;
	## get pwd in session
	$pwd = $MIOLO->getSession()->getValue('pwd');
        
        echo '
        <form target="_blank" name="login" id="login" method="post" action="http://'.SAGU::getUnitParameter('BASIC', 'MOODLE_URL', SAGU::getParameter('BASIC', 'DEFAULT_UNIT_ID')).'/login/index.php?authldap_skipntlmsso=1";>
        <input type="hidden" name="username" value="'.$uid.'"/><input class="loginform" type="hidden" name="password" value="'.$pwd.'"/>
        </form> 
        ';        
        
        $this->page->onLoad("document.forms[\"login\"].submit();window.history.back();");
        
        parent::defineFields();
    }


}

?>
