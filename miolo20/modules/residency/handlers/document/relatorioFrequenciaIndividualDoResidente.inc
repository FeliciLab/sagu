<?php

$module = SAGU::getFileModule(__FILE__);
$action = MIOLO::getCurrentAction();
$title = _M('Relatório de frequência do residente', $module);
$form = 'FrmRelatorioFrequenciaIndividualDoResidente';

SAGU::handle($module, $action, $title, $form, 'FrmRelatorioFrequenciaIndividualDoResidente', array('checkAccess' => false));