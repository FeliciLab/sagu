<?php
class MButtonMinimize extends MLinkButton
{
    public function __construct($action)
    {
        parent::__construct('', '', '','&nbsp;');
    }

    public function onMouseUp($boxId)
    {
//        $this->setAttribute("onmouseup","miolo.box.closeBox( event,'{$boxId}');");
    }

    public function generateLink()
    {
        $div = new MDiv('','','mButtonMinimizeUp');
        $div->addEvent('mousedown',"event.currentTarget.className='mButtonMinimizeDown';");
        $div->addEvent('mouseout',"event.currentTarget.className='mButtonMinimizeUp';");
        $div->addEvent('mouseup',"event.currentTarget.className='mButtonMinimizeUp';");
        $this->caption = $div->generate();
    }
}

?>