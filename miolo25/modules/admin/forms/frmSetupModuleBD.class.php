<?php

class frmSetupModuleBD extends MForm
{
    public $home;
    public $objModule;
    public $modName;

    public function __construct()
    {
        global $dirMod, $modName;
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
     
        $session = $MIOLO->session;
     
        if ($session->getValue('installModulo'))
        {
            $modName = $session->getValue('installModulo');
        }
        
        $this->home      = $MIOLO->getActionURL($module, $action);
        $this->objModule = $MIOLO->getBusiness($module, 'module');

        parent::__construct( _M('Setup Data Base','admin') );
        $this->setWidth('70%');
        $this->setIcon( $MIOLO->getUI()->getImage('admin', 'modules-16x16.png') );
        $this->setClose( $MIOLO->getActionURL('admin', 'main') );
        $this->eventHandler();
        
    }

    public function createFields()
    {  
        global $modName;
        $MIOLO = MIOLO::getInstance();
        
        //le informacoes do modulo no xml module.conf
        $dom = new DomDocument();
        $dom->load($MIOLO->getConf('home.modules') . '/' . $modName .
        '/etc/module.conf');

        $modInfo[0]=$dom->getElementsByTagName('system')->item(0)->nodeValue;
        $modInfo[1]=$dom->getElementsByTagName('host')->item(0)->nodeValue;
        $modInfo[2]=$dom->getElementsByTagName('port')->item(0)->nodeValue;
        $modInfo[3]=$dom->getElementsByTagName('name')->item(0)->nodeValue;
        $modInfo[4]=$dom->getElementsByTagName('user')->item(0)->nodeValue;        
        $modInfo[5]=$dom->getElementsByTagName('password')->item(0)->nodeValue;
    
        $options = array('postgres'=>'Postgres','mysql'=>'MySQL','SQL Server'=>'SQL Server','sqlite'=>'SQL Lite');
        
        
        if ($modInfo[2])
        {
            $port = $modInfo[2];
        }
        else
        {
            $port = "default";
        }
        
        $fields = array(new MSelection( 'bdSystem',      $modInfo[0], 'Banco de Dados', $options),
                        new MTextField( 'bdHost',        $modInfo[1], 'Host', 30 ),
                        new MTextField( 'bdPort',        $port,       'Port', 30 ),
                        new MTextField( 'bdName',        $modInfo[3], 'Nome do Banco', 30 ),
                        new MTextField( 'bdUser',        $modInfo[4], 'User', 30 ),
                        new PasswordField( 'bdPassword', $modInfo[5], 'Password', 30 )
                        );
        
        $this->setFields($fields);
        
        $buttons = array( new MButton('btnInstallBD'   , _M('Next >' , 'admin') )
                        );
        $this->setButtons($buttons);
    }
    
    public function btnInstallBD_click()
    {
        global $modName;
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction(); 

       //chama a funcao que cria o banco
       if (file_exists($MIOLO->getConf('home.modules') . '/' . $modName .
        '/sql/' . $modName . '.sql'))
       {
           $this->createBD();
       }
       
       //gera o module.conf no modules/modulo/etc 
       $this->createXML($MIOLO->getConf('home.modules') . '/' . $modName . '/etc/module.conf', $this->generateXML());
       
       //acresenta o modulo no menu
       $this->createXML($MIOLO->getConf('home.modules') . '/modules_menu.xml', $this->generateLinkMainMenuXML());
       
       $MIOLO->information( _M('Module successfully Installed!'), $MIOLO->getActionURL('admin', 'modules'));
 
    }
    
    public function createBD()
    {
        global $modName;
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
    
        //cria a tabela
        $conf = 'createNewDB';
        $MIOLO->setConf("db.$conf.host", $this->getFieldValue('bdHost'));
        
        if ($this->getFieldValue('bdSystem')=="postgres")
        {
            $MIOLO->setConf("db.$conf.name", 'template1');
        }
        
        if ($this->getFieldValue('bdSystem')=="mysql")
        {
            $MIOLO->setConf("db.$conf.name", 'teste');
        }

        if ($this->getFieldValue('bdSystem')=="sqlite" || $this->getFieldValue('bdSystem')=="SQL Server")
        {
            $MIOLO->setConf("db.$conf.name", $this->getFieldValue('bdName'));
        }
        
        $MIOLO->setConf("db.$conf.system", $this->getFieldValue('bdSystem'));
        
        if($this->getFieldValue('bdPort')!="default")
        {
            $MIOLO->setConf("db.$conf.port", $this->getFieldValue('bdPort'));
        }
        
        
        $MIOLO->setConf("db.$conf.user", $this->getFieldValue('bdUser'));
        $MIOLO->setConf("db.$conf.password", $this->getFieldValue('bdPassword'));
        
        
        $db = $MIOLO->getDatabase( $conf );
        $db->execute("CREATE DATABASE ".$this->getFieldValue('bdName'));
        
        

        //cria tabelas no banco usando o script sql do modulo
        $conf2 = 'createNewDBTables';
        $MIOLO->setConf("db.$conf2.system", $this->getFieldValue('bdSystem'));
        $MIOLO->setConf("db.$conf2.name", $this->getFieldValue('bdName'));
        $MIOLO->setConf("db.$conf2.host", $this->getFieldValue('bdHost'));
        $MIOLO->setConf("db.$conf2.user", $this->getFieldValue('bdUser'));
        $MIOLO->setConf("db.$conf2.password", $this->getFieldValue('bdPassword'));
        
        
        //leitura do script sql do modulo
        $db = $MIOLO->getDatabase( $conf2 );
        $sqlFile = $MIOLO->getConf('home.modules') . "/" . $modName . '/sql/'.$modName.'.sql';
        
        $fd = file_get_contents($sqlFile);        
        $sql = split(";",$fd);
        
        foreach ($sql as $s)
        {        
            @$db->query($s);
        }

    }
    
