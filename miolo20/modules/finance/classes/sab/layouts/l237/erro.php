<?php
/**
 * Este é o arquivo o qual o M.U.P irá chamar em caso de erro. (Recomenda-se colocar junto com gerenciador.php)
 */
 
 // Recebe dados do M.U.P
$vars[] = $_REQUEST;

// Retorna na tela
echo "OCORREU UM ERRO!";
echo "<pre>";
var_dump($vars);

if ( $vars[0]['cod'] == "-503" )
{
    echo "<br><hr>Provavelmente o arquivo \"".$vars[0]['OrderId'].".txt\" n&atilde;o foi encontrado...<hr>";
}
else
{
	echo "<br><hr>".$vars[0]['errordesc']."<hr>";
}
?>
