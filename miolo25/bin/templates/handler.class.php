<?php

class Handler#Module extends MHandler
{
    public function init()
    {
        parent::init();

        $this->manager->trace('Handler#Module:init');
    }
}

?>