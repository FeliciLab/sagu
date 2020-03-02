<?php
    ini_set('display_errors', true);
    $con = new PDO("pgsql:host=localhost;dbname=sagu3", "postgres", "Victor81854778");

    if (!$con) {
        echo 'não conectou';
    }

    $nome_instituicao2 = utf8_encode("'nome_instituição'::varchar");
    $sql = "
              SELECT personid,
                     getcustomvalue('cidade'::varchar, personid::varchar) as cidade,
                    getcustomvalue('location'::varchar, personid::varchar) as location,
                    getcustomvalue('complemento'::varchar, personid::varchar) as complemento,
                    getcustomvalue('cpe'::varchar, personid::varchar) as cep,
                    getcustomvalue('dt_inicio'::varchar, personid::varchar) as dt_inicio,
                    getcustomvalue(".$nome_instituicao2.", personid::varchar) as nome_instituicao,
                    getcustomvalue('cargo'::varchar, personid::varchar) as cargo,
                    getcustomvalue('trabalha_casa'::varchar, personid::varchar) as trabalha_casa,
                    getcustomvalue('tipo_vinculo'::varchar, personid::varchar) as tipo_vinculo,
                    getcustomvalue('tp_vinculo'::varchar, personid::varchar) as tp_vinculo,
                    getcustomvalue('categoria_profissional'::varchar, personid::varchar) as categoria_profissional,
                    getcustomvalue('cat_profissional'::varchar, personid::varchar) as cat_profissional
                FROM basphysicalperson
              WHERE getcustomvalue('cidade'::varchar, personid::varchar) is not null
                GROUP BY personid
                ORDER BY personid
	";
    $query1 = $con->query($sql);

    $qtd = 0;
    $qtdIbge = 0;
    $qtdVinculo = 0;
    $qtdCategoria = 0;
    while($row = $query1->fetch(PDO::FETCH_OBJ)){

        if ($row->cidade !=  '') {
            $qtd++;
            $cidade = formata($row->cidade);
            $cidadeExplode = explode('/', $cidade);
            $cidade = $cidadeExplode[0];

            $set = '';

            if (!empty($row->cep) && $row->cep != '') {
                $cep = trim(str_replace('-', '', $row->cep));
                $set .= ", zipcodework = '".$cep."'";
            }

            if (!empty($row->location) && $row->location != '') {
                $location = $row->location;
                $set .= ", locationwork = '".$location."'";
            }

            if (!empty($row->complemento) && $row->complemento != '') {
                $complemento = $row->complemento;
                $set .= ",  complementwork = '".$complemento."'";
            }

            if (!empty($row->nome_instituicao) && $row->nome_instituicao != '') {
                $nome_instituicao = $row->nome_instituicao;
                $set .= ", workemployername = '".$nome_instituicao."'";
            }

            if (!empty($row->cargo) && $row->cargo != '') {
                $cargo = $row->cargo;
                $set .= ", workfunction = '".$cargo."'";
            }

            if (!empty($row->dt_inicio) && $row->dt_inicio != '') {
                $dataInicioExplode = explode('/', $row->dt_inicio);
                $dataInicio = $dataInicioExplode[2].'-'.$dataInicioExplode[1].'-'.$dataInicioExplode[0];
                $set .= ", workstartdate = '".$dataInicio."'";
            }

            if (!empty($row->trabalha_casa) && $row->trabalha_casa != '') {
                if ($row->trabalha_casa == 'SIM') {
                    $trabalhaEmCasa = 't';
                    $set .= ", workathome = '" . $trabalhaEmCasa."'";
                } else if (utf8_decode($row->trabalha_casa) == 'NÃO') {
                    $trabalhaEmCasa = 'f';
                    $set .= ", workathome = '" . $trabalhaEmCasa."'";
                }
            }

            $query2 = $con->query("SELECT cityid, name, ibgeid FROM bascity WHERE stateid = 'CE'");

            $cidadeOriginal = null;
            $iDCidadeOriginal = null;
            $ibgeOriginal = null;
            while($row2 = $query2->fetch(PDO::FETCH_OBJ)) {
                if (trim(formata($row2->name)) == trim($cidade)) {
                    $cidadeOriginal = $row2->name;
                    $iDCidadeOriginal = $row2->cityid;
                    $ibgeOriginal = $row2->ibgeid;
                    $qtdIbge++;
                    break;
                }
            }

            // Endereço do trabalho
            $sqlUpdate = "UPDATE basphysicalperson
                          SET
                          cityidwork = ".$iDCidadeOriginal."
                          ".$set."
                          WHERE personid = " . $row->personid;
            $con->exec($sqlUpdate);
            //echo $row->personid . '/' . $cidade . ' | <span style="color:#F00;">' . $iDCidadeOriginal . '/' . $cidadeOriginal . '/' . $ibgeOriginal . '</span><br>';
        }

        // TIPO DE VINCULO
        $sqlInsertVinculo = null;
        if (!empty($row->tipo_vinculo) && $row->tipo_vinculo != '') {
            $sqlInsertVinculo = "INSERT INTO miolo_custom_value (customized_id, custom_field_id, value) VALUES ('".$row->personid."', '38', '".$row->tipo_vinculo."')";
            $con->exec($sqlInsertVinculo);
            $sqlInsertVinculo = "INSERT INTO miolo_custom_value (customized_id, custom_field_id, v alue) VALUES ('".$row->personid."', '55', '".$row->tipo_vinculo."')";
            $con->exec($sqlInsertVinculo);
            $sqlInsertVinculo = "INSERT INTO miolo_custom_value (customized_id, custom_field_id, value) VALUES ('".$row->personid."', '50', '".$row->tipo_vinculo."')";
            $con->exec($sqlInsertVinculo);
        }
        //echo 'Pessoa: ' . $row->personid . ' | Antigo: ' . $row->tipo_vinculo . '/ <span style="color:#F00;">Novo:</span> <span style="color:blue;">' . $row->tp_vinculo . '</span><br>';


        // CATEGORIA PROFISSIONAL
        $sqlInsertCategoria = null;
        if (!empty($row->categoria_profissional) && $row->categoria_profissional != '') {
            $sqlInsertCategoria = "INSERT INTO miolo_custom_value (customized_id, custom_field_id, value) VALUES ('".$row->personid."', '54', '".$row->categoria_profissional."')";
            $con->exec($sqlInsertCategoria);
            $sqlInsertCategoria = "INSERT INTO miolo_custom_value (customized_id, custom_field_id, value) VALUES ('".$row->personid."', '41', '".$row->categoria_profissional."')";
            $con->exec($sqlInsertCategoria);
            $sqlInsertCategoria = "INSERT INTO miolo_custom_value (customized_id, custom_field_id, value) VALUES ('".$row->personid."', '37', '".$row->categoria_profissional."')";
            $con->exec($sqlInsertCategoria);
        }
        //echo 'Pessoa: ' . $row->personid . ' | Antigo: ' . $row->categoria_profissional . '/ <span style="color:#F00;">Novo:</span> <span style="color:blue;">' . $row->cat_profissional . '</span><span style="color:green;">' . $sqlInsertCategoria . '</span> <br>';

    }

    function formata($string) {
        $map = array('á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ü' => 'u', 'ç' => 'c', 'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'É' => 'E', 'Ê' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ç' => 'C', '´' => '');
        return strtoupper(strtr(utf8_decode($string), $map));
    }

?>