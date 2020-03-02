<?php


require_once "ws-security.php";


ini_set('display_errors', true);


$conCnes = new PDO("pgsql:host=10.17.0.209;port=5470;dbname=cnes", "mapas", "mapas");
$conCnes->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if (!$conCnes) {
    echo 'não conectou';
}


//$tipos = include_once '../../../src/protected/application/conf/space-types.php';

$schemaCnes = 'public';


$sql = "
    SELECT DISTINCT cnes   
    FROM public.estabelecimentos
";
$query1 = $conCnes->query($sql);
while ($row = $query1->fetch(PDO::FETCH_OBJ)) {


    $options = array('location' => 'https://servicoshm.saude.gov.br/cnes/EstabelecimentoSaudeService/v1r0',
        'encoding' => 'utf-8',
        'soap_version' => SOAP_1_2,
        'connection_timeout' => 180,
        'trace' => 1,
        'exceptions' => 1);

    $client = new SoapClient('https://servicoshm.saude.gov.br/cnes/EstabelecimentoSaudeService/v1r0?wsdl', $options);
    $client->__setSoapHeaders(soapClientWSSecurityHeader('CNES.PUBLICO', 'cnes#2015public'));

    $function = 'consultarEstabelecimentoSaude';

    $arguments = array('est' => array(
        'FiltroPesquisaEstabelecimentoSaude' => array(
            'CodigoCNES' => array(
                'codigo' => $row->cnes
            )
        )
    )
    );

    echo $row->cnes . PHP_EOL;

    $result = $client->__soapCall($function, $arguments);

    $servicosEspecializados = $result->DadosGeraisEstabelecimentoSaude->servicoespecializados;

    if (is_array($servicosEspecializados->servicoespecializado)) {
        foreach ($servicosEspecializados->servicoespecializado as $servicosEspecializado) {
            if ($servicosEspecializado->codigo != null || $servicosEspecializado->codigo != '') {

                $sqlInsert = "INSERT INTO public.estabelecimentosservicos (cnes, codigo, descricao) VALUES ('{$row->cnes}', '{$servicosEspecializado->codigo}', '{$servicosEspecializado->descricao}')";
                $conCnes->exec($sqlInsert);

            }
        }
    } else {

        $servicosEspecializado = $servicosEspecializados->servicoespecializado;
        if ($servicosEspecializado->codigo != null || $servicosEspecializado->codigo != '') {
            $sqlInsert = "INSERT INTO public.estabelecimentosservicos (cnes, codigo, descricao) VALUES ('{$row->cnes}', '{$servicosEspecializado->codigo}', '{$servicosEspecializado->descricao}')";
            $conCnes->exec($sqlInsert);
        }
    }

}

?>
