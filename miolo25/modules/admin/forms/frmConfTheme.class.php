<?php
class frmConfTheme extends MForm
{
    public $conf;
    
    /**
     * form constructor
     */
    public function __construct($conf)
    {   
        header("Content-Type: text/html;  charset=ISO-8859-1",true);
        $MIOLO = MIOLO::getInstance();
        $action = MIOLO::getCurrentAction();
        $module = MIOLO::getCurrentModule();
//        $this->conf = $MIOLO->getConf('home.etc').'/miolo.conf';
        $this->conf = $conf;
        parent::__construct( _M('Theme', $module) );
 
        $this->eventHandler();
        $this->loadData();
    }

    /**
     * method to create form fields
     */
    public function createFields()
    {  
        $MIOLO = MIOLO::getInstance();
        $action = MIOLO::getCurrentAction();
        $module = MIOLO::getCurrentModule();
        
        /* get installed modules */
        $db = $MIOLO->getBusiness($module, 'module');
        $rsModules = $db->listAll()->result;
        if( ! empty($rsModules) )
        {
            foreach($rsModules as $rsModule)
            {
                $modules[$rsModule[0]] = $rsModule[0];
            }
        }

        /* get list of installed themes */
        $themesPath = $MIOLO->getConf('home.themes');
        foreach( scandir($themesPath) as $dir )
        {
            if( (substr($dir,0,1) != '.') and is_dir($themesPath.'/'.$dir) )
            {
                $themesList[$dir] = $dir;
            }
        }
        /* array used in selections to show [Yes] and [No], and return [true] and [false] */
        $optionsTF = array('true'=>_M('Yes'),'false'=>_M('No'));

        $cont[]    = $lblTab2t[] = new MLabel(_M('Module', $module).':');
        $cont[]    = new MSelection('tModule', $this->getFormValue('tModule'), null, $modules);
        $tab2sel[] = new MHContainer('cont1', $cont);
        unset($cont);
        $cont[]    = $lblTab2t[] = new MLabel(_M('Main', $module).':');
        $cont[]    = new MSelection('tMain', $this->getFormValue('tMain'), null, $themesList);
        $tab2sel[] = new MHContainer('cont2', $cont);
        unset($cont);
        $cont[]    = $lblTab2t[] = new MLabel(_M('Lookup', $module).':');
        $cont[]    = new MSelection('tLookup', $this->getFormValue('tLookup'), null, $themesList);
        $tab2sel[] = new MHContainer('cont3', $cont);
        unset($cont);
        $topo[]    = new MVContainer('contTab2sel', $tab2sel);

        /* 
         * bg Options 
         */
        $cont[]    = $lblOptions[] = new MLabel(_M('Close', $module).':');
        $cont[]    = new MSelection('toClose', $this->getFormValue('toClose'), null, $optionsTF);
        $cont[]    = $lblOptions[] = new MLabel(_M('Minimize', $module).':');
        $cont[]    = new MSelection('toMinimize', $this->getFormValue('toMinimize'), null, $optionsTF);
        $options[] = new MHContainer('cont4', $cont);
        unset($cont);
        $cont[]    = $lblOptions[] = new MLabel(_M('Help', $module).':');
        $cont[]    = new MSelection('toHelp', $this->getFormValue('toHelp'), null, $optionsTF);
        $cont[]    = $lblOptions[] = new MLabel(_M('Move', $module).':');
        $cont[]    = new MSelection('toMove', $this->getFormValue('toMove'), null, $optionsTF);
        $options[] = new MHContainer('cont5', $cont);
        unset($cont);
        $topo[]    = $bgOptions = new MBaseGroup('bgThemeOptions', _M('Form Options', $module), $options, 'vertical');
        $bgOptions ->width  = '420px';

        /* set lables width */
        foreach( $lblOptions as $lbl )
        {
            $lbl->width = '80px';
        }
        /*
         * fim bg Options 
         */

        $fields[]  = new MHContainer('contTopo', $topo);

        $cont[]   = $lblTab2t[] = new MLabel(_M('Title', $module).':');
        $cont[]   = new MTextField('tTitle', $this->getFormValue('tTitle'), null, '60px');
        $fields[] = new MHContainer('cont6', $cont);
        unset($cont);
        $cont[]   = $lblTab2t[] = new MLabel(_M('Company', $module).':');
        $cont[]   = new MTextField('tCompany', $this->getFormValue('tCompany'), null, '60px');
        $fields[] = new MHContainer('cont7', $cont);
        unset($cont);
        $cont[]   = $lblTab2t[] = new MLabel(_M('System', $module).':');
        $cont[]   = new MTextField('tSystem', $this->getFormValue('tSystem'), null, '60px');
        $fields[] = new MHContainer('cont8', $cont);
        unset($cont);
        $cont[]   = $lblTab2t[] = new MLabel(_M('Logo Path', $module).':');
        $cont[]   = new MTextField('tLogo', $this->getFormValue('tLogo'), null, '40px');
        $fields[] = new MHContainer('cont9', $cont);
        unset($cont);
        $cont[]   = $lblTab2t[] = new MLabel(_M('Email', $module).':');
        $cont[]   = new MTextField('tEmail', $this->getFormValue('tEmail'), null, '30px');
        $fields[] = new MHContainer('cont10', $cont);
        unset($cont);
        
        /* set lables width */
        foreach( $lblTab2t as $lbl )
        {
            $lbl->width = '100px';
        }

        $fields = new MVContainer('contTab2', $fields);
        
        $this->setFields($fields);
        
        $version = new MTextField('version', MIOLO_VERSION, null, 15);
        $version->setReadOnly(true);
        $this->addField($version);

        $this->defaultButton = false;

    }

