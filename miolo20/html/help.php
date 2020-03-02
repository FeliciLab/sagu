<?php

function replaceImage($strValue, $MIOLO)
{
    if ( ( $posBegin = strpos($strValue, 'THEME_IMAGE/') ) > 0 )
    {
        $posEnd = strpos($strValue, ' ', $posBegin);
        $len = (int) $posEnd - (int) $posBegin - 12;

        $imageName = substr($strValue, $posBegin+12 , $len);

        $ui = $MIOLO->getUI();
        $img = "<img src='". $ui->getImageTheme( $MIOLO->getTheme()->id, $imageName)."'>";

        $result = str_replace('THEME_IMAGE/'.$imageName, $img, $strValue);
    }
    else
    {
        $result = $strValue;
    }

    return $result;
}

function parseXMLFile($file, $module, $lang, $theme_image_dir, $MIOLO)
{
    define('THEME_IMAGE', $theme_image_dir);
    define('DOC_IMAGE', 'doc/'.$lang.'/images/');

    if ( file_exists($file) )
    {
        $fp   = fopen($file, 'r');
        $data = fread($fp, filesize($file));
        fclose($fp);

        //$xml = new SimpleXMLElement($data);
        $xml = simplexml_load_file($file);

        $header = '<p class="m-tableraw-row"><b>' . _M( utf8_decode($xml->name), $module) . ':</b> ' .
                  replaceImage( utf8_decode($xml->description), $MIOLO) . '</p>';

        if ( count($xml->attributes->attribute) > 0 )
        {
            foreach ( $xml->attributes->attribute as $attribute )
            {
                $label = _M( utf8_decode($attribute->label), $module);
                $type  = _M( utf8_decode($attribute->type) , $module);
                $descr = _M(utf8_decode( $attribute->description), $module );
                $descr = replaceImage( $descr, $MIOLO);

                $fieldsArray = array("$label", "$type" , "$descr");
                $array[] = $fieldsArray;
            }
        }

        /* only for off-line documentation
        if ( strlen($xml->image) > 0 )
        {
            $image = '<p class="m-tableraw-row">' . DOC_IMAGE . '/' . $xml->image . '</p>';
        }
        */

       $tableraw = new MTableRaw('', $array);
       $tableraw->setAlternate(true);
       $content = $header . $tableraw->generate() . $image;

        return $content;
    }
    else
    {
        return 'NONE|'.$xmlFileName;
    }

}


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
$fileName = $action . '_' . $class;
$fileNameXML  = str_replace(':', '_', $fileName . '.xml');
$fileNameHTML = str_replace(':', '_', $fileName . '.html');

// complete file name
$xmlFileName  = $modulePath . '/doc/' . $lang . '/' . $fileNameXML;
$htmlFileName = $modulePath . '/doc/' . $lang . '/' . $fileNameHTML;

if ( file_exists($xmlFileName) )
{
    // find the path to the module's theme
    $themeImagesDir = $MIOLO->getConf('home.themes') . '/' .
                      $MIOLO->getConf('theme.main')  . '/images/';

    // undocumented temporary workaround.. ;-)
    $MIOLO->setConf('options.dispatch.ignore', 'true');
    $MIOLO->history = new MHistory($MIOLO);
    $MIOLO->page = new MPage();

    //parse the xml file
    $content = parseXMLFile($xmlFileName, $module, $lang, $themeImagesDir, $MIOLO);
}
elseif ( file_exists($htmlFileName) )
{
    $content = file_get_contents($htmlFileName);
}
else
{
    $content = 'NONE|'.$xmlFileName;
}

echo $content;

?>
