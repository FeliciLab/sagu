<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dados Profissional</title>
</head>
<body>


<?php

ini_set('display_errors', true);

include "php-cnes-master/ws-security.php";

try {
    $pdo = new PDO("pgsql:host=localhost;dbname=sagu", "postgres", "postgres");




$consulta = $pdo->query("
    select DISTINCT (B.personid), CPF.content as cpf from 
    public.basperson B
    LEFT JOIN public.basDocument CPF
        ON B.personId = CPF.personId AND CPF.documentTypeId = 2 ORDER BY B.personid
");


while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
    $personid = $linha['personid'];
    $cpf = str_replace(array('-', '.'), array('', ''), $linha['cpf']);


    try {

        /**
         * -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
         * Consulta profissional
         * -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
         */
        $options1 = array('location' => 'https://servicoshm.saude.gov.br/cnes/ProfissionalSaudeService/v1r0',
            'encoding' => 'utf-8',
            'soap_version' => SOAP_1_2,
            'connection_timeout' => 180,
            'trace' => 1,
            'exceptions' => 1);

        $client1 = new SoapClient('https://servicoshm.saude.gov.br/cnes/ProfissionalSaudeService/v1r0?wsdl', $options1);
        $client1->__setSoapHeaders(soapClientWSSecurityHeader('CNES.PUBLICO', 'cnes#2015public'));

        $function1 = 'consultarProfissionalSaude';

        $arguments1 = array('prof' => array(
                            'FiltroPesquisaProfissionalSaude' => array(
                                'CPF' => array(
                                    'numeroCPF' => $cpf
                                )
                            )
                        )
                    );
        $result1 = $client1->__soapCall($function1, $arguments1);
        $stmt1 = $pdo->prepare('INSERT INTO cnes12_2019.profissional (personid, cns, dataatualizacao) VALUES(:personid, :cns, :dataatualizacao)');
        $stmt1->execute(array(
            'personid' => $personid,
            'cns' => $result1->ProfissionalSaude->CNS->numeroCNS,
            'dataatualizacao' => $result1->ProfissionalSaude->dataAtualizacao
        ));

        $indice = 0;


        $cnesArray = array();

        if (count($result1->ProfissionalSaude->CNES) > 1) {
            $cnesArray = $result1->ProfissionalSaude->CNES;
        } else {
            $cnesArray[0] = $result1->ProfissionalSaude->CNES;
        }


        foreach ($cnesArray as $cnes) {

            /**
             * -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
             * Consulta vinculação profissional
             * -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
             */


            $options2 = array( 'location' => 'https://servicos.saude.gov.br/cnes/VinculacaoProfissionalService/v1r0',
                'encoding' => 'utf-8',
                'soap_version' => SOAP_1_2,
                'connection_timeout' => 180,
                'trace'        => 1,
                'exceptions'   => 1 );
            $client2 = new SoapClient('https://servicos.saude.gov.br/cnes/VinculacaoProfissionalService/v1r0?wsdl', $options2);
            $client2->__setSoapHeaders(soapClientWSSecurityHeader('CNES.PUBLICO', 'cnes#2015public'));
            $function2 = 'pesquisarVinculacaoProfissionalSaude';
            $arguments2 = array( 'vin' => array(
                            'FiltroPesquisaVinculacaos' => array(
                                'IdentificacaoProfissional' => array(
                                    'cns' => array(
                                        'numeroCNS'  => $result1->ProfissionalSaude->CNS->numeroCNS
                                    )
                                ),
                                'IdentificacaoEstabelecimento' => array(
                                    'cnes' => array(
                                        'codigo'  => $cnes->CodigoCNES->codigo
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
            $result2 = $client2->__soapCall($function2, $arguments2);


            /**
             * -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
             * Estabelecimento
             * -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
             */

            $options3 = array( 'location' => 'https://servicos.saude.gov.br/cnes/EstabelecimentoSaudeService/v1r0',
                'encoding' => 'utf-8',
                'soap_version' => SOAP_1_2,
                'connection_timeout' => 180,
                'trace'        => 1,
                'exceptions'   => 1 );

            $client3 = new SoapClient('https://servicos.saude.gov.br/cnes/EstabelecimentoSaudeService/v1r0?wsdl', $options3);
            $client3->__setSoapHeaders(soapClientWSSecurityHeader('CNES.PUBLICO', 'cnes#2015public'));

            $function3 = 'consultarEstabelecimentoSaude';

            $arguments3 = array( 'est' => array(
                'FiltroPesquisaEstabelecimentoSaude' => array(
                    'CodigoCNES' => array(
                        'codigo'      => $result2->Vinculacaos->Vinculacao->Estabelecimento->CodigoCNES->codigo
                    )
                )
            )
            );
            $result3 = $client3->__soapCall($function3, $arguments3);

            $cboArray = array();

            if (count($result1->ProfissionalSaude->CBO) > 1) {
                $cboArray = $result1->ProfissionalSaude->CBO;

                $cbo_cod = $cboArray[$indice]->codigoCBO;
                $cbo_descricao = $cboArray[$indice]->descricaoCBO;
            } else {
                $cboArray[0] = $result1->ProfissionalSaude->CBO;

                $cbo_cod = $cboArray[0]->codigoCBO;
                $cbo_descricao = $cboArray[0]->descricaoCBO;
            }


            if ($result3->DadosGeraisEstabelecimentoSaude->Localizacao->latitude == null) {
                $geo = '';
            } else {
                $geo = $result3->DadosGeraisEstabelecimentoSaude->Localizacao->latitude . ', ' . $result3->DadosGeraisEstabelecimentoSaude->Localizacao->longitude;
            }


            $sql = 'INSERT INTO cnes12_2019.profissionalvinculo (personid, estabelecimento_codigocnes, estabelecimento_nomefantasia, estabelecimento_municipio, estabelecimento_uf, estabelecimento_geo, estabelecimento_pertencesus, estabelecimento_tipounidade, vinculo_descricao, vinculo_tipo, vinculo_subtipo, cbo_cod, cbo_descricao, estabelecimento_logradouro, estabelecimento_numero, estabelecimento_bairro, estabelecimento_complemento, estabelecimento_cep, estabelecimento_diretor_cpf, estabelecimento_diretor_nome, estabelecimento_telefone) 
VALUES(:personid, :estabelecimento_codigocnes, :estabelecimento_nomefantasia, :estabelecimento_municipio, :estabelecimento_uf, :estabelecimento_geo, :estabelecimento_pertencesus, :estabelecimento_tipounidade, :vinculo_descricao, :vinculo_tipo, :vinculo_subtipo, :cbo_cod, :cbo_descricao, :estabelecimento_logradouro, :estabelecimento_numero, :estabelecimento_bairro, :estabelecimento_complemento, :estabelecimento_cep, :estabelecimento_diretor_cpf, :estabelecimento_diretor_nome, :estabelecimento_telefone)';
            $stmt3 = $pdo->prepare($sql);

            $dados = array(
                'personid' => $personid,
                'estabelecimento_codigocnes' => $result2->Vinculacaos->Vinculacao->Estabelecimento->CodigoCNES->codigo,
                'estabelecimento_nomefantasia' => $result2->Vinculacaos->Vinculacao->Estabelecimento->NomeFantasia->Nome,

                'estabelecimento_municipio' => $result3->DadosGeraisEstabelecimentoSaude->Endereco->Municipio->nomeMunicipio,
                'estabelecimento_uf' => $result3->DadosGeraisEstabelecimentoSaude->Endereco->Municipio->UF->siglaUF,
                'estabelecimento_geo' => $geo,
                'estabelecimento_pertencesus' => $result3->DadosGeraisEstabelecimentoSaude->perteceSistemaSUS == 1 ? 'SIM' : 'NÃO',
                'estabelecimento_tipounidade' => $result3->DadosGeraisEstabelecimentoSaude->tipoUnidade->descricao,

                'vinculo_descricao' => $result2->Vinculacaos->Vinculacao->desCodigoModVinculo,
                'vinculo_tipo' => $result2->Vinculacaos->Vinculacao->desTipoCodigoModVinculo,
                'vinculo_subtipo' => $result2->Vinculacaos->Vinculacao->desSubTipoCodigoModVinculo,

                'cbo_cod' => $cbo_cod,
                'cbo_descricao' => $cbo_descricao,

                'estabelecimento_logradouro' => $result3->DadosGeraisEstabelecimentoSaude->Endereco->nomeLogradouro,
                'estabelecimento_numero' => $result3->DadosGeraisEstabelecimentoSaude->Endereco->numero,
                'estabelecimento_bairro' => $result3->DadosGeraisEstabelecimentoSaude->Endereco->Bairro->descricaoBairro,
                'estabelecimento_complemento' => empty($result3->DadosGeraisEstabelecimentoSaude->Endereco->complemento) ? '' : $result3->DadosGeraisEstabelecimentoSaude->Endereco->complemento,
                'estabelecimento_cep' => $result3->DadosGeraisEstabelecimentoSaude->Endereco->CEP->numeroCEP,


                'estabelecimento_diretor_cpf' => !empty($result3->DadosGeraisEstabelecimentoSaude->Diretor) ?  $result3->DadosGeraisEstabelecimentoSaude->Diretor->CPF->numeroCPF : '',
                'estabelecimento_diretor_nome' => !empty($result3->DadosGeraisEstabelecimentoSaude->Diretor) ?  $result3->DadosGeraisEstabelecimentoSaude->Diretor->nome->Nome : '',
                'estabelecimento_telefone' =>  !empty($result3->DadosGeraisEstabelecimentoSaude->Telefone) ? $result3->DadosGeraisEstabelecimentoSaude->Telefone->DDD . $result3->DadosGeraisEstabelecimentoSaude->Telefone->numeroTelefone : ''
            );
            $stmt3->execute($dados);

            $indice++;
        }


    } catch (Exception  $e) {
        print $e->getMessage();
    }

    echo '<hr>';
}

} catch (PDOException  $e) {
    print $e->getMessage();
}
/*





?>

</body>
</html>