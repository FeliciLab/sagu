<?php

$module = MIOLO::getCurrentModule();
$action = 'main:process:exportarDadosContabeis';
$title = _M('Exportar dados cont�beis', $module);
$managementForm = 'FrmExportarDadosContabeis';
$searchForm = 'FrmExportarDadosContabeis';

SAGU::handle($module, $action, $title, $searchForm, $managementForm);

?>