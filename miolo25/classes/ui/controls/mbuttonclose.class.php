<?php
class MButtonClose extends MLinkButton
{
    public function __construct($action)
    {
        parent::__construct('', '', '','&nbsp;');
        $history = $this->manager->history;

        if ($action == 'back')
        {
            $action = $history->back('action');
        }
        elseif ($action == 'backContext')
        {
            $action = $history->back('context');
        }

        $this->setHREF($action);

        $div = new MDiv('','','mButtonCloseUp');
        $div->addEvent('mousedown',"event.currentTarget.className='mButtonCloseDown';");
        $div->addEvent('mouseout',"event.currentTarget.className='mButtonCloseUp';");
        $div->addEvent('mouseup',"event.currentTarget.className='mButtonCloseUp';");
        $this->caption = $div->generate();
    }
}
?>