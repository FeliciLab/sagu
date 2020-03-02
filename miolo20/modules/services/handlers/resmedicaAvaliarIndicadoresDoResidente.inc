<?php

$action = MIOLO::getCurrentAction();
$module = SAGU::getFileModule(__FILE__);

SAGU::handle('resmedica', $action, _M('Avaliar indicadores do residente', $module), 'FrmAvaliarIndicadoresDoResidente', 'FrmAvaliarIndicadoresDoResidente', array('checkAccess' => false));
?>