<?php

$module = MIOLO::getCurrentModule();
$title = _M('Alunos da turma', $module);
$managementForm = 'FrmAlunosDaTurma';
$searchForm = 'FrmAlunosDaTurma';

SAGU::handle($module, $action, $title, $searchForm, $managementForm, array('transactionName' => 'FrmOfertaDeCurso'));

?>
