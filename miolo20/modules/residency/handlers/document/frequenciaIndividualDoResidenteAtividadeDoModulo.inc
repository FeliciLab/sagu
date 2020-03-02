<?php

$module = SAGU::getFileModule(__FILE__);
$action = MIOLO::getCurrentAction();
$title = _M('Frequência individual do residente na oferta de atividade do módulo', $module);
$form = 'FrmFrequenciaIndividualDoResidenteAtividadeDoModulo';

SAGU::handle($module, $action, $title, $form, 'FrmFrequenciaIndividualDoResidenteAtividadeDoModulo', array('checkAccess' => false));
?>