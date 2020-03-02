<?php
class MFormDialog extends MFormAJAX
{
    public $linkClose;
    public $linkFree;
    protected $commands;

    public function __construct($title='',$action='',$close='',$icon='')
    {
        $this->linkClose = 'javascript:miolo.iFrame.object.close();';
        $this->linkFree = 'javascript:miolo.iFrame.object.free();';
        parent::__construct($title,$action,$close,$icon);
        $this->page->addScript('x/x_event.js');
        $this->page->addScript('x/x_drag.js');
        $this->page->addScript('m_iframe.js');
        $this->manager->getTheme()->setLayout('popup');
        $title = $this->getId() . '_title';
        $this->box->boxTitle->setId($title);
        $this->page->addJsCode("miolo.iFrame.base = window.parent.miolo.iFrame.base;");
        $this->page->addJsCode("miolo.iFrame.parent = window.parent;");
        $this->page->addJsCode("miolo.iFrame.object =  miolo.iFrame.getById(frameElement.id);");
        $this->page->addJsCode("miolo.iFrame.object.dragElement =  '{$title}';");
        $this->addField(new MHiddenField('dialogCommands',''));
        $this->addField(new MHiddenField('dialogStatus','opened'));
        $this->defaultButton = false;
        $this->page->onload("miolo.iFrame.object.onload();");
    }

    public function getClose()
    {
        return "this.close();";
    }

    public function getFree()
    {
        return "this.free();";
    }

	function close()
    {
        $this->commands .= $this->getClose();
    }

	function free()
    {
        $this->commands .= $this->getFree();
    }

    public function setParentFieldValue($field,$value)
    {
        $this->commands .= "this.parentField('{$field}','{$value}');";
    }

    public function getParentFieldValue($parentField, $field)
    {
        $this->commands .= "this.getField('$field').value  = this.parentField('{$parentField}');";
    }

    public function setPosition($top, $left)
    {
        $this->commands .= "this.setPosition({$top},{$left});";
    }

    public function generate()
    {
        $this->setFieldValue('dialogCommands', $this->commands);
        $this->setClose($this->linkClose);
        return parent::generate();
    }

}
?>
