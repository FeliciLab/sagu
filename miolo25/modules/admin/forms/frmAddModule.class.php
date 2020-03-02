<?php

class frmAddModule extends MForm
{
    public $home;
    public $objModule;

    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();

        $this->home      = $MIOLO->getActionURL($module, $action);
        $this->objModule = $MIOLO->getBusiness($module, 'module');

        parent::__construct( _M('Add New Module','admin') );
        $this->setWidth('70%');
        $this->setIcon( $MIOLO->getUI()->getImage('admin', 'modules-16x16.png') );
        $this->page->setAction($url);
        $this->setClose( $MIOLO->getActionURL('admin', 'main') );
        $this->eventHandler();
    }

    public function createFields()
    {  $MIOLO = MIOLO::getInstance();

       $fields = array( new MTextField( 'localFileField', '', 'Location/Filename:', 30 ),
                        //new MTextField( 'remoteFileField', '', 'Remote', 30 ),
                       );

        $remoteFileLocation = new MText('remoteFileLocation', _M('Location/Filename') . ':');
        $flds[]             = $remoteFileLocation;
        $txtLocation = new MTextField('txtLocation', $this->getFormValue('txtLocation', ''), '', 50);
        $flds[]         = $txtLocation;

        $hctDestination = new MHContainer('hctDestination', $flds);
        unset($flds);
        $fields[]       = $hctDestination;

       $this->setFields($hctDestination);

       $buttons = array( new MButton('btnAdd'   , _M('Add' , 'admin') )
                        );
       $this->setButtons($buttons);
    }

    public function  btnAdd_click()
    {
        $MIOLO = MIOLO::getInstance();
        
        $this->page->goto( $MIOLO->getActionURL($module,
        'main:modules:requisite_setup_module', null,
        array('localFileField'=>$this->getFieldValue('txtLocation'))));

    }
}

?>
