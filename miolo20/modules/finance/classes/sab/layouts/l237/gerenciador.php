<?php
/**
 * Este arquivo (gerenciador.php) recomenda-se estar no mesmo local onde é salvo os arquivos gerados pelo SAB
 */

//Recebe do M.U.P. o ID da ordem (que é o nome do arquivo)
$params[] = $_REQUEST;

//Caminho dos arquivos gerados pelo SAB (no caso na mesma pasta onde está este arquivo(gerenciador.php)
$diretorio = dirname(__FILE__) . "/../../../miolo20/modules/basic/upload/".$params[0]['numOrder'].".txt";

//Retorna o arquivo para o M.U.P
echo file_get_contents($diretorio);
?>
