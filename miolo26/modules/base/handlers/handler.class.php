<?php
class HandlerBase extends MHandler
{
    function init()
    {
        parent::init();
	$MIOLO = MIOLO::getInstance();
	if ($MIOLO->getConf('options.miolo2modules'))
	{
            $this->manager->uses('classes/sAutoload.class', 'basic');
            $sAutoload = new sAutoload();
            $sAutoload->definePaths();
	}
    }
}
?>
