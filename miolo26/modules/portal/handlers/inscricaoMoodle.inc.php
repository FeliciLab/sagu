<?php

$MIOLO->checkAccess('FrmMoodleProfessor', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Inscrição no Moodle', $module), $module, $self);
$form = $ui->getForm($module, 'frmInscricaoMoodle');
$theme->insertContent($form);
?>