    public function generateXML()
    {
        global $modName;
        $MIOLO = MIOLO::getInstance();
    
        //recebe a porta do BD, caso default, passa null para a porta
        if ($this->getFieldValue('bdPort')!="default")
        {
            $port = $this->getFieldValue('bdPort');
        }
        else
        {
            $port = null;
        }
        
        //gera o xml do module.conf
        $dom = new DomDocument('1.0','iso-8859-1');
        $conf = $dom->appendChild($dom->createElement('conf'));
        $db = $conf->appendChild($dom->createElement('db'));
        $moduloName = $db->appendChild($dom->createElement($modName));
        $moduloName->appendChild($dom->createElement('system',$this->getFieldValue('bdSystem')));
        $moduloName->appendChild($dom->createElement('host',$this->getFieldValue('bdHost')));
        $moduloName->appendChild($dom->createElement('port', $port));
        $moduloName->appendChild($dom->createElement('name',$this->getFieldValue('bdName')));
        $moduloName->appendChild($dom->createElement('user',$this->getFieldValue('bdUser')));
        $moduloName->appendChild($dom->createElement('password',$this->getFieldValue('bdPassword')));
        $theme = $conf->appendChild($dom->createElement('theme'));
        $theme->appendChild($dom->createElement('module',$MIOLO->getConf("theme.module")));
        $theme->appendChild($dom->createElement('main',$MIOLO->getConf("theme.main")));
        $theme->appendChild($dom->createElement('lookup',$MIOLO->getConf("theme.lookup")));
        $theme->appendChild($dom->createElement('title',$modName));
        
                
        $dom->formatOutput = true;
        return ($dom->saveXML());
    }
    
    public function generateLinkMainMenuXML()
    {
        global $modName;
        $MIOLO = MIOLO::getInstance();
        
        //leitura do module.inf
        $moduleInf = new DomDocument();
        $moduleInf->load($MIOLO->getConf('home.modules') . '/' . $modName . '/etc/module.inf');
        $modInfo[0] = $moduleInf->getElementsByTagName('menu_text')->item(0)->nodeValue;
        $modInfo[1] = $moduleInf->getElementsByTagName('description')->item(0)->nodeValue;
        
        
        //leitura do modules_menu.xml atual
        $domMenu = new DOMDocument();
        $domMenu->load($MIOLO->getConf('home.modules') . '/modules_menu.xml');
        $xpath = new DOMXPath($domMenu);
        $links = $xpath->query('menu');
        
        $dom = new DomDocument();        
        $mainmenu = $dom->appendChild($dom->createElement('mainmenu'));
        
        foreach ($links as $l)
        {
        
            $fn = $xpath->query('caption',$l);
            $menuCaption = $fn->item(0)->firstChild->nodeValue;
            
            $fn = $xpath->query('module',$l);
            $menuModule = $fn->item(0)->firstChild->nodeValue;
            
            $fn = $xpath->query('action',$l);
            $menuAction = $fn->item(0)->firstChild->nodeValue;
            
            $fn = $xpath->query('icon',$l);
            $menuIcon = $fn->item(0)->firstChild->nodeValue;            
            
            $fn = $xpath->query('description',$l);
            $menuDescription = $fn->item(0)->firstChild->nodeValue;

            
            $menu = $mainmenu->appendChild($dom->createElement('menu'));
            $menu->appendChild($dom->createElement('caption',$menuCaption));
            $menu->appendChild($dom->createElement('module',$menuModule));
            $menu->appendChild($dom->createElement('action',$menuAction));
            $menu->appendChild($dom->createElement('icon',$menuIcon));
            $menu->appendChild($dom->createElement('description',$menuDescription));
        
        }
        
        //adiciona o novo modulo no modules_menu.xml
        $menu = $mainmenu->appendChild($dom->createElement('menu'));
        $menu->appendChild($dom->createElement('caption',$modInfo[0]));
        $menu->appendChild($dom->createElement('module',$modName));
        $menu->appendChild($dom->createElement('action','main'));
        $menu->appendChild($dom->createElement('description',$modInfo[1]));        
        
        $dom->formatOutput = true;
        return ($dom->saveXML());  
        
    }
    
    public function createXML($path,$xml)
    {
        global $modName;
        $MIOLO = MIOLO::getInstance();
        file_put_contents($path,$xml);        
    }

}

?>
