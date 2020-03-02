<?php
$navbar->setHome($module);
$navbar->setLabelHome(_M('Contas a pagar', $module));

$theme->clearContent();

$painel = new MActionPanel('pnlContaspagar', _M('Contas a pagar', $module));


$theme->appendContent($painel);

// Inclui o manipulador.
$chave = MIOLO::_REQUEST('chave');

if ( strlen($chave) > 0 )
{
    $MIOLO->uses('handlers/manipulador.inc.php', 'base');
}
else
{
    $shiftAction = $context->shiftAction();

    if ( $shiftAction )
    {
        $MIOLO->invokeHandler($module, $shiftAction);
    }
}
?>
