<?php

// logout
if ( !(MIOLO::_REQUEST('do_logout') == 'n') )
{
    $MIOLO->getAuth()->logout();
}
$opts = null;

$MIOLO->uses('types/avaConfig.class.php', 'avinst');
$config = new stdClass();
$config->chave_ = 'RETURN_TO';
$avaConfig = new avaConfig($config);
$returnTo = $avaConfig->search();

$returnTo = $returnTo[0][1];
$opts = array('return_to' => urlencode($returnTo));
$theme->clearContent();

$MIOLO->session->set("loginFrom", null);

// redirect to common environment
if ( $returnTo == 'PORTAL_ANTIGO' )
{
    $newURL = $MIOLO->getActionURL('services', 'main');
    if ( !(MIOLO::_REQUEST('do_logout') == 'n') )
    {
        $newURL = $MIOLO->getActionURL('services', 'login');
    }
    $newURL = str_replace('&amp;', '&', $newURL);
    $MIOLO->page->onLoad("window.location = \"$newURL\";");
}
elseif ( $returnTo == 'PORTAL_NOVO' )
{
    
    $newURL = $MIOLO->getActionURL( 'portal', 'main');
    $MIOLO->page->onLoad('window.location = "' . $newURL . '"');
}
else    
{    
    $newURL = $MIOLO->getActionURL( 'avinst', 'main', null, $opts);
    $MIOLO->page->onLoad('window.location = "' . $newURL . '"');
}
?>
