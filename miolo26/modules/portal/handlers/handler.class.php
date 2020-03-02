<?php
class HandlerPortal extends MHandler
{
    public function init()
    {
        parent::init();
        
        //importa os componentes do jquery
        $this->manager->uses('classes/jCollapsible.class.php', 'portal');
        $this->manager->uses('classes/bottomBar.class.php', 'portal');
        $this->manager->uses('classes/mobilePanel.class.php', 'portal');
        $this->manager->uses('classes/prtUsuario.class.php', 'portal');

        $this->manager->uses('classes/fields/SLookupContainer.class', 'basic');
        
        $this->manager->uses('types/TraRequest.class', 'training');
        
        $this->manager->uses('types/TraRequest.class', 'training');
        $this->manager->uses('types/TraAddendumCourseAdmin.class', 'training');
        $this->manager->uses('types/TraTeam.class', 'training');
        
        $this->manager->uses('types/ResResidente.class', 'residency');
        $this->manager->uses('types/ResOfertaDeUnidadeTematica.class', 'residency');
        $this->manager->uses('types/ResPreceptoria.class', 'residency');
        
        $this->manager->uses('db/groupuser.class', 'admin');
        $this->manager->uses('db/BusUser.class', 'admin');
        
        //compatibiliadade com o sagu
        define('DB_NAME', 'basic');
        define('DB_TRUE', 't');
        define('DB_FALSE', 'f');
        define('BASE_ENCODING', 'UTF-8');
        
        $this->manager->page->onload("if ( typeof jQuery != 'undefined' ) $('.ui-page').trigger('create');");
    }
    
    public function dispatch($handler)
    {
        //autentica pelo joomla
        if($_COOKIE['miolo_username'] && $_COOKIE['m_password'])
        {
            $MIOLO = MIOLO::getInstance();
            $MIOLO->uses('/security/mauthmiolo.class.php');        
        
            $busPerson = $MIOLO->getBusiness('basic', 'BusPerson');
            $person = $busPerson->getPersonByMioloUserName($uid, true);
            
            $uid = base64_decode($_COOKIE['miolo_username']);
            $pwd = base64_decode($_COOKIE['m_password']);
            
            if ( SAGU::authenticate($person->personId, $pwd) )
            {
                $login = new MLogin($uid,
                                    $pwd,
                                    null,
                                    $person->mioloIdUser);

                $MIOLO->auth->setLogin($login);
            }
        }
        
        SDatabase::query("SET DateStyle TO 'SQL,DMY'");
        
        if(!$this->manager->getLogin()->id)
        {
            $return = parent::dispatch('login');
        }
        else
        {
            $return = parent::dispatch($handler);
        }
        
        return $return;
    }
    
}

?>
