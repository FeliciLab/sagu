<?php
class MComboBox extends MSelection
{
    public $size;

    public function __construct($name='',$value='',$label='',$options=Array('No','Yes'),$showValues=false,$hint='',$size=6)
    {
        parent::__construct($name,$value,$label,$options,$hint);
        $this->size = $size;
        $this->page->addScript('m_combobox.js');
        if (is_array($this->options)) 
        {
           reset($this->options);
           $o = current($this->options);
        } 
    }

   public function generateInner()
   {
        if ( $this->autoPostBack )
        {
            $this->addEvent('change',"miolo.getForm().submit();");
        }

        if ( $this->readonly )
        {
            $this->setClass('mReadOnly');
            $this->addAttribute('readonly');
            //$this->setId($this->getId() . '_ro');
            $this->setValue($this->getOption($this->getValue()));
            $this->type = 'text';
            $this->size = '25';
            $this->inner = $this->generateLabel() . $this->getRender('inputtext');
            return;
        }

        if ($this->getClass() == '')
        {
            $this->setClass('mSelection');
        }
        $textName = $this->name;
        $selName = $this->name . '_sel';
        $text = new MTextField($textName,$this->value,'',$this->size);
        foreach ($this->attrs->attrs->getItems() as $key => $value)
        {
            $text->addAttribute($key, str_replace( "\"", '', trim($value) ));
        }
        $text->addEvent('blur',"miolo.comboBox.onTextChange('{$this->label}', '{$textName}','{$selName}');"); 
        $this->setId($selName);
        $this->setName($selName);
        $this->addEvent('change',"miolo.comboBox.onSelectionChange('{$this->label}', '{$selName}', '{$textName}');");

        $this->content = $this->generateOptions();
        $this->addAttribute('size', '1');
        $span = new MSpan('',array($text->generate(),' - ',$this->getRender('select')));
        $this->inner = $this->generateLabel() . $span->generate();
    }
}
?>
