<?php

// logout
$MIOLO->getAuth()->logout();

// redirect to common environment
$newURL = $MIOLO->getActionURL( $MIOLO->getConf('options.common'), 'main');
$page->redirect( $newURL );

?>
