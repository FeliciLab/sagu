<?php

$module = MIOLO::getCurrentModule();
$title = _M('Tipo de docente', $module);
$managementForm = 'FrmTipoDeDocente';
$searchForm = 'FrmTipoDeDocenteSearch';

SAGU::handle($module, $action, $title, $searchForm, $managementForm, array('transactionName' => 'FrmTipoDeDocente'));

