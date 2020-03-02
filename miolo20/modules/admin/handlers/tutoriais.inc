<?php

$module = SAGU::getFileModule(__FILE__);
$function = MIOLO::_REQUEST('function');
$ui = $MIOLO->getUI();

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$navbar->addOption(_M('Tutoriais', $module), $module, $action);

echo '<br><br><br>';
echo '<h1 style="font-size: 20px;">Vídeo tutoriais</h1><hr>';

$pastas = array('comum' => 'Vídeos comuns - processos básicos', 'curso' => 'Vídeos - Avaliação por curso', 'modulo' => 'Vídeos - Avaliação por módulo', 'unidade' => 'Vídeos - Avaliação por unidade');

foreach ($pastas as $pasta => $titulo) {
    $comuns   = 'tutoriais/'.$pasta.'/';
    $arquivosComuns = scandir($comuns);
    echo "<span style='font-size: 20px;'>{$titulo}</span><br>";

    foreach ($arquivosComuns as $arquivoComum) {
        $arquivoComum = utf8_decode($arquivoComum);
        if (strlen($arquivoComum) > 2) {
            echo "<a target='_blank' href='tutoriais/{$pasta}/{$arquivoComum}'>" . str_replace('.ogv', '', strtoupper($arquivoComum)) ."</a><br>";
        }
    }
    echo '<hr>';
}






?>
