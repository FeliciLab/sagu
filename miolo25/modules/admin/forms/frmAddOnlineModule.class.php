<?php

class frmAddOnlineModule extends MForm
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

        parent::__construct( _M("Available Modules on MIOLO On-Line Repository",'admin') );
        $this->setWidth('100%');
        $this->setIcon( $MIOLO->getUI()->getImage('admin', 'modules-16x16.png') );
        $url = $MIOLO->getActionURL($module, 'main:modules:add_online_module');
        $this->page->setAction($url);
        $this->defaultButton = false;
        $this->setClose( $MIOLO->getActionURL('admin', 'main') );
        $this->eventHandler();
    }

    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();

        $db = $MIOLO->getDatabase('admin');
        $sql = "select idModule from miolo_module";
        $modules = $db->query($sql);

        //verifica modulos disponiveis no repositorio
        $domMenu = new DOMDocument();
        $domMenu->load("http://www.miolo.org.br/miolo_modules.xml");
        $xpath = new DOMXPath($domMenu);
        $links = $xpath->query('menu');

        $dom = new DomDocument();
        $mainmenu = $dom->appendChild($dom->createElement('mainmenu'));

        foreach ($links as $l)
        {

            $fn = $xpath->query('name',$l);
            $modName = $fn->item(0)->firstChild->nodeValue;

            $fn = $xpath->query('version',$l);
            $modVersion = $fn->item(0)->firstChild->nodeValue;

            $fn = $xpath->query('description',$l);
            $modDescription = $fn->item(0)->firstChild->nodeValue;

            $fn = $xpath->query('required',$l);
            $modRequired = $fn->item(0)->firstChild->nodeValue;

            $fn = $xpath->query('url',$l);
            $modURLDecode = $modURL = $fn->item(0)->firstChild->nodeValue;

            ( ! $modRequired) ? ($packageRequired = true) : ($packageRequired = false);

            foreach ($modules as $m)
            {
                if ( $modName==$m[0] )
                {
                    $packageModule = true;
                }
                else
                {
                    $packageModule = false;
                }

                if ($modRequired)
                {
                    if ($modRequired==$m[0])
                    {
                        $packageRequired = true;
                    }
                    else
                    {
                        $packageRequired = false;
                    }
                }
            }

            if ($packageModule)
            {
                $moduleInstalled = new MImage('installed', null,
                $MIOLO->getUi()->getImage('admin','module_installed_16x16.png'));
                $moduleInstalled = $moduleInstalled->generate();
            }
            else
            {
                $moduleInstalled = new MImage('installed', null,
                $MIOLO->getUi()->getImage('admin','module_not_installed_16x16.png'));
                $moduleInstalled = $moduleInstalled->generate();
            }

            $installMod[] = array ( $modName, $modVersion, $modDescription,
            $modRequired, urlencode($modURL),  $moduleInstalled,
            $packageRequired, $modURLDecode);
        }


        $columns = array( new MGridColumn( _M('Module', 'admin'), 'left', true, '15%', true, null, false, true),
                          new MGridColumn( _M('Version', 'admin'), 'left', true, '10%', true, null, false, true ),
                          new MGridColumn( _M('Description', 'admin'), 'left', true, '40%', true, null, false, true ),
                          new MGridColumn( _M('Required', 'admin'), 'left', true, '15%', true, null, false, true ),
                          new MGridColumn( _M('URL', 'admin'), 'left', true, '15%', false, null, false, true ),                          
                          new MGridColumn( _M('Package', 'admin'), 'center', true, '15%', true, null, false, true ),                         
                          new MGridColumn( _M('Package Required', 'admin'), 'left', true, '15%', false, null, false, true ),
                          new MGridColumn( _M('URLDecode', 'admin'), 'left', true, '15%', false, null, false, true ) 
                            );

        $grid = new MGrid($installMod, $columns, $url,0);

        $grid->addActionIcon( _M('Install', 'admin'), 'module_add_online-16x16.png',$MIOLO->getActionURL($module,'main:modules:requisite_setup_module',null,array ('fileURL'=>'%4%', 'dependency'=>'%6%', 'modRequired'=>'%3%')));
        $grid->setLinkType('hyperlink');
        $grid->addActionIcon( _M('Download', 'admin'),'module_download-16x16.png',"javascript:document.location.href='%7%';");

        $this->addField($grid);
    }

}

?>
