<?php
// Redireciona /admin para painel inicial do gnuteca
$url = 'http://';
if ( $_SERVER['HTTPS'] == 'on' )
{
    $url = 'https://';
}

$url .= $_SERVER['HTTP_HOST'];
$url .= str_replace('admin/', '', $_SERVER['REQUEST_URI']);
$url .= "index.php?module=gnuteca3&action=main";

header("Location: $url");
?>
