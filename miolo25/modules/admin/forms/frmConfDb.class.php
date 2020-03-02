<?php
class frmConfDb extends MForm
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
        parent::__construct( _M('DB', $module) );
 
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
 
        $suportedDbs = $MIOLO->getConf('home.classes').'/database';
        foreach( scandir($suportedDbs) as $dir )
        {
            if( (substr($dir,0,1) != '.') and is_dir($suportedDbs.'/'.$dir) )
            {
                $systems[$dir] = $dir;
            }
        }

        $cont[]   = $lbls[] = new MLabel(_M('System').':');
        $cont[]   = new MSelection('dbSystem', $this->getFormValue('dbSystem'), null, $systems);
        $fields[] = new MHContainer('cont1', $cont);
        unset($cont);
        $cont[]   = $lbls[] = new MLabel(_M('Host').':');
        $cont[]   = new MTextField('dbHost', $this->getFormValue('dbHost'), null, '20px');
        $fields[] = new MHContainer('cont2', $cont);
        unset($cont);
        $cont[]   = $lbls[] = new MLabel(_M('Name(path)').':');
        $cont[]   = new MTextField('dbName', $this->getFormValue('dbName'), null, '20px');
        $fields[] = new MHContainer('cont3', $cont);
        unset($cont);
        $cont[]   = $lbls[] = new MLabel(_M('User').':');
        $cont[]   = new MTextField('dbUser', $this->getFormValue('dbUser'), null, '20px');
        $fields[] = new MHContainer('cont4', $cont);
        unset($cont);
        $cont[]   = $lbls[] = new MLabel(_M('Password').':');
        $cont[]   = new MTextField('dbPassword', $this->getFormValue('dbPassword'), null, '20px');
        $fields[] = new MHContainer('cont5', $cont);
        unset($cont);
        foreach( $lbls as $lbl )
        {
            $lbl->width = '85px';
        }

        $fields   = new MVContainer('tab5', $fields);
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
        
        if( $db = $conf->getElementsByTagName('db')->item(1) )
        {
            $dbMiolo = $db->getElementsByTagName('miolo')->item(0);
        }
        elseif( $db = $conf->getElementsByTagName('db')->item(0) )
        {
            $dbMiolo = $db->getElementsByTagName('miolo')->item(0);
        }
        
        $confModule = 'miolo';
        if( MIOLO::_REQUEST('confModule') )
        {
            $confModule = MIOLO::_REQUEST('confModule');
        }
        if( $dbMiolo = $db->getElementsByTagName($confModule)->item(0) )
        {
            $this->dbSystem   ->setValue($dbMiolo->getElementsByTagName('system'     )->item(0)->nodeValue);
            $this->dbHost     ->setValue($dbMiolo->getElementsByTagName('host'       )->item(0)->nodeValue);
            $this->dbName     ->setValue($dbMiolo->getElementsByTagName('name'       )->item(0)->nodeValue);
            $this->dbUser     ->setValue($dbMiolo->getElementsByTagName('user'       )->item(0)->nodeValue);
            $this->dbPassword ->setValue($dbMiolo->getElementsByTagName('password'   )->item(0)->nodeValue);
        }
        
    }

    /**
     * Update conf array by form data
     * @param (array) conf values
     * @return (array) updated conf values
     */
    public function setConfArray($confArray)
    {
        /* REVISAR */
        $confModule = MIOLO::_REQUEST('confModule');
        !$confModule ? $confModule = 'miolo' : null;
        $confArray['db.'.$confModule.'.system'  ] = $this->dbSystem  ->getvalue();
        $confArray['db.'.$confModule.'.host'    ] = $this->dbHost    ->getvalue();
        $confArray['db.'.$confModule.'.name'    ] = $this->dbName    ->getvalue();
        $confArray['db.'.$confModule.'.user'    ] = $this->dbUser    ->getvalue();
        $confArray['db.'.$confModule.'.password'] = $this->dbPassword->getvalue();
        
        return $confArray;
    }
}
?>
