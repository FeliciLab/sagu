<?php
    ini_set('display_errors', true);

	include "ws-security.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Dados Profissional</title>
</head>
<body>

<?php

try {
	$options = array( 'location' => 'https://servicos.saude.gov.br/cnes/VinculacaoProfissionalService/v1r0',
	'encoding' => 'utf-8', 
	'soap_version' => SOAP_1_2,
	'connection_timeout' => 180,
	'trace'        => 1, 
	'exceptions'   => 1 );

	$client = new SoapClient('https://servicos.saude.gov.br/cnes/VinculacaoProfissionalService/v1r0?wsdl', $options);
	$client->__setSoapHeaders(soapClientWSSecurityHeader('CNES.PUBLICO', 'cnes#2015public'));

	$function = 'pesquisarVinculacaoProfissionalSaude';

	$arguments= array( 'vin' => array(
							'FiltroPesquisaVinculacaos' => array(
                                'IdentificacaoProfissional' => array(
                                    'cns' => array(
                                        'numeroCNS'  => "980016281298549"
                                    )
                                ),
                                'IdentificacaoEstabelecimento' => array(
                                    'cnes' => array(
                                        'codigo'  => "2613476"
                                    )
                                )

							),
                            'Paginacao' => array(
                                'registroInicial'  => 1,
                                'quantidadeRegistros' => 100,
                                'totalRegistros' => 1000
                            )
	                    )
	                );

	$result = $client->__soapCall($function, $arguments);


    if (!empty($result)) {
        print("<pre>".print_r($result,true)."</pre>");
    }

} catch (Exception  $e) {
    print print_r($e);
}

?>
	
</body>
</html>