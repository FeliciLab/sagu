<?php
class MTabContainer extends MContainerControl
{
    protected $options;

    public function __construct( $name = NULL, $content = '&nbsp;', $class = NULL, $attributes = NULL )
    {
        parent::__construct( $name, $content, $ckass, $attributes );
        $this->options = new MStringList();
        $this->height = '150px';
    }

    public function addPanel($id, $title, $content)
    {
        $div = new MDiv('', $this->painter->generateToString($content));
        $div->addAttribute("title", $title);
        $div->addAttribute("dojoType", "dijit.layout.ContentPane");
        $this->addControl($div);
    }

    public function addOption($option, $value)
    {
        $this->options->addValue($option, $value);
    }

    public function generate()
    {
        $this->page->addDojoRequire('dijit.layout.ContentPane');
        $this->page->addDojoRequire('dijit.layout.TabContainer');
        $this->addAttribute("dojoType","dijit.layout.TabContainer");
        $this->page->onload("dojo.parser.parse(\"dojo.byId('{$this->id}')\");");
        $this->width = '100%';
        $this->setInner( $this->getControls() );
        return parent::generate();
    }

}
?>