<?php
class MCompoundForm extends MForm
{
    public $_info = array();
    public $_panel = array();
    public $_form = array();

    public function __construct($title = '', $action = '', $close = 'backContext', $icon = '')
    {
        parent::__construct($title, $action, $close, $icon);
        $this->defaultButton = false;
        $this->compoundFields();
    }

    public function compoundFields()
    {
        $this->clearControls();
        $this->fields = array();

        foreach ($this->_info as $f)
        {
            $this->addField($f);
        }

        $this->addField(new MSpacer());

        foreach ($this->_panel as $f)
        {
            $this->addField($f);
            $this->addField(new MSpacer());
        }

        foreach ($this->_form as $f)
        {
            $this->addField($f);
            $this->addField(new MSpacer());
        }
    }

    public function generateInner()
    {
        if (!isset($this->buttons))
        {
            if ($this->defaultButton)
            {
                $this->buttons[] = new MButton(FORM_SUBMIT_BTN_NAME, _M("Send"), 'SUBMIT');
            }
        }

        $body = $this->generateBody();
        $footer = $this->generateFooter();
        $this->formBox->setControls(array($body, $footer));
        $this->formBox->setClass("mFormOuter");
        $this->setClass("mForm");
        $this->inner = $this->formBox;
    }
}
?>