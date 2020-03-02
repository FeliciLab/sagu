<?php
/*
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0

$module = $_GET['module'] = $_REQUEST['module'];
$action = $_GET['action'] = $_REQUEST['action'];
$class  = $_GET['class']  = $_REQUEST['class'];

require_once '../classes/miolo.class';

$MIOLO = MIOLO::getInstance();

$MIOLO->conf = new MConfigLoader();
$MIOLO->conf->loadConf();

$urlContext =  $MIOLO->conf->getConf('home.url') . '/' .
               $MIOLO->conf->getConf('options.dispatch') ."?module=$module&action=$action";
$MIOLO->context = new MContext($urlContext, 0, false);
$MIOLO->manager = $MIOLO;
$MIOLO->context->isFile = true;

require_once '../classes/support.inc';
$MIOLO->init();

$MIOLO->context->setStyle( 0 );

$session = $MIOLO->session;
$session->start( );

$modulePath = $MIOLO->conf->getConf('home.modules'). "/$module";

$module = $_GET['module'] = $_REQUEST['module'];
$action = $_GET['action'] = $_REQUEST['action'];
$class  = $_GET['class']  = $_REQUEST['class'];

$MIOLO->conf->loadConf($module);

$lang = $MIOLO->getConf('i18n.language');

// undocumented temporary workaround.. ;-)
$MIOLO->setConf('options.dispatch.ignore', 'true');
$MIOLO->history = new MHistory($MIOLO);
$MIOLO->page = new MPage();


echo $content;
*/

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");              // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                                    // HTTP/1.0
//var_dump($_POST);

ini_set("session.bug_compat_42","off");
ini_set("session.bug_compat_warn","off");

require_once '../classes/miolo.class.php';

$MIOLO = MIOLO::getInstance();
$MIOLO->ignoreDispatch = true;
$MIOLO->generateMethod = 'generateAJAX';

$MIOLO->handlerRequest();

?>
