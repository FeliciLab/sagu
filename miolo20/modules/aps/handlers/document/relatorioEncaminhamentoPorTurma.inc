<?php

$module = SAGU::getFileModule(__FILE__);
$action = MIOLO::getCurrentAction();
$title = _M('Relatório de Procedimentos x encaminhamentos', $module);
$form = 'FrmRelatorioEncaminhamentosPorTurma';

SAGU::handle($module, $action, $title, $form, 'FrmRelatorioEncaminhamentosPorTurma', array('checkAccess' => false));