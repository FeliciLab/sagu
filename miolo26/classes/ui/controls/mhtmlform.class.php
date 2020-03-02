<?php
class MHtmlForm extends MFormControl
{
    public $enctype;
    public $content;
    public $action;
    public $onsubmit;
    public $onload;

    public function __construct($name)
    {
        parent::__construct($name);
    }

    public function setEnctype($enctype)
    {
        $this->enctype = $enctype;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function addContent($content)
    {
        $this->content[] = $content;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function setOnSubmit($onsubmit)
    {
        $this->onsubmit = $onsubmit;
    }

    public function getOnSubmit()
    {
        return $this->onsubmit;
    }

    public function setOnLoad($onload)
    {
        $this->onload = $onload;
    }

    public function getOnLoad()
    {
        return $this->onload;
    }

    public function generate()
    {
        return $this->getRender('form');
    }
}
?>