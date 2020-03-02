<?php

$module = MIOLO::getCurrentModule();
$title = _M('Turmas encerradas', $module);
$managementForm = 'FrmTurmasEncerradas';
$searchForm = 'FrmTurmasEncerradas';

SAGU::handle($module, $action, $title, $searchForm, $managementForm, array('transactionName' => 'FrmOfertaDeCurso'));

?>
