<?php
class frmConfOptions extends MForm
{
    public $conf;
    
    /**
     * form constructor
     */
    public function __construct($conf)
    {   
        header("Content-Type: text/html;  charset=ISO-8859-1",true);
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
//        $this->conf = $MIOLO->getConf('home.etc').'/miolo.conf';
        $this->conf = $conf;
        parent::__construct( _M('Options', $module) );
 
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
 
        $options01 = array('1'=>_M('Yes'),'0'=>_M('No'));
        $optionsTF = array('true'=>_M('Yes'),'false'=>_M('No'));

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


        $cont[] = $lblTab3[] = new MLabel(_M('Startup', $module).':');
        $cont[] = new MSelection('oStartup', $this->getFormValue('oStartup'), null, $modules);
        $cont[] = $lblDir[] = new MLabel(_M('URL Style', $module).':');
        $opUrl  = array('0'=>_M('Default'),'1'=>_M('Direct Access', $module));
        $cont[] = new MSelection('oUrlStyle', $this->getFormValue('oUrlStyle'), null, $opUrl);
        $tab3[] = new MHContainer('cont1', $cont);
        unset($cont);
        $cont[] = $lblTab3[] = new MLabel(_M('Common', $module).':');
        $cont[] = new MSelection('oCommon', $this->getFormValue('oCommon'), null, $modules);
        $cont[] = $lblDir[] = new MLabel(_M('Index file', $module).':');
        $cont[] = new MTextField('oIndex', $this->getFormValue('oIndex'), null, ' 22px');
        $tab3[] = new MHContainer('cont2', $cont);
        unset($cont);
        $cont[] = $lblTab3[] = new MLabel(_M('Autocomplete Alert', $module).':');
        $cont[] = new MSelection('oAutocomplete_alert', $this->getFormValue('oAutocomplete_alert'), null, $optionsTF);
        $cont[] = $lblDir[] = new MLabel(_M('Dispatch file', $module).':');
        $cont[] = new MTextField('oDispatch', $this->getFormValue('oDispatch'), null, '22px');
        $tab3[] = new MHContainer('cont3', $cont);
        unset($cont);
        $esq[] = new MSeparator();
        $cont[] = $lblTab3[] = new MLabel(_M('DB Session', $module).':');
        $cont[] = new MComboBox('oDbSession', $this->getFormValue('oDbSession'), null, $options01);
        $esq[] = new MHContainer('cont4', $cont);
        unset($cont);
        $cont[] = $lblTab3[] = new MLabel(_M('authMD5', $module).':');
        $cont[] = new MComboBox('oAuthMD5', $this->getFormValue('oAuthMD5'), null, $options01);
        $esq[] = new MHContainer('cont5', $cont);
        unset($cont);
        $cont[] = $lblTab3[] = new MLabel(_M('Debug', $module).':');
        $cont[] = new MComboBox('oDebug', $this->getFormValue('oDebug'), null, $options01);
        $esq[] = new MHContainer('cont6', $cont);
        unset($cont);
        $tab3c2[]  = new MVContainer('esq', $esq);

        /* Main Menu */
        $optionsMainMenu = array('1'=>_M('Panel', $module),'2'=>_M('2', $module), '3'=>_M('Top', $module));
        $cont[] = $lblMenu[] = new MLabel(_M('Type').':');
        $cont[] = new MSelection('oMainMenu', $this->getFormValue('oMainMenu'), null, $optionsMainMenu);
        $menu[] = new MHContainer('cont7', $cont);
        unset($cont);
        $cont[] = $lblMenu[] = new MLabel(_M('Style', $module).':');
        $cont[] = new MTextField('oMainMenuStyle', $this->getFormValue('oMainMenuStyle'), null, '14px');
        $menu[] = new MHContainer('cont8', $cont);
        unset($cont);
        $cont[] = $lblMenu[] = new MLabel(_M('Click to open', $module).':');
        $cont[] = new MSelection('oMainMenuClick', $this->getFormValue('oMainMenuClick'), null, $optionsTF);
        $menu[] = new MHContainer('cont9', $cont);
        unset($cont);
        $tab3c2[]  = $bgMainMenu = new MBaseGroup('bgMainMenu', _M('Main Menu', $module), $menu, 'vertical');
        $bgMainMenu->width = '230px';
        foreach( $lblMenu as $lbl )
        {
            $lbl->width = '100px';
        }
        /* Fim Main Menu */

        $tab3[]    = new MHContainer('tab3c2', $tab3c2);
        /* Dump */
        $cont[]  = $lblDump[] = new MLabel(_M('Peer', $module).':');
        $cont[]  = new MTextField('oDumpPeer', $this->getFormValue('oDumpPeer'), null, '25px');
        $dump[] = new MHContainer('cont10', $cont);
        unset($cont);
        $cont[]  = $lblDump[] = new MLabel(_M('Profile', $module).':');
        $cont[]  = new MSelection('oDumpProfile', $this->getFormValue('oDumpProfile', $module), null, $optionsTF);
        $dump[] = new MHContainer('cont11', $cont);
        unset($cont);
        $cont[]  = $lblDump[] = new MLabel(_M('Uses', $module).':');
        $cont[]  = new MSelection('oDumpUses', $this->getFormValue('oDumpUses'), null, $optionsTF);
        $dump[] = new MHContainer('cont12', $cont);
        unset($cont);
        $cont[]  = $lblDump[] = new MLabel(_M('Trace', $module).':');
        $cont[]  = new MSelection('oDumpTrace', $this->getFormValue('oDumptrace'), null, $optionsTF);
        $dump[] = new MHContainer('cont13', $cont);
        unset($cont);
        $cont[]  = $lblDump[] = new MLabel(_M('Handlers', $module).':');
        $cont[]  = new MSelection('oDumpHandlers', $this->getFormValue('oDumptHandlers'), null, $optionsTF);
        $dump[] = new MHContainer('cont14', $cont);
        unset($cont);
        $contDLP[] = $bgDump = new MBaseGroup('bgDump', _M('Dump', $module), $dump, 'vertical');
        $bgDump->width = '290px';
        foreach( $lblDump as $lbl )
        {
            $lbl->width = '65px';
        }
        /* Fim Dump */

        /* Loading */
        $cont[]    = $lblLoading[] = new MLabel(_M('Show', $module).':');
        $cont[]    = new MSelection('oLoadingShow', $this->getFormValue('oLoadingShow'), null, $optionsTF);
        $loading[] = new MHContainer('cont15', $cont);
        unset($cont);
        $cont[]    = $lblLoading[] = new MLabel(_M('Generating', $module).':');
        $cont[]    = new MSelection('oLoadingGenerating', $this->getFormValue('oLoadingGenerating'), null, $optionsTF);
        $loading[] = new MHContainer('cont16', $cont);
        unset($cont);
        $contLP[]  = $bgLoading = new MBaseGroup('bgLoading', _M('Loading', $module), $loading, 'vertical');
        $bgLoading->width  = '230px';
        foreach( $lblLoading as $lbl )
        {
            $lbl->width = '100px';
        }
        /* Fim Loading */

        /* Performance */
        $cont[] = $lblPerf[] = new MLabel(_M('uri_images', $module).':');
        $cont[] = new MSelection('oPerformanceUri_images', $this->getFormValue('oPerformanceUri_images'), null, $optionsTF);
        $perf[] = new MHContainer('cont17', $cont);
        unset($cont);
        $cont[] = $lblPerf[] = new MLabel(_M('Enable AJAX', $module).':');
        $cont[] = new MSelection('oPerformanceEnable_ajax', $this->getFormValue('oPerformanceEnable_ajax'), null, $optionsTF);
        $perf[]   = new MHContainer('cont18', $cont);
        unset($cont);
        $contLP[]  = $bgPerformance = new MBaseGroup('bgPerformance', _M('Performance', $module), $perf, 'vertical');
        $bgPerformance->width = '230px';
        foreach( $lblPerf as $lbl )
        {
            $lbl->width = '100px';
        }
        /* Fim Performance */

        $contDLP[] = new MVContainer('contLP',  $contLP);
        $tab3[]    = new MHContainer('contDLP', $contDLP);


        foreach( $lblTab3 as $lbl )
        {
            $lbl->width = '108px';
        }
        foreach( $lblDir as $lbl )
        {
            $lbl->width = '120px';
        }

        /* i18n */
        $cont[] = $lblI18n[] = new MLabel(_M('Locale Path', $module).':');
        $cont[] = $oLocale    = new MTextField('oLocale', $this->getFormValue('oLocale'), null, '25px');
        $oLocale->addAttribute('onBlur', 'javascript:reloadLanguages(this.value)');
        $i18n[] = new MHContainer('cont19', $cont); 
        unset($cont);
        /* get all languages installed */
        $localePath = $MIOLO->getConf('i18n.locale').'/';
        if(is_dir($localePath))
        {
            foreach( scandir($localePath) as $arq )
            {
                if( (substr($arq,0,1) != '.') and is_dir($localePath.$arq) )
                {
                    $languages[$arq] = $arq;
                }
            }
        }
        $cont[] = $lblI18n[] = new MLabel(_M('Language', $module).':');
        $cont[] = new MSelection('oLocaleLanguage', $this->getFormValue('oLocaleLanguage'), null, $languages);
        $cont[] = new MDiv('localeLoadingDiv', null);
        $i18n[] = new MHContainer('cont20', $cont);
        unset($cont);
        $tab3[] = $bgI18n = new MBaseGroup('bgI18n', _M('i18n', $module), $i18n, 'vertical');
        $bgI18n->width = '290px';
        foreach( $lblI18n as $lbl )
        {
            $lbl->width = '90px';
        }
        /* Fim i18n */

        $fields = new MVContainer('tab3', $tab3);
         $this->setFields($fields);
        $hidden[] = new MHiddenField('oScramble', '0');
        $hidden[] = new MHiddenField('oScramblePassword', $this->getFormValue('oScramblePassword'));
        $this->addField($hidden);

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
        
        /* options */
        if( $options = $conf->getElementsByTagName('options')->item(1) )
        {
            $this->oStartup           ->setValue($options->getElementsByTagName('startup'           )->item(0)->nodeValue);
            $this->oCommon            ->setValue($options->getElementsByTagName('common'            )->item(0)->nodeValue);
            $this->oScramble          ->setValue($options->getElementsByTagName('scramble'          )->item(0)->nodeValue);
            $this->oScramblePassword  ->setValue($options->getElementsByTagName('scramble.password' )->item(0)->nodeValue);
            $this->oDispatch          ->setValue($options->getElementsByTagName('dispatch'          )->item(0)->nodeValue);
            $this->oUrlStyle          ->setValue($options->getElementsByTagName('url.style'         )->item(0)->nodeValue);
            $this->oIndex             ->setValue($options->getElementsByTagName('index'             )->item(0)->nodeValue);
            $this->oMainMenu          ->setValue($options->getElementsByTagName('mainmenu'          )->item(0)->nodeValue);
            $this->oMainMenuStyle     ->setValue($options->getElementsByTagName('mainmenu.style'    )->item(0)->nodeValue);
            $this->oMainMenuClick     ->setValue($options->getElementsByTagName('mainmenu.clickopen')->item(0)->nodeValue);
            $this->oDbSession         ->setValue($options->getElementsByTagName('dbsession'         )->item(0)->nodeValue);
            $this->oAuthMD5           ->setValue($options->getElementsByTagName('authmd5'           )->item(0)->nodeValue);
            $this->oDebug             ->setValue($options->getElementsByTagName('debug'             )->item(0)->nodeValue);
            $this->oAutocomplete_alert->setValue($options->getElementsByTagName('autocomplete_alert')->item(0)->nodeValue);
            
            if( $oDump = $options->getElementsByTagName('dump')->item(0) )
            {
                $this->oDumpPeer    ->setValue($oDump->getElementsByTagName('peer'     )->item(0)->nodeValue);
                $this->oDumpProfile ->setValue($oDump->getElementsByTagName('profile'  )->item(0)->nodeValue);
                $this->oDumpUses    ->setValue($oDump->getElementsByTagName('uses'     )->item(0)->nodeValue);
                $this->oDumpTrace   ->setValue($oDump->getElementsByTagName('trace'    )->item(0)->nodeValue);
                $this->oDumpHandlers->setValue($oDump->getElementsByTagName('handlers' )->item(0)->nodeValue);
            }
            
            if( $oLoading = $options->getElementsByTagName('loading')->item(0) )
            {
                $this->oLoadingShow      ->setValue($oLoading->getElementsByTagName('show'      )->item(0)->nodeValue);
                $this->oLoadingGenerating->setValue($oLoading->getElementsByTagName('generating')->item(0)->nodeValue);
            }
            
            if( $oPerformance = $options->getElementsByTagName('performance')->item(0) )
            {
                $this->oPerformanceUri_images ->setValue($oPerformance->getElementsByTagName('uri_images' )->item(0)->nodeValue);
                $this->oPerformanceEnable_ajax->setValue($oPerformance->getElementsByTagName('enable_ajax')->item(0)->nodeValue);
            }

        }
        
        if( $oLocale = $conf->getElementsByTagName('i18n')->item(0) )
        {
            $this->oLocale        ->setValue($oLocale->getElementsByTagName('locale'  )->item(0)->nodeValue);
            $this->oLocaleLanguage->setValue($oLocale->getElementsByTagName('language')->item(0)->nodeValue);
        }

    }

