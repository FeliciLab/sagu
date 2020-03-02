<?php

$module = SAGU::getFileModule(__FILE__);
$action = MIOLO::getCurrentAction();
$title = _M('Residentes por turma', $module);
$form = 'FrmResidentesPorTurma';

SAGU::handle($module, $action, $title, $form, 'FrmResidentesPorTurma');
?>