<pre>
<?php

ini_set("soap.wsdl_cache_enabled", 0);

$url = 'http://localhost/sagu_trunk/miolo25/html/';

// Parâmetros do SOAP.
$clientOptions["location"] = "$url/webservices.php?module=gnuteca3&action=main&class=gnuteca3WebServicesPurchaseRequest";
$clientOptions["uri"] = "$url";
$clientOptions["encoding"] = "UTF-8";

try
{
    $client = new SoapClient(NULL, $clientOptions);
    $result = $client->getExemplariesFromPurchaseRequest( '1', base64_encode('123456'), array(123,321) );
}
catch ( Exception $e)
{
    die( 'Erro='.$e->getMessage() ."\n" );
    return false;
}

var_dump($result);

?> 
</pre>