<?php
class frmConf extends MForm
{
    public $conf, $isModule;

    /**
     * form constructor
     */
    public function __construct()
    {   
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();

        /** 
         * get module to be configured from _REQUEST and set path
         */
        $confModule = MIOLO::_REQUEST('confModule');

        if( $confModule and $confModule != 'GLOBAL' )
        {
            $this->isModule = true;
            $this->conf     = $MIOLO->getConf('home.modules').'/'.$confModule.'/etc/module.conf';
            $bkpFileName    = 'module.conf';
        }
        else
        {
            $this->conf     = $MIOLO->getConf('home.etc').'/miolo.conf';
            $this->isModule = false;
            $bkpFileName    = 'miolo.conf';
        }

        /* backup the conf file */
        $time   = date('Ymd-Hi');
        $bkpDir = MUtil::getSystemTempDir();
        $bkpFileName = $bkpDir . '/'. $bkpFileName .'-bkp_'.$time;

        if( ! @copy($this->conf, $bkpFileName) )
        {
            $MIOLO->information(_M("Backup failed. It wasn't possible to create a backup file [@1] of your current configuration!", 'admin', $bkpFileName), null, null, false);
        }
        else
        {
            $MIOLO->getTheme()->insertContent( MPrompt::information( _M("Backup file [@1] created.",'admin', $bkpFileName), null ) );
        }

        /* permission test */
        if( ! is_writable($this->conf) )
        {
            $this->addError( _M("READ-ONLY MODE!<br/>You don't have permission to write the configuration file:<br/> [@1]", 'admin', $this->conf) );
        }

        parent::__construct( _M('Configuration', $module) );

        $this->eventHandler();
    }
    
    /**
     * method to create form fields
     */
    public function createFields()
    {  
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        
        $db = $MIOLO->getBusiness($module, 'module');
        $rsModules = $db->listAll()->result;
        $modules['miolo'] = 'GLOBAL';
        if( ! empty($rsModules) )
        {
            foreach($rsModules as $rsModule)
            {
                $modules[$rsModule[0]] = $rsModule[0];
            }
        }
        $confModule = 'miolo';
        if( MIOLO::_REQUEST('confModule') )
        {
            $confModule = MIOLO::_REQUEST('confModule');
        }
        $fields[0] = new MSelection('confModule', $confModule, _M('Configuration', $module), $modules);
        $url = $MIOLO->getActionUrl($module, $action);
        
        $fields[1] = new MTabbedBaseGroup('configBsg');
        
        $fields[1]->createTab('paths', _M('Paths'), array($this->manager->getUi()->getForm('admin', 'frmConfPaths', $this->conf)));
        $fields[1]->createTab('theme', _M('Theme'), array($this->manager->getUi()->getForm('admin', 'frmConfTheme', $this->conf)));
        $fields[1]->createTab('options', _M('Options'), array($this->manager->getUi()->getForm('admin', 'frmConfOptions', $this->conf)));
        $fields[1]->createTab('admin', _M('Administration'), array($this->manager->getUi()->getForm('admin', 'frmConfAdmin', $this->conf)));
        $fields[1]->createTab('db', _M('DB'), array($this->manager->getUi()->getForm('admin', 'frmConfDb', $this->conf)));
        
        $this->setShowPostButton(false);
        $this->setFields($fields);

        $button = new MButton('btnSave', _M('Save',$module), "javascript:save();");
        $this->addField($button); 

    }
    
    /**
     * Save form data at the conf file
     * @returns (boolean) true if success
     */
    public function saveTab($formValues)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        /* get tab and remove from array */
        $thisTab    = $formValues[0];
        $formValues = $formValues[1];

        /* manipulate form values */
        $formValues = explode('&', $formValues);
        foreach( $formValues as $value )
        {
            $aux = explode('=', $value);
            $data->$aux[0] = urldecode($aux[1]);
        }
        /* it's data ok? */
        if( !$data->version )
        {
            echo 'false';
            return;
        }
        /* get form */
        $form = $MIOLO->getUi()->getForm($module, 'frmConf'.$thisTab, $this->conf);
        /* set data */
        $form->setData($data);

