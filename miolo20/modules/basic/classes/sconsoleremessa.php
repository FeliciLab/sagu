<?php
require 'sconsole.php';

SDatabase::beginTransaction();

$cob = new FinCobrancaBancaria();
$lastInsertId = $cob->getLastInsertId();

//SDatabase::execute("UPDATE finbankaccount SET geracaodonossonumeropelobanco = TRUE"); // testar com TRUE e FALSE

$filtros = new stdClass();
FinCobrancaBancaria::gerarArquivoBradesco($filtros);

// apaga configuracoes
$newLastInsertId = $cob->getLastInsertId();

if ( $newLastInsertId != $lastInsertId )
{
    $cobranca = new FinCobrancaBancaria($newLastInsertId);
    $cobranca->delete();
}

SDatabase::rollback();
?>
