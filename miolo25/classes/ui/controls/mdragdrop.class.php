<?php
class MDragDropControl 
{
    protected $control;
    protected $options;
    public    $containerId;

    public function __construct($control)
    {
        $this->control = $control;
        $this->options = new MStringList();
    }

    public function addOption($option, $value)
    {
        $this->options->addValue($option, $value);
    }
}

class MDraggable extends MDragDropControl
{
    public function generate()
    {
        $div = new MDiv("ddm_{$this->control->id}",$this->control);
        $this->control->setClass("dojoDndItem");
        $js = "ddm_{$this->control->id} = new dojo.dnd.Source('ddm_{$this->control->id}'";
        $js .= $this->options->hasItems() ? ",{" . $this->options->getText(':', ',') . "}" : '';
        $js .= ");"; 
        $js .= "dojo.parser.parse(\"dojo.byId('ddm_{$this->control->id}')\");";
        $this->page->onLoad($js);
        return $div->generate();
    }

    public function addRevertNotDropped()
    {
//        $this->addOption("revert","function(element) {var dp =!Droppables.dropped; Droppables.dropped = false; return dp; }");       
    }

} 

class MDroppable extends MDragDropControl
{
    private $onDrop;

    public function generate()
    {
//        $this->addOption("onDrop", "function(element, drop) {" . $this->onDrop . "ddm_{$this->containerId}.onDrop(element, drop); }");
//        $js = "Droppables.add('{$this->control->id}'";
//        $js .= $this->options->hasItems() ? ",{" . $this->options->getText(':', ',') . "}" : '';
//        $js .= ");"; 
        $this->addOption("isSource", "false");
        $js = "ddm_{$this->control->id} = new dojo.dnd.Source('{$this->control->id}'";
        $js .= $this->options->hasItems() ? ",{" . $this->options->getText(':', ',') . "}" : '';
        $js .= ");"; 
        $js .= "dojo.parser.parse(\"dojo.byId('{$this->control->id}')\");";
        return $js;
    }
 
    public function onDrop($jsCode)
    {
        $this->onDrop = $jsCode;
    }

} 

class MDragDrop extends MFormControl
{
    private $draggable = array();
    private $dropZone = array();

    public function addDraggable($control, $options = array())
    {
//        $control->containerId = $this->id;
//        $this->draggable[] = $control;


        $js = "dnd_{$control->id} = new dojo.dnd.Source('{$control->id}'";
        $js .= ",{"; 
        if (count($options))
        {
            foreach($options as $a=>$v)
            {
                $js .= $a . ':' . $v . ',';
            }
        }
        $js .= "creator: function(item, hint){ if(hint == 'avatar'){ return {node: dojo.dnd._createSpan(item.data)};} else { mynode = dojo.byId(item.data); } return {node: mynode, data: item}; } ";
        $js .= "}"; 
        $js .= ");"; 
        foreach($control->getControls() as $c)
        {
            if ($c instanceof MControl)
            {
                $js .= "dnd_{$control->id}.insertNodes(false,[{data: '{$c->id}'} ]);"; 
            }
        }
        $this->page->onLoad($js);
    }

    public function addDropZone($control, $options = array())
    {
//        $control->containerId = $this->id;
//        $this->dropZone[] = $control;
        $js = "dnd_{$control->id} = new dojo.dnd.Source('{$control->id}'";
        $js .= ",{"; 
        if (count($options))
        {
            foreach($options as $a=>$v)
            {
                $js .= $a . ':' . $v . ',';
            }
        }
        $js .= "isSource: false ";
        $js .= "}"; 
        $js .= ");"; 
/*
        $control->addAttribute("dojoType","dojo.dnd.Source");
        $control->addAttribute("isSource","false");
        foreach($options as $a=>$v)
        {
            $control->addAttribute($a, $v);
        }
        $js = "dojo.parser.parse(\"dojo.byId('{$control->id}')\");";
//        $control->addEvent('onDrop',"ddm_{$this->id}.onDrop(s,n,c);");
//        $control->addEvent('onDrop',"console.log('----');");
*/
//        $js .= "console.log(dojo.byId('{$control->id}'));";
        $js .= "dojo.connect(dnd_{$control->id}, 'onDrop', function (s,n,c) { ddm_{$this->id}.onDrop('{$control->id}',s,n,c);});";
        $this->page->onLoad($js);
    }

    public function getValue()
    {
        parse_str($this->value, $v);
        return $v;
    }

    public function generate()
    {
//        $this->page->addScript('scriptaculous/scriptaculous.js?load=effects,dragdrop');
        $this->page->addScript('m_dragdrop.js');
        $this->page->addJsCode("var ddm_{$this->id} = new Miolo.DnD('{$this->id}');");
        $this->page->onSubmit("ddm_{$this->id}.onSubmit()");
////        $this->manager->getTheme()->appendContent(new MHiddenField($this->id,''));


        $this->page->addDojoRequire("dojo.dnd.Source");

/*
        foreach($this->draggable as $control)
        { 
           $control->generate();
        }
        foreach($this->dropZone as $control)
        { 
           $this->page->onLoad($control->generate());
        }
*/
        return $this->getRender('inputhidden');
    }
}
?>