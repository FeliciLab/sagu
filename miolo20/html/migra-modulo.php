<?php
    ini_set('display_errors', true);

    try {
        $pdo = new PDO('pgsql:host=10.17.0.209;dbname=sagu', 'postgres', 'postgres');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $consulta = $pdo->query("select * from miolo_transaction where idmodule = 'resmedica' and parentm_transaction <> 'medDocument'  order by idtransaction");

        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $m_transaction = $linha['m_transaction'];
            $nametransaction = $linha['nametransaction'];
            $idmodule = $linha['idmodule'];
            $parentm_transaction = $linha['parentm_transaction'];
            $action = $linha['action'];

            if ($m_transaction == 'RESMEDICA') {
                $m_transaction = 'APS';
            }
            $m_transaction = str_replace('med', 'aps', $m_transaction);
            $m_transaction = str_replace('Med', 'Aps', $m_transaction);

            if ($m_transaction == 'FrmIndicador' || $m_transaction == 'FrmResidentesPorTurma') {
                $m_transaction = $m_transaction . 'Aps';
            }

            if ($nametransaction == 'Residência médica') {
                $nametransaction = 'Atenção Primária à Saúde';
            }

            if ($idmodule == 'resmedica') {
                $idmodule = 'aps';
            }

            if ($parentm_transaction == 'RESMEDICA') {
                $parentm_transaction = 'APS';
            }
            $parentm_transaction = str_replace('med', 'aps', $parentm_transaction);
            $parentm_transaction = str_replace('Med', 'Aps', $parentm_transaction);


            $sqlInsert = "INSERT INTO miolo_transaction (m_transaction, nametransaction, idmodule, parentm_transaction,  action) VALUES ('".$m_transaction."', '".$nametransaction."', '".$idmodule."', '".$parentm_transaction."', '".$action."')";
            $pdo->exec($sqlInsert);

        }


    } catch(PDOException $e) {
        print_r($e);
    }

?>