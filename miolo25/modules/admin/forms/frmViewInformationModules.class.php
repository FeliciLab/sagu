<?php

class frmViewInformationModules extends MForm
{
    public $home;
    public $objModule;
    public $chks;

    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();

        $this->home      = $MIOLO->getActionURL($module, $action);
        $this->objModule = $MIOLO->getBusiness($module, 'module');

        parent::__construct( _M('Module Information','admin') );
        $this->setWidth('100%');
        $this->setIcon( $MIOLO->getUI()->getImage('admin', 'modules-16x16.png') );
        $url = $MIOLO->getActionURL($module, 'main:rem_module_options');
        $this->page->setAction($url);
        $this->defaultButton = false;
        $this->setClose( $MIOLO->getActionURL('admin', 'main') );
        $this->eventHandler();
    }

    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();

        $moduleName = $MIOLO->_REQUEST('moduleInfo');

        $dom = new DOMDocument();
        $dom->load($MIOLO->getConf('home.modules') . '/' . $moduleName . '/etc/module.inf');

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
        $textField1  = new MTextField( 'moduloDescription', $modInfo[1], _M('Description', 'admin'), 30 );
        $textField1->setReadOnly( true );
        $textField2  = new MTextField( 'moduloMenu', $modInfo[2], _M('Menu Caption', 'admin'), 30 );
        $textField2->setReadOnly( true );
        $textField3  = new MTextField( 'moduloVersion', $modInfo[3], _M('Version'), 30 );
        $textField3->setReadOnly( true );
        $textField4  = new MTextField( 'moduloMaintainer', $modInfo[4], _M('Maintainer', 'admin'), 30 );
        $textField4->setReadOnly( true );
        $textField5  = new MTextField( 'moduloHomePage', $modInfo[5], _M('Home Page', 'admin'), 30 );
        $textField5->setReadOnly( true );
        $textField6  = new MTextField( 'moduloEmail', $modInfo[6], _M('Email', 'admin'), 30 );
        $textField6->setReadOnly( true );
        $textField7  = new MTextField( 'moduloUpdateURL', $modInfo[7], _M('Update URL', 'admin'), 30 );
        $textField7->setReadOnly( true );

        $fields = array($textField0, $textField1, $textField2, $textField3, $textField4, $textField5, $textField6, $textField7);

        $fieldsCointainer = new MHContainer('',$fields);

        $spacer = new MSpacer('5px');

        $painel = new BaseGroup('painel', _M('Information about the Module', 'admin'),array($spacer, $fieldsCointainer, $spacer),'vertical','css'); 

        $this->setFields($painel);

        $this->addField($spacer);
    }
}

?>