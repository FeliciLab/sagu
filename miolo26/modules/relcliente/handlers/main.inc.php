<?php
$navbar->setHome($module);
$navbar->setLabelHome(_M('Relacionamento com cliente', $module));

$theme->clearContent();

$painel = new MActionPanel('pnlRelcliente', _M('Relacionamento com cliente', $module));


$theme->appendContent($painel);

// Inclui o manipulador.
$MIOLO->uses('handlers/manipulador.inc.php', 'base');
?>
