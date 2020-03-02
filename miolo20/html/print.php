<?
    header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
    header ("Pragma: no-cache");                          // HTTP/1.0

    include_once '../etc/miolo/miolo.conf';

    // capture some statistics
    $MIOLO->Trace("HTTP_REFERER='" . getenv("HTTP_REFERER") . "'");
    $MIOLO->Trace("HTTP_USER_AGENT='$HTTP_USER_AGENT'");

    // page processing
    $MIOLO->Handler('print');

?>
