<?php
class MButtonWindow extends MButton
{
    public function __construct($name = NULL, $label = NULL, $href = NULL, $target = '')
    {
        parent::__construct($name, $label, $href);
        $this->target = ($target == '') ? 'mioloWindow' : $target;
        $this->setActionType('WINDOW:'. $href);
    }
}
?>