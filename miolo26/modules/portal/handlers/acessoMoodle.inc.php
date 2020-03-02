<?php
/**
 *
 * @author Fabiano da Silva Fernandes [contato@fabianofernandes.adm.br]
 *
 * @since
 * Class created on 08/10/2013
 */

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Moodle', $module), $module, $self);
$form = $ui->getForm($module, 'frmAcessoMoodle');
$theme->insertContent($form);
?>
