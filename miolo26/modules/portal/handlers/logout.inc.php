<?php

$login = $MIOLO->getLogin();
BasFilaDeEspera::excluiUsuarioFilaDeEspera($login->id);

$MIOLO->session->set("loginFrom", null);

// logout
$MIOLO->getAuth()->logout();

// redirect to common environment
$newURL = $MIOLO->getActionURL( $MIOLO->getConf('options.common'), 'main');
$page->redirect( $newURL );

?>
