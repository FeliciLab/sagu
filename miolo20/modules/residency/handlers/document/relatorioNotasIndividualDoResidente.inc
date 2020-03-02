<?php

$module = SAGU::getFileModule(__FILE__);
$action = MIOLO::getCurrentAction();
$title = _M('Relatório de notas do residente', $module);
$form = 'FrmRelatorioNotasIndividualDoResidente';

SAGU::handle($module, $action, $title, $form, 'FrmRelatorioNotasIndividualDoResidente', array('checkAccess' => false));