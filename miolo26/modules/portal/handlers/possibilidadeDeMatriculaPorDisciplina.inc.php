<?php

$MIOLO->checkAccess('DocPossibilidadeDeMatriculaPorDisciplina', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Possibilidades de matrícula por disciplina', $module), $module, $self);
$form = $ui->getForm($module, 'frmPossibilidadeDeMatriculaPorDisciplina');
$theme->insertContent($form);

?>
