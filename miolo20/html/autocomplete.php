<?php
#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
# @title
#   Autocomplete features
#
# @description
#   This file handles request for automcomplete fields values
#
# @see
#
# @topics   ui
#
# @created
#   2005/10/14
#
# @organisation
#   MIOLO - Miolo Development Team - UNIVATES Centro Universitario
#
# @legal
#   CopyLeft (L) 2001-2002 UNIVATES, Lajeado/RS - Brasil
#   Licensed under GPL (see COPYING.TXT or FSF at www.fsf.org for
#   further details)
#
# @contributors
#   Vilson Cristiano Gartner [author] [vgartner@univates.br]
# 
# @maintainers
#   Vilson Cristiano Gartner [author] [vgartner@univates.br]
#
# @history
#   $Log$
#
# @id $$
#---------------------------------------------------------------------

header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0

$module  = $_GET['lmodule'] = $_REQUEST['lmodule'];
$action  = $_GET['action']  = $_REQUEST['action'];
$item    = $_GET['item']    = $_REQUEST['item'];
$related = $_GET['related'] = $_REQUEST['related'];
$value   = $_GET['value'] = $_REQUEST['value'];

require_once '../classes/miolo.class';

$MIOLO = MIOLO::GetInstance();

$MIOLO->conf = new MConfigLoader();
$MIOLO->conf->LoadConf();
$urlContext =  $MIOLO->conf->getConf('home.url') . '/' .
               $MIOLO->conf->getConf('options.dispatch') ."?module=$module&action=$action";
$MIOLO->context = new MContext($urlContext, 0, false);
$MIOLO->manager = $MIOLO;
$MIOLO->context->isFile = true;

require_once '../classes/support.inc';
$MIOLO->init();

// $MIOLO->setConf('options.dispatch', 'autocomplete.php');

$MIOLO->context->setStyle( 0 );
//$MIOLO->history = new MHistory($MIOLO);
//$MIOLO->handler();

$MIOLO->setConf('options.dispatch', 'autocomplete.php');
//include_once('history.class');

$session = $MIOLO->session;
$session->start( );

//$MIOLO->history = new MHistory($MIOLO);

$modulePath = $MIOLO->conf->getConf('home.modules'). "/$module";

$ok = require_once( $modulePath . '/db/lookup.class' );

//$ok = $MIOLO->Uses('/db/lookup.class',$module);

$MIOLO->Assert($ok,_M('File modules/@1/db/lookup.class not found.<br>'.
                      'This file must implement the Business@1Lookup class '.
                      'containing the Lookup@2 method.', 
                      'miolo',$module, $item));

eval("\$object = new Business{$module}Lookup();");

//$context = new MContext();

//echo "autoComplete$item";


$autoCompleteObj = new MAutoComplete($module,$item, $value,$related);
//var_dump($autoCompleteObj);
eval("\$rs = \$object->autoComplete$item(\$autoCompleteObj);");

$info = $autoCompleteObj->getResult();

$info_ = '';

if ( ($info) && ( $info != '' ) )
{
    foreach( $info as $i )
    {
        $info_ .= "$i|";
    }
}
// if no $info found, should we show an alert?
elseif ( MUtil::getBooleanValue( $MIOLO->conf->getConf('options.autocomplete_alert') ) == true )
{
    $info_ = 'nothing_found_';
}

$info_ = substr($info_, 0, -1);

//if( MUtil::getBooleanValue($MIOLO->getConf('options.debug')) && $MIOLO->getConf('logs.handler') == 'screen' )
//{
//    $msg = _M("[autocomplete]: Field @1 not found!<br />", $this->baseModule);
//}

echo $info_;

//var_dump( $autoCompleteObj->getResult() );

?>
