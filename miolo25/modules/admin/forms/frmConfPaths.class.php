<?php
class frmConfPaths extends MForm
{
    public $conf;

    /**
     * form constructor
     */
    public function __construct($conf)
    {   
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        header("Content-Type: text/html;  charset=ISO-8859-1",true);
//        $this->conf = $MIOLO->getConf('home.etc').'/miolo.conf';
        $this->conf = $conf;
        parent::__construct( _M('Paths', $module) );
 
        $this->eventHandler();
        $this->loadData();
    }

    
    /**
     * method to create form fields
     */
    public function createFields()
    {  
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        
        $pathFieldsWidth = '35px';
        $cont[]    = new MSeparator();
        $cont[]    = $lblPaths[] = new MLabel(_M('Miolo', $module).':');
        $cont[]    = new MTextField('homeMiolo', $this->getFormValue('homeMiolo'), null, $pathFieldsWidth);
        $cont[]    = $lblPaths[] = new MLabel(_M('Classes', $module).':');
        $cont[]    = new MTextField('homeClasses', $this->getFormValue('homeClasses'), null, $pathFieldsWidth);
        $bgPaths[] = new MHContainer('cont1', $cont);
        unset($cont);
        $cont[]    = $lblPaths[] = new MLabel(_M('Modules', $module).':');
        $cont[]    = new MTextField('homeModules', $this->getFormValue('homeModules'), null, $pathFieldsWidth);
        $cont[]    = $lblPaths[] = new MLabel(_M('Etc', $module).':');
        $cont[]    = new MTextField('homeEtc', $this->getFormValue('homeEtc'), null, $pathFieldsWidth);
        $bgPaths[] = new MHContainer('cont2', $cont);
        unset($cont);
        $cont[]    = $lblPaths[] = new MLabel(_M('Logs', $module).':');
        $cont[]    = new MTextField('homeLogs', $this->getFormValue('homeLogs'), null, $pathFieldsWidth);
        $cont[]    = $lblPaths[] = new MLabel(_M('Trace', $module).':');
        $cont[]    = new MTextField('homeTrace', $this->getFormValue('homeTrace'), null, $pathFieldsWidth);
        $bgPaths[] = new MHContainer('cont3', $cont);
        unset($cont);
        $cont[]    = $lblPaths[] = new MLabel(_M('DB', $module).':');
        $cont[]    = new MTextField('homeDb', $this->getFormValue('homeDb'), null, $pathFieldsWidth);
        $cont[]    = $lblPaths[] = new MLabel(_M('HTML', $module).':');
        $cont[]    = new MTextField('homeHtml', $this->getFormValue('homeHTML'), null, $pathFieldsWidth);
        $bgPaths[] = new MHContainer('cont4', $cont);
        unset($cont);
        $cont[]    = $lblPaths[] = new MLabel(_M('Themes', $module).':');
        $cont[]    = new MTextField('homeThemes', $this->getFormValue('homeThemes'), null, $pathFieldsWidth);
        $cont[]    = $lblPaths[] = new MLabel(_M('Extensions', $module).':');
        $cont[]    = new MTextField('homeExtensions', $this->getFormValue('homeExtensions'), null, $pathFieldsWidth);
        $bgPaths[] = new MHContainer('cont5', $cont);
        unset($cont);
        $cont[]    = $lblPaths[] = new MLabel(_M('Reports', $module).':');
        $cont[]    = new MTextField('homeReports', $this->getFormValue('homeReports'), null, $pathFieldsWidth);
        $cont[]    = $lblPaths[] = new MLabel(_M('Images', $module).':');
        $cont[]    = new MTextField('homeImages', $this->getFormValue('homeImages'), null, $pathFieldsWidth);
        $bgPaths[] = new MHContainer('cont6', $cont);
        unset($cont);
        $cont[]    = $lblPaths[] = new MLabel(_M('URL', $module).':');
        $cont[]    = new MTextField('homeUrl', $this->getFormValue('homeURL'), null, $pathFieldsWidth);
        $cont[]    = $lblPaths[] = new MLabel(_M('URL themes', $module).':');
        $cont[]    = new MTextField('homeUrl_themes', $this->getFormValue('homeURLThemes'), null, $pathFieldsWidth);
        $bgPaths[] = new MHContainer('cont7', $cont);
        unset($cont);
        $cont[]    = $lblPaths[] = new MLabel(_M('URL reports', $module).':');
        $cont[]    = new MTextField('homeUrl_reports', $this->getFormValue('homeURLReports'), null, $pathFieldsWidth);
        $cont[]    = $lblPaths[] = new MLabel(_M('Module Themes', $module).':');
        $cont[]    = new MTextField('homeModule_themes', $this->getFieldValue('homeModule_themes'), null, $pathFieldsWidth);
        $bgPaths[] = new MHContainer('cont8', $cont);
        unset($cont);
        $cont[]    = $lblPaths[] = new MLabel(_M('Module HTML', $module).':');
        $cont[]    = new MTextField('homeModule_html', $this->getFormValue('homeModule_htmL'), null, $pathFieldsWidth);
        $cont[]    = $lblPaths[] = new MLabel(_M('Module Images', $module).':');
        $cont[]    = new MTextField('homeModule_images', $this->getFormValue('homeModule_images'), null, $pathFieldsWidth);
        $bgPaths[] = new MHContainer('cont9', $cont);
        unset($cont);

        $bgs[] = $bgHome = new MBaseGroup('bgHome', _M('Paths', $module), $bgPaths, 'vertical');
        $bgHome->width = '99%';
        /* set lables width */
        foreach( $lblPaths as $lbl )
        {
            $lbl->width = '109px';
        }
        
        $cont[] = new MSeparator();
        $cont[] = $lblNS[] = $lblNSCore = new MLabel(_M('Core', $module).':');
        $cont[] = new MTextField('nsCore', $this->getFormValue('nsCore'), null, $pathFieldsWidth);
        $cont[] = $lblNS[] = $lblNSService = new MLabel(_M('Service', $module).':');
        $cont[] = new MTextField('nsService', $this->getFormValue('nsService'), null, $pathFieldsWidth);
        $bgNS[] = new MHContainer('cont10', $cont);
        unset($cont);
        $cont[] = $lblNS[] = $lblNSUi = new MLabel(_M('UI', $module).':');
        $cont[] = new MTextField('nsUi', $this->getFormValue('nsUi'), null, $pathFieldsWidth);
        $cont[] = $lblNS[] = $lblNSThemes = new MLabel(_M('Themes', $module).':');
        $cont[] = new MTextField('nsThemes', $this->getFormValue('nsThemes'), null, $pathFieldsWidth);
        $bgNS[] = new MHContainer('cont11', $cont);
        unset($cont);
        $cont[] = $lblNS[] = $lblNSExtensions = new MLabel(_M('Extensions', $module).':');
        $cont[] = new MTextField('nsExtensions', $this->getFormValue('nsExtensions'), null, $pathFieldsWidth);
        $cont[] = $lblNS[] = $lblNSControls = new MLabel(_M('Controls', $module).':');
        $cont[] = new MTextField('nsControls', $this->getFormValue('nsControls'), null, $pathFieldsWidth);
        $bgNS[] = new MHContainer('cont12', $cont);
        unset($cont);
        $cont[] = $lblNS[] = $lblNSDatabase = new MLabel(_M('Database', $module).':');
        $cont[] = new MTextField('nsDatabase', $this->getFormValue('nsDatabase'), null, $pathFieldsWidth);
        $cont[] = $lblNS[] = $lblNSUtils = new MLabel(_M('Utils', $module).':');
        $cont[] = new MTextField('nsUtils', $this->getFormValue('nsUtils'), null, $pathFieldsWidth);
        $bgNS[] = new MHContainer('cont13', $cont);
        unset($cont);
        $cont[] = $lblNS[] = $lblNSModules = new MLabel(_M('Modules', $module).':');
        $cont[] = new MTextField('nsModules', $this->getFormValue('nsModules'), null, $pathFieldsWidth);
        $bgNS[] = new MHContainer('cont14', $cont);

        $bgs[]  = $bgNamespace = new MBaseGroup('bgNamespace', _M('Namespace', $module), $bgNS, 'vertical');
        $bgNamespace->width = '99%';
        /* set lables width */
        foreach( $lblNS as $lbl )
        {
            $lbl->width = '100px';
        }

        $fields  = new MVContainer('tab1', $bgs);
        $this->setFields($fields);
        $this->defaultButton = false;
        
        $version = new MTextField('version', MIOLO_VERSION, null, 15);
        $version->setReadOnly(true);
        $this->addField($version);

        $validators[] = new MRequiredValidator('homeMiolo'   , null, null, _M('Field "Home/Miolo" in tab Path must be informed.'));
        $validators[] = new MRequiredValidator('homeClasses' , null, null, _M('Field "Home/Classes" in tab Path must be informed.'));
        $this->setValidators($validators);
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
        
        /* home */
        if( $home = $conf->getElementsByTagName('home')->item(0) )
        {
            $this->homeMiolo         ->setValue($home->getElementsByTagName('miolo'        )->item(0)->nodeValue);
            $this->homeClasses       ->setValue($home->getElementsByTagName('classes'      )->item(0)->nodeValue);
            $this->homeModules       ->setValue($home->getElementsByTagName('modules'      )->item(0)->nodeValue);
            $this->homeEtc           ->setValue($home->getElementsByTagName('etc'          )->item(0)->nodeValue);
            $this->homeLogs          ->setValue($home->getElementsByTagName('logs'         )->item(0)->nodeValue);
            $this->homeTrace         ->setValue($home->getElementsByTagName('trace'        )->item(0)->nodeValue);
            $this->homeDb            ->setValue($home->getElementsByTagName('db'           )->item(0)->nodeValue);
            $this->homeHtml          ->setValue($home->getElementsByTagName('html'         )->item(0)->nodeValue);
            $this->homeThemes        ->setValue($home->getElementsByTagName('themes'       )->item(0)->nodeValue);
            $this->homeExtensions    ->setValue($home->getElementsByTagName('extensions'   )->item(0)->nodeValue);
            $this->homeReports       ->setValue($home->getElementsByTagName('reports'      )->item(0)->nodeValue);
            $this->homeImages        ->setValue($home->getElementsByTagName('images'       )->item(0)->nodeValue);
            $this->homeUrl           ->setValue($home->getElementsByTagName('url'          )->item(0)->nodeValue);
            $this->homeUrl_themes    ->setValue($home->getElementsByTagName('url_themes'   )->item(0)->nodeValue);
            $this->homeUrl_reports   ->setValue($home->getElementsByTagName('url_reports'  )->item(0)->nodeValue);
            $this->homeModule_themes ->setValue($home->getElementsByTagName('module.themes')->item(0)->nodeValue);
            $this->homeModule_html   ->setValue($home->getElementsByTagName('module.html'  )->item(0)->nodeValue);
            $this->homeModule_images ->setValue($home->getElementsByTagName('module.images')->item(0)->nodeValue);
        }
   
        /* namespace */
        if( $namespace = $conf->getElementsByTagName('namespace')->item(0) )
        {
            $this->nsCore       ->setValue($namespace->getElementsByTagName('core'      )->item(0)->nodeValue);
            $this->nsService    ->setValue($namespace->getElementsByTagName('service'   )->item(0)->nodeValue);
            $this->nsUi         ->setValue($namespace->getElementsByTagName('ui'        )->item(0)->nodeValue);
            $this->nsThemes     ->setValue($namespace->getElementsByTagName('themes'    )->item(0)->nodeValue);
            $this->nsExtensions ->setValue($namespace->getElementsByTagName('extensions')->item(0)->nodeValue);
            $this->nsControls   ->setValue($namespace->getElementsByTagName('controls'  )->item(0)->nodeValue);
            $this->nsDatabase   ->setValue($namespace->getElementsByTagName('database'  )->item(0)->nodeValue);
            $this->nsUtils      ->setValue($namespace->getElementsByTagName('utils'     )->item(0)->nodeValue);
            $this->nsModules    ->setValue($namespace->getElementsByTagName('modules'   )->item(0)->nodeValue);
        }

    }

