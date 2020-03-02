<?php

class frm#Form extends MForm
{
    public $grid;

    public function __construct()
    {
        parent::__construct(_M('#title', MIOLO::getCurrentModule()));
        $this->eventHandler();
    }

    public function createFields()
    {
        $module = MIOLO::getCurrentModule();
        $fields[] = MMessage::getMessageContainer();
#filters
        $fields[] = new MDiv(NULL, new MButton('searchButton', _M('Search')), NULL, 'align=center');

        $MIOLO = MIOLO::getInstance();
        $#table = $MIOLO->getBusiness('#module', '#table');
        $this->grid = $this->manager->getUI()->getGrid('#module','grd#Grid');
        $this->grid->setData($#table->search());
        
        $fields[] = $this->grid;
        $this->setFields($fields);

        $buttons[] = new MBackButton();
        $this->setButtons($buttons);
    }

    public function searchButton_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $data = $this->getData();

        $#table = $MIOLO->getBusiness('#module', '#table');
        $this->grid->setData($#table->search($data));
    }
}

?>