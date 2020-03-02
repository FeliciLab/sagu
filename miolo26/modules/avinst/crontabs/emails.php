<?php 
include_once 'miolo25.php';
$MIOLO->uses('classes/amail.class.php','avinst');
$amail = new AMail($argv[1]);
$amail->enviarEmails();
?>