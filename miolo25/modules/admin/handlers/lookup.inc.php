<?php
$MIOLO->history->pop();
$lookup = new MLookup();

$MIOLO->conf->loadConf($lookup->module);

$file = $MIOLO->getModulePath($lookup->module, 'db/lookup.class.php');
if ( file_exists( $file ) )
{
    $ok = $MIOLO->uses('/db/lookup.class.php', $lookup->module);
}
else
{
    $ok = $MIOLO->uses('/classes/lookup.class.php', $lookup->module);
}

$MIOLO->assert($ok, _M('File modules/@1/db/lookup.class.php not found!<br>'.
                       'This file must implement Business@1Lookup class '.
                       'which must have a method called Lookup@2.',
                       'miolo',$lookup->module, $lookup->item));

$page->addScript('m_lookup.js');
$page->setTitle(_M('Search Window'));

$businessClass = "Business{$lookup->module}Lookup";
$lookupMethod = $lookup->autocomplete ? "AutoComplete{$lookup->item}" : "Lookup{$lookup->item}";

$object = new $businessClass();
$object->$lookupMethod($lookup);

$lookup->setContent();
?>