        /* get old conf, set new values, generate xml and save the the file */
        $confArray    = $this->getConfArray($this->conf);
        $newConfArray = $form->setConfArray($confArray);
//        $xml = $this->generateXml($newConfArray);
        $xml = $MIOLO->conf->generateConfigXML($newConfArray);
//        return file_put_contents('/home/miolo2/etc/miolo-saved.conf', $xml);
        echo file_put_contents($this->conf, $xml);
    }

    public function getTab($tab)
    {
        global $theme;
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUi();
        $tab = $tab[0];
        $form = $ui->getForm($module, 'frmConf'.$tab, $this->conf);
        echo $form->generate();
    }

    /**
     * Get array with conf values
     * @params conf file path
     * @returns (array) with the conf data
     */
    public function getConfArray($conf)
    {
        $MIOLO = MIOLO::getInstance();
        $dom = new DOMDocument();
        $dom->load($conf);
        $conf = $dom->getElementsByTagName('conf')->item(0);
        
        /* home */
        $home = $conf->getElementsByTagName('home')->item(0);
        if( $home )
        {
            $confArray['home.miolo'        ] = $home->getElementsByTagName('miolo'        )->item(0)->nodeValue;
            $confArray['home.classes'      ] = $home->getElementsByTagName('classes'      )->item(0)->nodeValue;
            $confArray['home.modules'      ] = $home->getElementsByTagName('modules'      )->item(0)->nodeValue;
            $confArray['home.etc'          ] = $home->getElementsByTagName('etc'          )->item(0)->nodeValue;
            $confArray['home.logs'         ] = $home->getElementsByTagName('logs'         )->item(0)->nodeValue;
            $confArray['home.trace'        ] = $home->getElementsByTagName('trace'        )->item(0)->nodeValue;
            $confArray['home.db'           ] = $home->getElementsByTagName('db'           )->item(0)->nodeValue;
            $confArray['home.html'         ] = $home->getElementsByTagName('html'         )->item(0)->nodeValue;
            $confArray['home.themes'       ] = $home->getElementsByTagName('themes'       )->item(0)->nodeValue;
            $confArray['home.extensions'   ] = $home->getElementsByTagName('extensions'   )->item(0)->nodeValue;
            $confArray['home.reports'      ] = $home->getElementsByTagName('reports'      )->item(0)->nodeValue;
            $confArray['home.images'       ] = $home->getElementsByTagName('images'       )->item(0)->nodeValue;
            $confArray['home.url'          ] = $home->getElementsByTagName('url'          )->item(0)->nodeValue;
            $confArray['home.url_themes'   ] = $home->getElementsByTagName('url_themes'   )->item(0)->nodeValue;
            $confArray['home.url_reports'  ] = $home->getElementsByTagName('url_reports'  )->item(0)->nodeValue;
            $confArray['home.module.themes'] = $home->getElementsByTagName('module.themes')->item(0)->nodeValue;
            $confArray['home.module.html'  ] = $home->getElementsByTagName('module.html'  )->item(0)->nodeValue;
            $confArray['home.module.images'] = $home->getElementsByTagName('module.images')->item(0)->nodeValue;
        }
   
        /* namespace */
        $namespace = $conf->getElementsByTagName('namespace')->item(0);
        if( $namespace )
        {
            $confArray['namespace.core'      ] = $namespace->getElementsByTagName('core'      )->item(0)->nodeValue;
            $confArray['namespace.service'   ] = $namespace->getElementsByTagName('service'   )->item(0)->nodeValue;
            $confArray['namespace.ui'        ] = $namespace->getElementsByTagName('ui'        )->item(0)->nodeValue;
            $confArray['namespace.themes'    ] = $namespace->getElementsByTagName('themes'    )->item(0)->nodeValue;
            $confArray['namespace.extensions'] = $namespace->getElementsByTagName('extensions')->item(0)->nodeValue;
            $confArray['namespace.controls'  ] = $namespace->getElementsByTagName('controls'  )->item(0)->nodeValue;
            $confArray['namespace.database'  ] = $namespace->getElementsByTagName('database'  )->item(0)->nodeValue;
            $confArray['namespace.utils'     ] = $namespace->getElementsByTagName('utils'     )->item(0)->nodeValue;
            $confArray['namespace.modules'   ] = $namespace->getElementsByTagName('modules'   )->item(0)->nodeValue;
        }

        /* theme */
        $theme = $conf->getElementsByTagName('theme')->item(0);
        if( $theme )
        {
            $confArray['theme.module' ] = $theme->getElementsByTagName('module' )->item(0)->nodeValue;
            $confArray['theme.main'   ] = $theme->getElementsByTagName('main'   )->item(0)->nodeValue;
            $confArray['theme.lookup' ] = $theme->getElementsByTagName('lookup' )->item(0)->nodeValue;
            $confArray['theme.title'  ] = $theme->getElementsByTagName('title'  )->item(0)->nodeValue;
            $confArray['theme.company'] = $theme->getElementsByTagName('company')->item(0)->nodeValue;
            $confArray['theme.system' ] = $theme->getElementsByTagName('system' )->item(0)->nodeValue;
            $confArray['theme.logo'   ] = $theme->getElementsByTagName('logo'   )->item(0)->nodeValue;
            $confArray['theme.email'  ] = $theme->getElementsByTagName('email'  )->item(0)->nodeValue;
            $tOptions = $theme->getElementsByTagName('options')->item(0);
            if( $tOptions )
            {
                $confArray['theme.options.close'   ] = $tOptions->getElementsByTagName('close'   )->item(0)->nodeValue;
                $confArray['theme.options.minimize'] = $tOptions->getElementsByTagName('minimize')->item(0)->nodeValue;
                $confArray['theme.options.help'    ] = $tOptions->getElementsByTagName('help'    )->item(0)->nodeValue;
                $confArray['theme.options.move'    ] = $tOptions->getElementsByTagName('move'    )->item(0)->nodeValue;
            }

        }
        /* options */
        $options = $conf->getElementsByTagName('options')->item(1);
        !$options ? $options = $conf->getElementsByTagName('options')->item(0) : null;
        if( $options )
        {
            $confArray['options.startup'           ] = $options->getElementsByTagName('startup'           )->item(0)->nodeValue;
            $confArray['options.common'            ] = $options->getElementsByTagName('common'            )->item(0)->nodeValue;
            $confArray['options.scramble'          ] = $options->getElementsByTagName('scramble'          )->item(0)->nodeValue;
            $confArray['options.scramble.password' ] = $options->getElementsByTagName('scramble.password' )->item(0)->nodeValue;
            $confArray['options.dispatch'          ] = $options->getElementsByTagName('dispatch'          )->item(0)->nodeValue;
            $confArray['options.url.style'         ] = $options->getElementsByTagName('url.style'         )->item(0)->nodeValue;
            $confArray['options.index'             ] = $options->getElementsByTagName('index'             )->item(0)->nodeValue;
            $confArray['options.mainmenu'          ] = $options->getElementsByTagName('mainmenu'          )->item(0)->nodeValue;
            $confArray['options.mainmenu.style'    ] = $options->getElementsByTagName('mainmenu.style'    )->item(0)->nodeValue;
            $confArray['options.mainmenu.clickopen'] = $options->getElementsByTagName('mainmenu.clickopen')->item(0)->nodeValue;
            $confArray['options.dbsession'         ] = $options->getElementsByTagName('dbsession'         )->item(0)->nodeValue;
            $confArray['options.authmd5'           ] = $options->getElementsByTagName('authmd5'           )->item(0)->nodeValue;
            $confArray['options.debug'             ] = $options->getElementsByTagName('debug'             )->item(0)->nodeValue;
            $confArray['options.autocomplete_alert'] = $options->getElementsByTagName('autocomplete_alert')->item(0)->nodeValue;

            $oDump = $options->getElementsByTagName('dump')->item(0);
            if( $oDump )
            {
                $confArray['options.dump.peer'    ] = $oDump->getElementsByTagName('peer'     )->item(0)->nodeValue;
                $confArray['options.dump.profile' ] = $oDump->getElementsByTagName('profile'  )->item(0)->nodeValue;
                $confArray['options.dump.uses'    ] = $oDump->getElementsByTagName('uses'     )->item(0)->nodeValue;
                $confArray['options.dump.trace'   ] = $oDump->getElementsByTagName('trace'    )->item(0)->nodeValue;
                $confArray['options.dump.handlers'] = $oDump->getElementsByTagName('handlers' )->item(0)->nodeValue;
            }
            $oLoading = $options->getElementsByTagName('loading')->item(0);
            if( $oLoading )
            {
                $confArray['options.loading.show'      ] = $oLoading->getElementsByTagName('show'      )->item(0)->nodeValue;
                $confArray['options.loading.generating'] = $oLoading->getElementsByTagName('generating')->item(0)->nodeValue;
            }
            $oPerformance = $options->getElementsByTagName('performance')->item(0);
            if( $oPerformance )
            {
                $confArray['options.performance.uri_images' ] = $oPerformance->getElementsByTagName('uri_images' )->item(0)->nodeValue;
                $confArray['options.performance.enable_ajax'] = $oPerformance->getElementsByTagName('enable_ajax')->item(0)->nodeValue;
            }

        }
        $oLocale = $conf->getElementsByTagName('i18n')->item(0);
        if( $oLocale )
        {
            $confArray['i18n.locale'  ] = $oLocale->getElementsByTagName('locale'  )->item(0)->nodeValue;
            $confArray['i18n.language'] = $oLocale->getElementsByTagName('language')->item(0)->nodeValue;
        }

        /* administration */
        $mad = $conf->getElementsByTagName('mad')->item(0);
        if( $mad )
        {
            $confArray['mad.module'] = $mad->getElementsByTagName('module'     )->item(0)->nodeValue;
            $madClasses = $mad->getElementsByTagName('classes')->item(0);
            if( $madClasses )
            {
                $confArray['mad.classes.access'     ] = $madClasses->getElementsByTagName('access'     )->item(0)->nodeValue;
                $confArray['mad.classes.group'      ] = $madClasses->getElementsByTagName('group'      )->item(0)->nodeValue;
                $confArray['mad.classes.log'        ] = $madClasses->getElementsByTagName('log'        )->item(0)->nodeValue;
                $confArray['mad.classes.session'    ] = $madClasses->getElementsByTagName('session'    )->item(0)->nodeValue;
                $confArray['mad.classes.transaction'] = $madClasses->getElementsByTagName('transaction')->item(0)->nodeValue;
                $confArray['mad.classes.user'       ] = $madClasses->getElementsByTagName('user'       )->item(0)->nodeValue;
            }
        }

        $login = $conf->getElementsByTagName('login')->item(0);
        if( $login )
        {
            $confArray['login.module'] = $login->getElementsByTagName('module')->item(0)->nodeValue;
            $confArray['login.class' ] = $login->getElementsByTagName('class' )->item(0)->nodeValue;
            $confArray['login.check' ] = $login->getElementsByTagName('check' )->item(0)->nodeValue;
            $confArray['login.shared'] = $login->getElementsByTagName('shared')->item(0)->nodeValue;
            $confArray['login.auto'  ] = $login->getElementsByTagName('auto'  )->item(0)->nodeValue;
        }

        $session = $conf->getElementsByTagName('session')->item(1); //acesso o 2Âº item pois senÃ£o pega o <session> o <mad>
        if( $session )
        {
            $confArray['session.handler'] = $session->getElementsByTagName('handler')->item(0)->nodeValue;
            $confArray['session.timeout'] = $session->getElementsByTagName('timeout')->item(0)->nodeValue;
        }

        /* db */
        $db = $conf->getElementsByTagName('db')->item(1);
        !$db ? $db = $conf->getElementsByTagName('db')->item(0) : null;
        if( $db )
        {
            $confModule = MIOLO::_REQUEST('confModule');
            !$confModule ? $confModule = 'miolo' : null;
            $dbMiolo = $db->getElementsByTagName($confModule)->item(0);
            if( $dbMiolo )
            {
                $confArray['db.'.$confModule.'.system'  ] = $dbMiolo->getElementsByTagName('system'     )->item(0)->nodeValue;
                $confArray['db.'.$confModule.'.host'    ] = $dbMiolo->getElementsByTagName('host'       )->item(0)->nodeValue;
                $confArray['db.'.$confModule.'.name'    ] = $dbMiolo->getElementsByTagName('name'       )->item(0)->nodeValue;
                $confArray['db.'.$confModule.'.user'    ] = $dbMiolo->getElementsByTagName('user'       )->item(0)->nodeValue;
                $confArray['db.'.$confModule.'.password'] = $dbMiolo->getElementsByTagName('password'   )->item(0)->nodeValue;
            }
        }

        /* logs */
        $logs = $conf->getElementsByTagName('logs')->item(1);
        if( $logs )
        {
            $confArray['logs.level'  ] = $logs->getElementsByTagName('level'     )->item(0)->nodeValue;
            $confArray['logs.handler'] = $logs->getElementsByTagName('handler'   )->item(0)->nodeValue;
            $confArray['logs.peer'   ] = $logs->getElementsByTagName('peer'      )->item(0)->nodeValue;
            $confArray['logs.port'   ] = $logs->getElementsByTagName('port'      )->item(0)->nodeValue;
        }
        return $confArray;
    }

}
?>
