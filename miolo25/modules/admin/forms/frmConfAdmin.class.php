<?php
class frmConfAdmin extends MForm
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

//        $this->conf = $MIOLO->getConf('home.etc').'/miolo.conf';
        $this->conf = $conf;
        parent::__construct( _M('Admin', 'admin') );
 
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


        /* MAD base group */
        $cont[] = $lblTab4a[] = new MLabel(_M('Admin Module').':');
        $cont[] = $madModule = new MSelection('madModule', $this->getFormValue('madModule'), null, $modules);
        $cont[] = new MDiv('madModuleLoadingDiv', null);
        $madModule->addAttribute('onChange', 'javascript:reloadMadClasses(this.value)');
        $mad[]= new MHContainer('cont1', $cont);
        unset($cont);
        $mad[]= new MSeparator();
        
        /* MAD Classes base group */
        /* get all classes in the 'classes' path of the module */
        $classesPath = $MIOLO->getConf('home.modules').'/'.$MIOLO->getConf('mad.module').'/classes/';
        if(is_dir($classesPath))
        {
            foreach( scandir($classesPath) as $arqs )
            {
                $arq = explode(".", $arqs);
                if( (substr($arqs,0,1) != '.') and !is_dir($classesPath.$arqs) and ($arq[1] == 'class'))
                {
                    $classes[$arq[0]] = $arq[0];
                }
            }
        }
        $cont[] = $lblMadClasses[] = new MLabel(_M('Access').':');
        $cont[] = new MSelection('aAccess', $this->getFormValue('aAccess'), null, $classes);
        $madClasses[] = new MHContainer('cont2', $cont);
        unset($cont);
        $madClasses[] = new MSeparator();
        $cont[] = $lblMadClasses[] = new MLabel(_M('Group').':');
        $cont[] = new MSelection('aGroup', $this->getFormValue('aGroup'), null, $classes);
        $madClasses[] = new MHContainer('cont3', $cont);
        unset($cont);
        $cont[] = $lblMadClasses[] = new MLabel(_M('Log').':');
        $cont[] = new MSelection('aLog', $this->getFormValue('aLog'), null, $classes);
        $madClasses[] = new MHContainer('cont4', $cont);
        unset($cont);
        $cont[] = $lblMadClasses[] = new MLabel(_M('Session').':');
        $cont[] = new MSelection('aSession', $this->getFormValue('aSession'), null, $classes);
        $madClasses[] = new MHContainer('cont5', $cont);
        unset($cont);
        $cont[] = $lblMadClasses[] = new MLabel(_M('Transaction').':');
        $cont[] = new MSelection('aTransaction', $this->getFormValue('aTransaction'), null, $classes);
        $madClasses[] = new MHContainer('cont6', $cont);
        unset($cont);
        $cont[] = $lblMadClasses[] = new MLabel(_M('User').':');
        $cont[] = new MSelection('aUser', $this->getFormValue('aUser'), null, $classes);
        $madClasses[] = new MHContainer('cont7', $cont);
        unset($cont);

        $contMadClasses   = new MVContainer('contMadClasses', $madClasses);
        $mad[]= $bgMadClasses = new MBaseGroup('bgMadClasses', _M('Classes'), array($contMadClasses), 'vertical');
        $bgMadClasses->width = '300px';
        /* end MAD Classes base group */

        $top[]   = $bgMad = new MBaseGroup('bgMad', _M('MAD'), $mad, 'vertical');
        $bgMad->width = '310px';
        
        foreach( $lblMadClasses as $lbl )
        {
            $lbl->width = '80px';
        }
        /* end MAD base group */

        /* login base group */
        $cont[]  = $lblLogin[] = new MLabel(_M('Module').':');
        $cont[]  = new MSelection('aLoginModule', $this->getFormValue('aLoginModule'), null, $modules);
        $login[] = new MHContainer('cont8', $cont);
        unset($cont);
        $login[] = new MSeparator();
        $cont[]  = $lblLogin[] = new MLabel(_M('Class').':');
        $cont[]  = new MTextField('aLoginClass', $this->getFormValue('aLoginClass'), null, '20px');
        $login[] = new MHContainer('cont9', $cont);
        unset($cont);
        $cont[]  = $lblLogin[] =  new MLabel(_M('Check').':');
        $cont[]  = new MSelection('aLoginCheck', $this->getFormValue('aLoginCheck'), null, $optionsTF);
        $login[] = new MHContainer('cont10', $cont);
        unset($cont);
        $cont[]  = $lblLogin[] = new MLabel(_M('Shared').':');
        $cont[]  = new MSelection('aLoginShared', $this->getFormValue('aLoginShared'), null, $optionsTF);
        $login[] = new MHContainer('cont11', $cont);
        unset($cont);
        $cont[]  = $lblLogin[] = new MLabel(_M('Auto').':');
        $cont[]  = new MSelection('aLoginAuto', $this->getFormValue('aLoginAuto'), null, $options01);
        $login[] = new MHContainer('cont12', $cont);
        unset($cont);

        $dir[] = $bgLogin = new MBaseGroup('bgLogin', _M('Login'), $login, 'vertical');
        $bgLogin->width = '250px';
        foreach( $lblLogin as $lbl )
        {
            $lbl->width = '60px';
        }
        /* end login base group */

        /* session base group */
        $cont[]    = $lblSession[] = new MLabel(_M('Handler').':');
        $cont[]    = new MTextField('aSessionHandler', $this->getFormValue('aSessionHandler'), null, '20px');
        $session[] = new MHContainer('cont13', $cont);
        unset($cont);
        $cont[]    = $lblSession[] = new MLabel(_M('Timeout').':');
        $cont[]    = new MTextField('aSessionTimeout', $this->getFormValue('aSessionTimeout'), null, '20px');
        $session[] = new MHContainer('cont14', $cont);
        unset($cont);

        $dir[]     = $bgSession = new MBaseGroup('bgSession', _M('Session'), $session, 'vertical');
        $bgSession->width = '250px';
        foreach( $lblSession as $lbl )
        {
            $lbl->width = '60px';
        }
        /* end session base group */

        $top[]    = new MVContainer('contDir', $dir);
        $fields[] = new MHContainer('tab4',    $top);

        /* logs base group */
        $cont[] = $lblLogs[] = new MLabel(_M('Level').':');
        $cont[] = new MTextField('aLogsLevel', $this->getFormValue('aLogsLevel'), null, '20px');
        $logs[] = new MHContainer('cont15', $cont);
        unset($cont);
        $cont[] = $lblLogs[] = new MLabel(_M('Handler').':');
        $cont[] = new MTextField('aLogsHandler', $this->getFormValue('aLogsHandler'), null, '20px');
        $logs[] = new MHContainer('cont16', $cont);
        unset($cont);
        $cont[] = $lblLogs[] = new MLabel(_M('Peer').':');
        $cont[] = new MTextField('aLogsPeer', $this->getFormValue('aLogsPeer'), null, '20px');
        $logs[] = new MHContainer('cont17', $cont);
        unset($cont);
        $cont[] = $lblLogs[] = new MLabel(_M('Port').':');
        $cont[] = new MTextField('aLogsPort', $this->getFormValue('aLogsPort'), null, '20px');
        $logs[] = new MHContainer('acont18', $cont);
        unset($cont);
        $fields[] = $bgLogs = new MBaseGroup('bgLogs', _M('Logs'), $logs, 'vertical');
        $bgLogs->width = '225px';
        foreach( $lblLogs as $lbl )
        {
            $lbl->width = '60px';
        }
        /* end logs base group */

        $fields = new MVContainer('tab4', $fields);

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
        
        /* administration */
        if( $mad = $conf->getElementsByTagName('mad')->item(0) )
        {
            $this->madModule      ->setValue($mad->getElementsByTagName('module'     )->item(0)->nodeValue);
            if( $madClasses = $mad->getElementsByTagName('classes')->item(0) )
            {
                $this->aAccess        ->setValue($madClasses->getElementsByTagName('access'     )->item(0)->nodeValue);
                $this->aGroup         ->setValue($madClasses->getElementsByTagName('group'      )->item(0)->nodeValue);
                $this->aLog           ->setValue($madClasses->getElementsByTagName('log'        )->item(0)->nodeValue);
                $this->aSession       ->setValue($madClasses->getElementsByTagName('session'    )->item(0)->nodeValue);
                $this->aTransaction   ->setValue($madClasses->getElementsByTagName('transaction')->item(0)->nodeValue);
                $this->aUser          ->setValue($madClasses->getElementsByTagName('user'       )->item(0)->nodeValue);
            }
        }

        if( $login = $conf->getElementsByTagName('login')->item(0) )
        {
            $this->aLoginModule   ->setValue($login->getElementsByTagName('module')->item(0)->nodeValue);
            $this->aLoginClass    ->setValue($login->getElementsByTagName('class' )->item(0)->nodeValue);
            $this->aLoginCheck    ->setValue($login->getElementsByTagName('check' )->item(0)->nodeValue);
            $this->aLoginShared   ->setValue($login->getElementsByTagName('shared')->item(0)->nodeValue);
            $this->aLoginAuto     ->setValue($login->getElementsByTagName('auto'  )->item(0)->nodeValue);
        }

        //acesso o 2Âº item pois senÃ£o pega o <session> o <mad>
        if( $session = $conf->getElementsByTagName('session')->item(1) )
        {
            $this->aSessionHandler  ->setValue($session->getElementsByTagName('handler')->item(0)->nodeValue);
            $this->aSessionTimeout  ->setValue($session->getElementsByTagName('timeout')->item(0)->nodeValue);
        }
       
        if( $logs = $conf->getElementsByTagName('logs')->item(1) )
        {
            $this->aLogsLevel   ->setValue($logs->getElementsByTagName('level'     )->item(0)->nodeValue);
            $this->aLogsHandler ->setValue($logs->getElementsByTagName('handler'   )->item(0)->nodeValue);
            $this->aLogsPeer    ->setValue($logs->getElementsByTagName('peer'      )->item(0)->nodeValue);
            $this->aLogsPort    ->setValue($logs->getElementsByTagName('port'      )->item(0)->nodeValue);
        }


    }

    /**
     * Update conf array by form data
     * @param (array) conf values
     * @return (array) updated conf values
     */
    public function setConfArray($confArray)
    {
        $confArray['mad.module'             ] = $this->madModule      ->getValue();
        $confArray['mad.classes.access'     ] = $this->aAccess        ->getValue();
        $confArray['mad.classes.group'      ] = $this->aGroup         ->getValue();
        $confArray['mad.classes.log'        ] = $this->aLog           ->getValue();
        $confArray['mad.classes.session'    ] = $this->aSession       ->getValue();
        $confArray['mad.classes.transaction'] = $this->aTransaction   ->getValue();
        $confArray['mad.classes.user'       ] = $this->aUser          ->getValue();
        $confArray['login.module'           ] = $this->aLoginModule   ->getValue();
        $confArray['login.class'            ] = $this->aLoginClass    ->getValue();
        $confArray['login.check'            ] = $this->aLoginCheck    ->getValue();
        $confArray['login.shared'           ] = $this->aLoginShared   ->getValue();
        $confArray['login.auto'             ] = $this->aLoginAuto     ->getValue();
        $confArray['session.handler'        ] = $this->aSessionHandler->getValue();
        $confArray['session.timeout'        ] = $this->aSessionTimeout->getValue();
        $confArray['logs.level'             ] = $this->aLogsLevel     ->getValue();
        $confArray['logs.handler'           ] = $this->aLogsHandler   ->getValue();
        $confArray['logs.peer'              ] = $this->aLogsPeer      ->getValue();
        $confArray['logs.port'              ] = $this->aLogsPort      ->getValue();
        
        return $confArray;
    }

}
?>
