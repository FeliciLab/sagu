<?php

    
$MIOLO->trace('file:'.$_SERVER['SCRIPT_NAME']);

$navbar->addOption( _M('Module Setup', 'admin'), $module, 'main:modules:add_modules:requisite_setup_module');

$theme->clearContent();
 
$MIOLO->checkAccess('admin', A_ACCESS, true);

include_once( $MIOLO->getConf('home.etc') . '/webinstaller/installer.class' );

//recebe o diretorio em que se encotra o modulo zip
//recebe instalacao local
$tmpSysDir = MUtil::getSystemTempDir();

if ( ! $tmpSysDir )
{
    $tmpSysDir = '/tmp/';
}


if ( $modpkg = MIOLO::_REQUEST('localFileField') )
{
    $modpkgName = getModName($modpkg);  //recebe o nome do dir

    $dirModTMP = $tmpSysDir . "/". $modpkgName;
    $dirTMP = $tmpSysDir;

    copy($modpkg, $dirModTMP.".zip");
}

if ( $modpkg = MIOLO::_REQUEST('fileURL') )
{
    $modpkgName = getModName( urldecode($modpkg) );  //recebe o nome do dir

    $dirModTMP = $tmpSysDir . "/". $modpkgName;
    $dirTMP = $tmpSysDir;

    $modpkg =  urldecode($modpkg);

    download($modpkg, $dirModTMP.'.zip');
} 


//descompacta modulo no tmp
$MIOLO->uses('utils/mzip.class');

MZip::unzip($dirModTMP.".zip",$dirTMP);


//le informacoes do modulo no xml module.inf
$dom = new DomDocument();
$dom->load($dirModTMP."/etc/module.inf");

if($dom->getElementsByTagName('required')->item(0)->nodeValue)
{
    $modRequired=$dom->getElementsByTagName('required')->item(0)->nodeValue;
}


// zip
Requisites::addRequisite('Suppor for Zip Files', // label
                         'function_exists(zip_read)', // expression
                         ' true ' , // expected label
                         'Miolo requires zip extension to be able to install new modules',
                         false);


// dependency
if($modRequired)
Requisites::addRequisite('Moule dependencies satistied?', // label
                         file_exists($MIOLO->getConf('home.modules').'/'.$modRequired ), // expression
                         ' true ' , // expected label
                         'This Module requires dependency installed to be able to install',
                         true);

//validate module
Requisites::addRequisite('Is it a valid module?', // label
                         file_exists($dirModTMP.'/etc/module.inf'), // expression
                         ' true ' , // expected label
                         'Verifies if this is a Valid Miolo2 module',
                         true);




$installDir = $MIOLO->getConf('home.modules');
Requisites::addRequisite("Is <b>$installDir</b> writable?", // label
                         "is_writable(\"$installDir\")", // expression
                         ' True ' , // expected label
                         'In order to install this module, this dir MUST exist and be writable!',
                         true);

$nextStep = Requisites::processRequisites( );

$form = new MForm('Module Environment');
$form->defaultButton = false;

$url = $MIOLO->getActionURL($module, 'main:modules');

$formActionBack = $MIOLO->getConf('home.url') . '/' . $MIOLO->getConf('options.dispatch');
$form->addButton( new MButton( 'btnForm', _M('Go Back', $module), "javascript:GotoURL('$url')" ) );

if ($nextStep)
{
    $formActionNext = $MIOLO->getActionURL('admin', 'main:modules:setup_module', null, array ('modulo'=>$modpkgName));
    $form->addButton( new MButton( 'btnNext', _M('Install Module', $module), "javascript:GotoURL('$formActionNext')" ) );
    $form->setAction($formActionNext);
}

$fields = array( Requisites::$content );
$form->setFields( $fields);

$theme->appendContent($form);

$handled = $MIOLO->invokeHandler($module,'modules/'. $context->shiftAction() );
if ( ! $handled)
{
    $theme->insertContent($cmPanel);
}


function getModName($modpkg)
{
    $modpkgName = split ('/',$modpkg);
    $n = count($modpkgName)-1;
    $aux = $modpkgName[$n];
    return (substr($aux,0,-4));
}

//faz o download do modulo
function download($file_source, $file_target) 
{
    $rh = fopen($file_source, 'rb');
    $wh = fopen($file_target, 'wb');
    if ($rh===false || $wh===false) 
    {
        return true;
    }
    while ( ! feof($rh) ) 
    {
        if ( fwrite($wh, fread($rh, 1024)) === FALSE ) 
        {                   
            return true;
        }
    }
    fclose($rh);
    fclose($wh);
    return false;
}

?>