    /**
     * Update conf array by form data
     * @param (array) conf values
     * @return (array) updated conf values
     */
    public function setConfArray($confArray)
    {
        $confArray['home.miolo'          ] = $this->homeMiolo        ->getValue();
        $confArray['home.classes'        ] = $this->homeClasses      ->getValue();
        $confArray['home.modules'        ] = $this->homeModules      ->getValue();
        $confArray['home.etc'            ] = $this->homeEtc          ->getValue();
        $confArray['home.logs'           ] = $this->homeLogs         ->getValue();
        $confArray['home.trace'          ] = $this->homeTrace        ->getValue();
        $confArray['home.db'             ] = $this->homeDb           ->getValue();
        $confArray['home.html'           ] = $this->homeHtml         ->getValue();
        $confArray['home.themes'         ] = $this->homeThemes       ->getValue();
        $confArray['home.extensions'     ] = $this->homeExtensions   ->getValue();
        $confArray['home.reports'        ] = $this->homeReports      ->getValue();
        $confArray['home.images'         ] = $this->homeImages       ->getValue();
        $confArray['home.url'            ] = $this->homeUrl          ->getValue();
        $confArray['home.url_themes'     ] = $this->homeUrl_themes   ->getValue();
        $confArray['home.url_reports'    ] = $this->homeUrl_reports  ->getValue();
        $confArray['home.module.themes'  ] = $this->homeModule_themes->getValue();
        $confArray['home.module.html'    ] = $this->homeModule_html  ->getValue();
        $confArray['home.module.images'  ] = $this->homeModule_images->getValue();
        $confArray['namespace.core'      ] = $this->nsCore           ->getValue();
        $confArray['namespace.service'   ] = $this->nsService        ->getValue();
        $confArray['namespace.ui'        ] = $this->nsUi             ->getValue();
        $confArray['namespace.themes'    ] = $this->nsThemes         ->getValue();
        $confArray['namespace.extensions'] = $this->nsExtensions     ->getValue();
        $confArray['namespace.controls'  ] = $this->nsControls       ->getValue();
        $confArray['namespace.database'  ] = $this->nsDatabase       ->getValue();
        $confArray['namespace.utils'     ] = $this->nsUtils          ->getValue();
        $confArray['namespace.modules'   ] = $this->nsModules        ->getValue();
        return $confArray;
    }

}
?>
