<?php

//$MIOLO->checkAccess('FrmGradeHorarios', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Grade de horários', $module), $module, $self);
$form = $ui->getForm($module, 'frmGradeHorario');
$theme->insertContent($form);

?>
