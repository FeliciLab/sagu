<?php
set_time_limit(0);
require dirname(__FILE__) . '/sconsole.php';


# for i in `seq 0 1000 4000`; do php miolo20/modules/basic/classes/testes_unitarios.php 1000 $i > REMESSA$i.REM; done;
# for i in `seq 0 500 3000`; do php miolo20/modules/basic/classes/testes_unitarios.php 500 $i > REMESSA$i.REM; done;

# tar cfzv remessa.tar.gz *.REM

//$filtros = new stdClass();
//$filtros->limit = $argv[1];
//$filtros->offset = $argv[2];
//$filtros->beginMaturityDate = '01/09/2013';
//$filtros->endMaturityDate = '15/09/2013';

//FinCobrancaBancaria::gerarArquivoBanrisul($filtros);

//BasDocumentoGerado::removerArquivosAntigos($bst);
?>
