<?php
class MButtonFind extends MInputButton
{
    public function __construct($action = '')
    {
        parent::__construct('', '', $action);
        $this->setClass('mButtonFind');
    }
}
?>