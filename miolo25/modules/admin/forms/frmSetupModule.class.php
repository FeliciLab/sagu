<?php

class frmSetupModule extends MForm
{
    public $home;
    public $objModule;
    private $business;

    public $MIOLO, $module, $action;

    public function __construct()
    {   global $modInfo, $modpkgName, $dirModTMP, $dirTMP;

    //$modInfo -> array que recebe os valores do xml do module.inf
    //$modpkgName -> nome do diretorio do modulo
    //$dirModTMP -> diretorio em que se encotra o modulo no tmp
    //$dirTMP -> diretorio do tmp                
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->action   = MIOLO::getCurrentAction();
        $this->business = $this->MIOLO->getBusiness($this->module, 'module');

        //recebe o diretorio em que se encotra o modulo zip
        //recebe instalacao local
        if ($modpkgName = $this->MIOLO->_REQUEST('modulo'))
        {
            $tmpSysDir = MUtil::getSystemTempDir();

            if ( ! $tmpSysDir )
            {
                $tmpSysDir = '/tmp';
            }

            $dirModTMP = $tmpSysDir . "/". $modpkgName;
            $dirTMP = $tmpSysDir;
        }

        //le informacoes do modulo no xml module.inf
        $dom = new DomDocument();
        $dom->load($dirModTMP."/etc/module.inf");

        $modInfo[0]=$dom->getElementsByTagName('name')->item(0)->nodeValue;
        $modInfo[1]=$dom->getElementsByTagName('description')->item(0)->nodeValue;
        $modInfo[2]=$dom->getElementsByTagName('menu_text')->item(0)->nodeValue;
        $modInfo[3]=$dom->getElementsByTagName('version')->item(0)->nodeValue;
        $modInfo[4]=$dom->getElementsByTagName('maintainer')->item(0)->nodeValue;
        $modInfo[5]=$dom->getElementsByTagName('home_page')->item(0)->nodeValue;
        $modInfo[6]=$dom->getElementsByTagName('email')->item(0)->nodeValue;
        $modInfo[7]=$dom->getElementsByTagName('update_url')->item(0)->nodeValue;

        $this->home      = $this->MIOLO->getActionURL($this->module, $this->action);
        $this->objModule = $this->MIOLO->getBusiness($this->module, 'module');

        parent::__construct( _M('Setup Module','admin') );
        $this->setWidth('70%');
        $this->setIcon( $this->MIOLO->getUI()->getImage('admin', 'modules-16x16.png') );
        $this->setClose( $this->MIOLO->getActionURL('admin', 'main') );

        $this->eventHandler();
    }

    public function createFields()
    {  global $modInfo, $modpkg, $modpkgName;

        $textField1  = new MTextField( 'moduleName', $modInfo[0], _M('Module', 'admin'), 30 );
        $textField1->setReadOnly( true );
        $textField2  = new MTextField( 'moduloDescription', $modInfo[1], _M('Description', 'admin') , 30 );
        $textField3  = new MTextField( 'moduloMenu', $modInfo[2], _M('Menu Caption', 'admin'), 30 );
        $textField4  = new MTextField( 'moduloVersion', $modInfo[3], _M('Version', 'admin'), 30 );
        $textField4->setReadOnly( true );
        $textField5  = new MTextField( 'moduloMaintainer', $modInfo[4], _M('Maintainer', 'admin'), 30 );
        $textField5->setReadOnly( true );
        $textField6  = new MTextField( 'moduloHomePage', $modInfo[5], _M('Home Page', 'admin'), 30 );
        $textField6->setReadOnly( true );
        $textField7  = new MTextField( 'moduloEmail', $modInfo[6], 'Email', 30 );
        $textField7->setReadOnly( true );
        $textField8  = new MTextField( 'moduloUpdateURL', $modInfo[7], _M('Update URL', 'admin'), 30 );
        $textField8->setReadOnly( true );
        $textField9  = new HiddenField( 'mioloBase', $this->MIOLO->getAbsolutePath(),
        _M('Base Path', 'admin'), 30);
        $textField10 = new HiddenField('localFileField',$modpkg);

       $fields = array($textField1, $textField2, $textField3, $textField4, $textField5, $textField6, $textField7, $textField8, $textField9, $textField10);

       $this->setFields($fields);

       $session = $this->MIOLO->session;

       $session->setValue('installModulo', $modpkgName);

       $buttons[] = new FormButton('btnInstallModule'   , _M('Install Module' , 'admin') );

       $this->setButtons($buttons);
    }

    public function btnInstallModule_click()
    {
        $this->createXML($this->generateXML()); //salva as alteracoes no module.inf
        $this->installModule();

        try
        {
            $this->business->idModule    = MForm::getFormValue('moduleName');
            $this->business->name        = MForm::getFormValue('moduleName');
            $this->business->description = MForm::getFormValue('moduloDescription');
            $this->business->insert();

            $this->page->goto( $this->MIOLO->getActionURL($this->module,'main:modules:setup_bd') );
        }
        catch( EDatabaseException $e )
        {
            $this->addError( $e->getMessage() );
        }
    }

    public function generateXML()
    {
        $dom = new DomDocument('1.0','iso-8859-1');
        $xml = $dom->appendChild($dom->createElement('module'));

        $xml-> appendChild($dom->createElement('name',$this->getFieldValue('moduleName')));
        $xml-> appendChild($dom->createElement('description',$this->getFieldValue('moduloDescription')));
        $xml-> appendChild($dom->createElement('menu_text',$this->getFieldValue('moduloMenu')));
        $xml-> appendChild($dom->createElement('version',$this->getFieldValue('moduloVersion')));
        $xml-> appendChild($dom->createElement('maintainer',$this->getFieldValue('moduloMaintainer')));
        $xml-> appendChild($dom->createElement('home_page',$this->getFieldValue('moduloHomePage')));
        $xml-> appendChild($dom->createElement('email',$this->getFieldValue('moduloEmail')));
        $xml-> appendChild($dom->createElement('update_url',$this->getFieldValue('moduloUpdateURL')));

        $dom->formatOutput = true;

        return ($dom->saveXML());
    }

    public function createXML($xml)
    {
        global $dirModTMP;
        file_put_contents($dirModTMP."/etc/module.inf",$xml);
    }

    public function installModule()
    {
        global $modpkgName, $dirModTMP, $dirTMP;

        //dir de destino do modulo
        $dest = $this->getFieldValue('mioloBase')."/modules/".$modpkgName;

        //cria o diretorio do modulo no dir miolo/moduless
        mkdir($dest,0777);
        MUtil::copyDirectory($dirModTMP,$dest);

    }
}

?>
