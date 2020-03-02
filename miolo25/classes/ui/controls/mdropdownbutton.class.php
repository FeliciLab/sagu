<?php
class MDropDownButton extends MButton
{
    protected $itens;
    public $name;
    public $label;
    public $iconClass;
    public $unload;

    public function __construct( $name = '', $label = '', $iconClass = '')
    {
        parent::__construct( $name, $label );    
        $this->name = $name;
        $this->label = $label;
        $this->iconClass = $iconClass;
        $this->unload = '';
    }
    
    public function addItem($label, $iconClass, $onclick)
    {
        $div = new MDiv('', $label, NULL, "dojoType=\"dijit.MenuItem\"  iconClass=\"{$iconClass}\"");
        $div->addAttribute('onclick', $onclick);
        $this->itens[] = $div;
//        $this->unload .= "dijit.byId('{$div->id}').destroy();";
    }
    
    public function addSubMenu($menu, $label, $onClick, $iconClass)
    {
        $id = $menu->getId() . '_submenu';
        
        $span = new MSpan( '', $label );
        
        $content = $span->generate() . $menu->generate();
        
        $div = new MDiv( $id, $content, NULL, "dojoType=\"dijit.PopupMenuItem\"" );
        $div->setAttribute( "iconClass", $iconClass );
        
        $this->itens[] = $div;
    }

    public function addSeparator()
    {
        $this->itens[] = new MDiv('', '', NULL, "dojoType=\"dijit.MenuSeparator\"");
    }
    
    public function generateInner()
    {
        $this->page->addDojoRequire("dijit.form.Button");
        $this->page->addDojoRequire("dijit.Menu");

        if ( count($this->itens) > 0 )
        {
            $menu = new MDiv("{$this->name}_menu", $this->itens, NULL, "dojoType=\"dijit.Menu\"");
        }
        
        $label = new MSpan('', $this->label);
        
        $div = new MDiv($this->name, array($label, $menu), NULL, "dojoType=\"dijit.form.DropDownButton\" iconClass=\"{$this->iconClass}\"");

        $this->inner = new MDiv("{$this->name}_div", $div);
//        $jsCode =  "dojo.parser.parse(\"dojo.byId('{$this->name}')\");";
//        $jsCode =  "if (dijit.byId('{$this->name}')) {dijit.byId('{$this->name}').destroy();}";
//        $this->unload .=  "dijit.byId('{$this->name}').destroy();";
//        $this->unload .=  "dijit.byId('{$this->name}_menu').destroy();";

//        $this->page->onLoad($jsCode);
        $this->page->onUnLoad($this->unload);
    }
}

?>
