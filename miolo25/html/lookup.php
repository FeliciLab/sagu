<?php

header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0


$theme_layout = 'lookup';
include_once '../etc/miolo/miolo.conf';

// capture some statistics
$MIOLO->trace("HTTP_REFERER='" . $_SERVER['HTTP_REFERER'] . "'");
$MIOLO->trace("HTTP_USER_AGENT='".$_SERVER['HTTP_USER_AGENT']."'");

$MIOLO->uses('lookup.class.php');
$MIOLO->uses('history.class.php');
$MIOLO->uses('database/sql.class.php');
$MIOLO->uses('ui/controls/basecontrols.class.php');
$MIOLO->uses('ui/controls/webcontrols.class.php');
$MIOLO->uses('ui/controls/labelcontrols.class.php');
$MIOLO->uses('ui/controls/buttoncontrols.class.php');
$MIOLO->uses('ui/controls/fieldcontrols.class.php');
$MIOLO->uses('ui/controls/listcontrols.class.php');
$MIOLO->uses('ui/controls/gridcontrols.class.php');

$MIOLO->uses('ui/controls/pagenavigator.class.php');
$MIOLO->uses('ui/controls/range.class.php');
$MIOLO->uses('ui/controls/gridnavigator.class.php');
$MIOLO->uses("ui/controls/datagrid.class");

$lookup = new Lookup();

$ok = $MIOLO->uses('/classes/lookup.class.php',$lookup->module);

$MIOLO->assert($ok,_M('File modules/@1/db/lookup.class.php not found!<br>'.
                      'This file must implement Business@1Lookup class '.
                      'which must have a method called Lookup@2.',
                      'miolo',$lookup->module, $lookup->item));

$page->addScript('m_lookup.js');
$page->setTitle('Janela de Pesquisa');
//$filter = $MIOLO->_REQUEST('filter');



//$lookup->setForm(new Form('Filter'));

//if ($fvalue)
//{
//   $lookup->filterValue = $fvalue;
//}

eval("\$object = new Business{$lookup->module}Lookup();");
eval("\$object->lookup{$lookup->item}(\$lookup);");

//$filterForm = & $lookup->getForm();

//$filterFields = $lookup->getFilterFields();
//if ( ! $filterFields )
//{
//    $filterFields = new TextField('filter','Filtro',$filtro,30);
//}
//$filterForm->addButton(new FormButton('btnPost', 'Pesquisar'));
//$filterForm->addButton(new FormButton('btnClose', 'Fechar', 'window.close()'));



//foreach( $filterFields as $f )
//{
//   $url .= "&$f="   . urlencode($MIOLO->_REQUEST($f));
//}

$page->setAction($url);

//$listing = new DataGrid2(
//    $lookup->query,
//    $lookup->columns,
//    $url,
//    $lookup->getPageLength(),
//    $lookup->keyColumn
//);

//foreach( $filterFields as $f )
//{
//    $listing->addFilterText($f->name,$f->label,$f->value, $f->name);
//}
//$listing->setFilter(true);

//$listing->setTitle($lookup->listingTitle);
//$content = array($lookup->getForm(),$listing);
$theme->setContent($lookup->grid);

//$MIOLO->generateTheme('lookup');

$page->generate();

?>
