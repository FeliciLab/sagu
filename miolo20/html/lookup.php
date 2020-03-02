<?php
    
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0


$theme_layout = 'lookup';
include_once '../etc/miolo/miolo.conf';

// capture some statistics
$MIOLO->Trace("HTTP_REFERER='" . $_SERVER['HTTP_REFERER'] . "'");
$MIOLO->Trace("HTTP_USER_AGENT='".$_SERVER['HTTP_USER_AGENT']."'");

$MIOLO->Uses('lookup.class');
$MIOLO->Uses('history.class');
$MIOLO->Uses('database/sql.class');
$MIOLO->Uses('ui/controls/basecontrols.class');
$MIOLO->Uses('ui/controls/webcontrols.class');
$MIOLO->Uses('ui/controls/labelcontrols.class');
$MIOLO->Uses('ui/controls/buttoncontrols.class');
$MIOLO->Uses('ui/controls/fieldcontrols.class');
$MIOLO->Uses('ui/controls/listcontrols.class');
$MIOLO->Uses('ui/controls/gridcontrols.class');

$MIOLO->Uses('ui/controls/pagenavigator.class');
$MIOLO->Uses('ui/controls/range.class');
$MIOLO->Uses('ui/controls/gridnavigator.class');
$MIOLO->Uses("ui/controls/datagrid.class");

$lookup = new Lookup();

$ok = $MIOLO->Uses('/classes/lookup.class',$lookup->module);

$MIOLO->Assert($ok,_M('File modules/@1/db/lookup.class not found.<br>'.
                      'This file must implement the Business@1Lookup class '.
                      'containing the Lookup@2 method.', 
                      'miolo',$lookup->module, $lookup->item));

$page->AddScript('m_lookup.js');
$page->SetTitle(_M('Query window'));
//$filter = $MIOLO->_REQUEST('filter');



//$lookup->SetForm(new Form('Filter'));

//if ($fvalue)
//{
//   $lookup->filterValue = $fvalue;
//}

eval("\$object = new Business{$lookup->module}Lookup();");
eval("\$object->Lookup{$lookup->item}(\$lookup);");

//$filterForm = & $lookup->GetForm();

//$filterFields = $lookup->GetFilterFields();
//if ( ! $filterFields )
//{
//    $filterFields = new TextField('filter','Filtro',$filtro,30);
//}
//$filterForm->AddButton(new FormButton('btnPost', 'Pesquisar'));
//$filterForm->AddButton(new FormButton('btnClose', 'Fechar', 'window.close()'));

       

//foreach( $filterFields as $f )
//{
//   $url .= "&$f="   . urlencode($MIOLO->_REQUEST($f));
//}

$page->SetAction($url);

//$listing = new DataGrid2(
//    $lookup->query,
//    $lookup->columns,
//    $url,
//    $lookup->GetPageLength(),
//    $lookup->keyColumn 
//);

//foreach( $filterFields as $f )
//{
//    $listing->AddFilterText($f->name,$f->label,$f->value, $f->name);
//}
//$listing->SetFilter(true);

//$listing->SetTitle($lookup->listingTitle);
//$content = array($lookup->GetForm(),$listing);
$theme->SetContent($lookup->grid);

//$MIOLO->GenerateTheme('lookup');

$page->Generate();

?>
