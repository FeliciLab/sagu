<?php

class frmRemModuleOptions extends MForm
{
    public $home;
    public $objModule;
    public $chks;

    private $business;
    
    public $MIOLO, $module, $action;

    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->action   = MIOLO::getCurrentAction();
        $this->business = $this->MIOLO->getBusiness($this->module, 'module');

        parent::__construct( _M('Remove Module Options','admin') );
        $this->setWidth('100%');
        $this->setIcon( $this->MIOLO->getUI()->getImage('admin', 'modules-16x16.png') );
        $url = $this->MIOLO->getActionURL($this->module, 'main:modules:rem_module_options');
        $this->page->setAction($url);
        $this->setClose( $this->MIOLO->getActionURL('admin', 'main') );
        $this->eventHandler();
    }

    public function createFields()
    {  
    
        $moduleDeleteName = $this->MIOLO->_REQUEST('moduleToDelete');
                            
        $dom = new DOMDocument();
        $dom->load($this->MIOLO->getConf('home.modules') . '/' . $moduleDeleteName . '/etc/module.inf');
        
        $modInfo[0]=$dom->getElementsByTagName('name')->item(0)->nodeValue;
        $modInfo[1]=$dom->getElementsByTagName('description')->item(0)->nodeValue;
        $modInfo[2]=$dom->getElementsByTagName('menu_text')->item(0)->nodeValue;
        $modInfo[3]=$dom->getElementsByTagName('version')->item(0)->nodeValue;
        $modInfo[4]=$dom->getElementsByTagName('maintainer')->item(0)->nodeValue;
        $modInfo[5]=$dom->getElementsByTagName('home_page')->item(0)->nodeValue;
        $modInfo[6]=$dom->getElementsByTagName('email')->item(0)->nodeValue;
        $modInfo[7]=$dom->getElementsByTagName('update_url')->item(0)->nodeValue;
        
        $textField0  = new MTextField( 'moduleName', $modInfo[0], _M('Module', 'admin'), 30 );
        $textField0->setReadOnly( true );
        $textField1  = new MTextField( 'moduloDescription', $modInfo[1],
        _M('Description', 'admin'), 30 );
        $textField1->setReadOnly( true );
        $textField2  = new MTextField( 'moduloMenu', $modInfo[2], _M('Text Menu', 'admin'), 30 );
        $textField2->setReadOnly( true );
        $textField3  = new MTextField( 'moduloVersion', $modInfo[3],
        _M('Version', 'admin'), 30 );
        $textField3->setReadOnly( true );
        $textField4  = new MTextField( 'moduloMaintainer', $modInfo[4],
        _M('Maintainer', 'admin'), 30 );
        $textField4->setReadOnly( true );
        $textField5  = new MTextField( 'moduloHomePage', $modInfo[5], _M('Home Page', 'admin'), 30 );
        $textField5->setReadOnly( true );
        $textField6  = new MTextField( 'moduloEmail', $modInfo[6], _M('Email',
        'admin'), 30 );
        $textField6->setReadOnly( true );
        $textField7  = new MTextField( 'moduloUpdateURL', $modInfo[7],
        _M('Update URL', 'admin'), 30 );
        $textField7->setReadOnly( true );
        $hiddenField = new HiddenField('moduleToDelete',$moduleDeleteName);
       
        $fields = array($textField0, $textField1, $textField2, $textField3, $textField4, $textField5, $textField6, $textField7, $hiddenField);
       
        $fieldsCointainer = new MHContainer('',$fields);
              
        $spacer = new MSpacer('5px');
         
        $painel = new BaseGroup('painel',_M('Module Information', 'admin'),array($spacer, $fieldsCointainer, $spacer),'vertical','css'); 
       
        $this->setFields($painel);
                                        
        $this->addField($spacer);
        
        $this->addField(new MLabel (_M('Removable Options:', 'admin')));
        
        $this->addField($spacer);
        
        $chks = array ( new MCheckBox('chk1','c1', _M( 'Remove Menu Link', 'admin'), false),
                        new MCheckBox('chk2','c2', _M( 'Remove Module Directory', 'admin'), false),
                        new MCheckBox('chk3','c3', _M( 'Drop database', 'admin'), false)
                       );
                       
        $this->addField($chks);
        
        $buttons = array( new MButton('btnModuleDelete', _M('Remove', 'admin'))
                        );
        $this->setButtons($buttons);
                       
    }
    
    public function btnModuleDelete_click()
    {        
        $moduleDeleteName = $this->MIOLO->_REQUEST('moduleToDelete');
        
        if ($this->chk1->checked)
        {
            $this->deleteModuleLink();             
        }
        
        if ($this->chk3->checked)
        {
            $this->deleteModuleBD();
        }
        
        if ($this->chk2->checked)
        {
            $this->deleteModuleBase();
        }

        try{
            $db = $this->MIOLO->getDatabase('admin');
            $sql = "delete from miolo_module where idModule='".$moduleDeleteName."'";
            $db->query($sql);
        
            $this->MIOLO->information( _M('Module successfully Removed!'), $this->MIOLO->getActionURL('admin', 'modules'));
            
         }
        catch( EDatabaseException $e )
        {
            $this->addError( $e->getMessage() );
        }

    }
    
    
    public function deleteModuleBD()
    {    
    
        $moduleDeleteName = $this->MIOLO->_REQUEST('moduleToDelete');
        $dir=$this->MIOLO->getConf('home.modules') . '/' . $moduleDeleteName . '/';
        
        $this->removeDB($moduleDeleteName);                   
    }
    
    public function deleteModuleBase()
    {          
        $moduleDeleteName = $this->MIOLO->_REQUEST('moduleToDelete');
                        
        $dir=$this->MIOLO->getConf('home.modules') . '/' . $moduleDeleteName . '/';
        
        MUtil::removeDirectory($dir);
    
    }
    
    public function deleteModuleLink()
    {    
        $moduleDeleteName = $this->MIOLO->_REQUEST('moduleToDelete');
    
        $dom = new DOMDocument();
        $dom->load($this->MIOLO->getConf('home.modules') . '/modules_menu.xml');
        $xpath = new DOMXPath($dom);
        $modules = $xpath->query('menu');
        
        foreach ($modules as $m)
        {
            $fn = $xpath->query('caption',$m);
            $moduleCaption = $fn->item(0)->firstChild->nodeValue;                        
        
            $fn = $xpath->query('module',$m);
            $moduleName = $fn->item(0)->firstChild->nodeValue;
            
            if ($moduleDeleteName!=$moduleName)
            {
                $fn = $xpath->query('action',$m);
                $moduleAction = $fn->item(0)->firstChild->nodeValue;

                $fn = $xpath->query('icon',$m);
                $moduleIcon = $fn->item(0)->firstChild->nodeValue;
                
                $fn = $xpath->query('description',$m);
                $moduleDescription = $fn->item(0)->firstChild->nodeValue;
                
                $fields[] = array ( $moduleCaption, $moduleName, $moduleAction, $moduleIcon, $moduleDescription);
            }
        }
        
       $this->createXML($this->MIOLO->getConf('home.modules') . '/modules_menu.xml',$this->generateLinkMainMenuXML($fields));
       
    } 
    
    public function generateLinkMainMenuXML($links)
    {
        $dom = new DomDocument();        
        $mainmenu = $dom->appendChild($dom->createElement('mainmenu'));
        
        foreach ($links as $l)
        {   
            $menu = $mainmenu->appendChild($dom->createElement('menu'));
            $menu->appendChild($dom->createElement('caption',$l[0]));
            $menu->appendChild($dom->createElement('module',$l[1]));
            $menu->appendChild($dom->createElement('action',$l[2]));
            $menu->appendChild($dom->createElement('icon',$l[3]));            
            $menu->appendChild($dom->createElement('description',$l[4]));
        }
        
        $dom->formatOutput = true;
        return ($dom->saveXML());
    }
    
    public function createXML($path,$xml)
    {
        file_put_contents($path,$xml);        
    }
    
    public function removeDB($moduleDeleteName)
    {         

        $dom = new DOMDocument();
        $dom->load($this->MIOLO->getConf('home.modules') . '/' . $moduleDeleteName . '/etc/module.conf');
        $bdSystem=$dom->getElementsByTagName('system')->item(0)->nodeValue;
        $bdName=$dom->getElementsByTagName('name')->item(0)->nodeValue;
        $bdHost=$dom->getElementsByTagName('host')->item(0)->nodeValue;
        $bdPort=$dom->getElementsByTagName('port')->item(0)->nodeValue;
        $bdUser=$dom->getElementsByTagName('user')->item(0)->nodeValue;
        $bdPassword=$dom->getElementsByTagName('password')->item(0)->nodeValue;
        

        try
        {
            $conf = 'createNewDB';
            $this->MIOLO->setConf("db.$conf.host", $bdHost);
                
            if ($bdSystem=="postgres" || $bdSystem=="mysql")
            {                
                        
                if ($bdSystem=="postgres")
                {
                    $this->MIOLO->setConf("db.$conf.name", 'template1');
                }
            
                if ($bdSystem=="mysql")
                {
                    $this->MIOLO->setConf("db.$conf.name", 'teste');
                }           
            
                $this->MIOLO->setConf("db.$conf.system", $bdSystem);
                $this->MIOLO->setConf("db.$conf.port", $bdPort);
                $this->MIOLO->setConf("db.$conf.user", $bdUser);
                $this->MIOLO->setConf("db.$conf.password", $bdPassword);
            
                $db2 = $this->MIOLO->getDatabase( $conf );                    
                $db2->query("DROP DATABASE ".$bdName);
            }
        }
        catch( EDatabaseException $e )
        {
            $this->addError( $e->getMessage() );
        }

                
        if ($bdSystem=="sqlite" || $bdSystem=="SQL Server")
        {
            unlink ($bdName);
        }
    }
   
}

?>
