<?php
class HandlerAdmin extends MHandler
{
    public function init()
    {
        parent::init();
        $this->manager->trace(__METHOD__);
    }
}
?>