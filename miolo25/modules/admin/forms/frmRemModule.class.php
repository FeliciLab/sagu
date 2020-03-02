<?php

class frmRemModule extends MForm
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

        parent::__construct( _M('Remove Modules','admin') );
        $this->setWidth('100%');
        $this->setIcon( $MIOLO->getUI()->getImage('admin', 'modules-16x16.png') );
        $url = $MIOLO->getActionURL($module, 'main:rem_module');
        $this->page->setAction($url);
        $this->defaultButton = false;
        $this->setClose( $MIOLO->getActionURL('admin', 'main') );
        $this->eventHandler();
    }

    public function createFields()
    {  $MIOLO = MIOLO::getInstance();

        $db = $MIOLO->getDatabase('admin');
        $sql = "select idModule from miolo_module";
        $modules = $db->query($sql);

        foreach ($modules as $m)
        {
            //le informacoes do modulo no xml module.inf
            $dom = new DomDocument();
            $dom->load($MIOLO->getConf('home.modules') . '/' . $m[0] .'/etc/module.inf');

            $modInfo[0]=$dom->getElementsByTagName('name')->item(0)->nodeValue;
            $modInfo[1]=$dom->getElementsByTagName('version')->item(0)->nodeValue;
            $modInfo[2]=$dom->getElementsByTagName('description')->item(0)->nodeValue;

            $fields[] = array ( $modInfo[0], $modInfo[1], $modInfo[2] );
        }

        $columns = array( new MGridColumn( _M('Module', 'admin'), 'left', true, '20%', true, null, false, true),
                          new MGridColumn( _M('Version', 'admin'), 'left', true, '15%', true, null, false, true ),
                          new MGridColumn( _M('Description', 'admin'), 'left', true, '65%', true, null, false, true ),
                            );

        $grid = new MGrid($fields, $columns, $url,0);
        $grid->addActionIcon('del','close.png',$MIOLO->getActionURL($module,'main:modules:rem_modules:rem_module_options',null,array ('moduleToDelete'=>'%0%')));
        $grid->addActionIcon('view','information16.png',$MIOLO->getActionURL($module,'main:modules:rem_modules:view_information_modules',null,array ('moduleInfo'=>'%0%')));
        $this->addField($grid);
    }

}

?>
