<?php
class MButtonHelp extends MLinkButton
{
    public function __construct($help)
    {
        $MIOLO = MIOLO::getInstance();

        parent::__construct('', '', 'mButtonHelpUp');

        $this->getBox()->setAttribute("onmousedown", "this.className='mButtonHelpDown';");
        $this->getBox()->setAttribute("onmouseup", $help);
        $this->getBox()->setAttribute("onmouseout", "this.className='mButtonHelpUp'");
        $this->page->addScript('m_help.js');
    }

    public function generateInner()
    {
        $this->inner = $this->getRender('anchor');
    }
}
?>