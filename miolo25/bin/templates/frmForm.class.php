<?php

class frm#Form extends MForm
{
    public function __construct()
    {
        parent::__construct(_M('#title', MIOLO::getCurrentModule()));
        $this->eventHandler();
    }

    public function createFields()
    {
        $module = MIOLO::getCurrentModule();

        $fields[] = MMessage::getMessageContainer();
#fields
        $this->setFields($fields);

        $buttons[] = new MBackButton();
        $buttons[] = new MButton('submit_button', _M('Send'));
        $this->setButtons($buttons);
    }

    public function submit_button_click()
    {
        $module = MIOLO::getCurrentModule();
        $data = '<br/><pre>' . print_r($this->getData(), 1) . '</pre>';

        new MMessageWarning(_M('This is a warning message with the form data', $module) . $data);
    }
}

?>