    /**
     * Update conf array by form data
     * @param (array) conf values
     * @return (array) updated conf values
     */
    public function setConfArray($confArray)
    {
        $confArray['options.startup'                ] = $this->oStartup               ->getValue();
        $confArray['options.common'                 ] = $this->oCommon                ->getValue();
        $confArray['options.scramble'               ] = $this->oScramble              ->getValue();
        $confArray['options.scramble.password'      ] = $this->oScramblePassword      ->getValue();
        $confArray['options.dispatch'               ] = $this->oDispatch              ->getValue();
        $confArray['options.url.style'              ] = $this->oUrlStyle              ->getValue();
        $confArray['options.index'                  ] = $this->oIndex                 ->getValue();
        $confArray['options.mainmenu'               ] = $this->oMainMenu              ->getValue();
        $confArray['options.mainmenu.style'         ] = $this->oMainMenuStyle         ->getValue();
        $confArray['options.mainmenu.clickopen'     ] = $this->oMainMenuClick         ->getValue();
        $confArray['options.dbsession'              ] = $this->oDbSession             ->getValue();
        $confArray['options.authmd5'                ] = $this->oAuthMD5               ->getValue();
        $confArray['options.debug'                  ] = $this->oDebug                 ->getValue();
        $confArray['options.autocomplete_alert'     ] = $this->oAutocomplete_alert    ->getValue();
        $confArray['options.dump.peer'              ] = $this->oDumpPeer              ->getValue();
        $confArray['options.dump.profile'           ] = $this->oDumpProfile           ->getValue();
        $confArray['options.dump.uses'              ] = $this->oDumpUses              ->getValue();
        $confArray['options.dump.trace'             ] = $this->oDumpTrace             ->getValue();
        $confArray['options.dump.handlers'          ] = $this->oDumpHandlers          ->getValue();
        $confArray['options.loading.show'           ] = $this->oLoadingShow           ->getValue();
        $confArray['options.loading.generating'     ] = $this->oLoadingGenerating     ->getValue();
        $confArray['options.performance.uri_images' ] = $this->oPerformanceUri_images ->getValue();
        $confArray['options.performance.enable_ajax'] = $this->oPerformanceEnable_ajax->getValue();
        $confArray['i18n.locale'                    ] = $this->oLocale                ->getValue();
        $confArray['i18n.language'                  ] = $this->oLocaleLanguage        ->getValue();

        return $confArray;
    }

}
?>
