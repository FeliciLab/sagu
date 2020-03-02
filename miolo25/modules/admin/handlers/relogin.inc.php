<?php

// logout
$MIOLO->getAuth()->logout();

// redirect to login
$return_to = urlencode(MIOLO::_REQUEST('return_to'));
$newURL = $MIOLO->getActionURL( $module, '_login','',array('return_to'=>$return_to));
$page->redirect( $newURL );

?>