    /**
     * load form data from configuration file
     */
    public function loadData()
    {
        $MIOLO = MIOLO::getInstance();
        $dom = new DOMDocument();
        $dom->load($this->conf);
        $conf = $dom->getElementsByTagName('conf')->item(0);
        
        /* theme */
        if( $theme = $conf->getElementsByTagName('theme')->item(0) )
        {
            $this->tModule  ->setValue($theme->getElementsByTagName('module' )->item(0)->nodeValue);
            $this->tMain    ->setValue($theme->getElementsByTagName('main'   )->item(0)->nodeValue);
            $this->tLookup  ->setValue($theme->getElementsByTagName('lookup' )->item(0)->nodeValue);
            $this->tTitle   ->setValue($theme->getElementsByTagName('title'  )->item(0)->nodeValue);
            $this->tCompany ->setValue($theme->getElementsByTagName('company')->item(0)->nodeValue);
            $this->tSystem  ->setValue($theme->getElementsByTagName('system' )->item(0)->nodeValue);
            $this->tLogo    ->setValue($theme->getElementsByTagName('logo'   )->item(0)->nodeValue);
            $this->tEmail   ->setValue($theme->getElementsByTagName('email'  )->item(0)->nodeValue);
            
            /* options */
            if( $tOptions = $theme->getElementsByTagName('options')->item(0) )
            {
                $this->toClose    ->setValue($tOptions->getElementsByTagName('close'   )->item(0)->nodeValue);
                $this->toMinimize ->setValue($tOptions->getElementsByTagName('minimize')->item(0)->nodeValue);
                $this->toHelp     ->setValue($tOptions->getElementsByTagName('help'    )->item(0)->nodeValue);
                $this->toMove     ->setValue($tOptions->getElementsByTagName('move'    )->item(0)->nodeValue);
            }
        }

    }

    /**
     * Update conf array by form data
     * @param (array) conf values
     * @return (array) updated conf values
     */
    public function setConfArray($confArray)
    {
        $confArray['theme.module'          ] = $this->tModule    ->getValue();
        $confArray['theme.main'            ] = $this->tMain      ->getValue();
        $confArray['theme.lookup'          ] = $this->tLookup    ->getValue();
        $confArray['theme.title'           ] = $this->tTitle     ->getValue();
        $confArray['theme.company'         ] = $this->tCompany   ->getValue();
        $confArray['theme.system'          ] = $this->tSystem    ->getValue();
        $confArray['theme.logo'            ] = $this->tLogo      ->getValue();
        $confArray['theme.email'           ] = $this->tEmail     ->getValue();
        $confArray['theme.options.close'   ] = $this->toClose    ->getValue();
        $confArray['theme.options.minimize'] = $this->toMinimize ->getValue();
        $confArray['theme.options.help'    ] = $this->toHelp     ->getValue();
        $confArray['theme.options.move'    ] = $this->toMove     ->getValue();

        return $confArray;
    }

}
?>
