<?php

$module = SAGU::getFileModule(__FILE__);
$action = MIOLO::getCurrentAction();
$title = _M('Relat�rio de faltas de atividades praticas por turma', $module);
$form = 'FrmRelatorioDeFaltasDeAtividadesPraticaPorTurma';

SAGU::handle($module, $action, $title, $form, 'FrmRelatorioDeFaltasDeAtividadesPraticaPorTurma', array('checkAccess' => false));
?>