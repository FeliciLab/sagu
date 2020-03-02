<?php
class MButtonClose extends MLinkButton
{
    public function __construct($action)
    {
        $MIOLO = MIOLO::getInstance();

        parent::__construct('', '', '','&nbsp;');
        $history = $this->manager->history;

        if ($action == 'back')
            $action = $history->back('action');

        elseif ($action == 'backContext')
            $action = $history->back('context');

        $this->setHREF($action);
        $div = new MDiv('','','m-button-close-up');
        $div->getBox()->setAttribute("onmousedown","this.className='m-button-close-down'");
        $div->getBox()->setAttribute("onmouseout","this.className='m-button-close-up'");
        $this->caption = $div->generate();
    }

    public function generateInner()
    {
        parent::generateInner();
    }
}

class MButtonMinimize extends MDiv
{
    public function __construct($action)
    {
        $MIOLO = MIOLO::getInstance();

        parent::__construct('','','m-button-minimize-up');
        $this->getBox()->setAttribute("onmousedown","this.className='m-button-minimize-down';");
        $this->getBox()->setAttribute("onmouseout","this.className='m-button-minimize-up'");
    }

    public function onMouseUp($boxId)
    {
        $this->getBox()->setAttribute("onmouseup","miolo.box.closeBox( event,'{$boxId}');");
    }

    public function generateInner()
    {
        $this->inner = $this->getRender('anchor');
    }
}

class MButtonHelp extends MDiv
{
    public function __construct($help)
    {
        $MIOLO = MIOLO::getInstance();

        parent::__construct('', '', 'm-button-help-up');

        $this->getBox()->setAttribute("onmousedown", "this.className='m-button-help-down';");
        $this->getBox()->setAttribute("onmouseup", $help);
        $this->getBox()->setAttribute("onmouseout", "this.className='m-button-help-up'");
        $this->page->addScript('m_help.js');
//        $this->page->addStyle('m_help.css');
    }

    public function generateInner()
    {
        $this->inner = $this->getRender('anchor');
    }
}

class MButtonFind extends MInputButton
{
    public function __construct($action = '')
    {
        parent::__construct('', '', $action);
        $this->setClass('m-button-find');
    }
}

class MLinkBack extends MLink
{
    public function __construct($text = 'Voltar', $href = '')
    {
        global $history;

        if ($href == '')
            $href = $history->back('action');

        parent::__construct('', '', $href, $text);
    }
}

class MOpenWindow extends MLink
{
    public function __construct($name = NULL, $label = NULL, $href = NULL, $target = '')
    {
        parent::__construct($name, $label, $href);
        $this->target = ($target == '') ? 'mioloWindow' : $target;
        $this->setOnClick("miolo.doWindow('{$this->href}')");
    }
}

class MButtonWindow extends MButton
{
    public function __construct($name = NULL, $label = NULL, $href = NULL, $target = '')
    {
        $action = "miolo.doWindow('{$href}')";
        parent::__construct($name, $label, $action);
        $this->target = ($target == '') ? 'mioloWindow' : $target;
    }
}
?>