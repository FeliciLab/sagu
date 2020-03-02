<?php

class MMultiLineField extends MTextField
{   
    public function __construct( $name='', $value='', $label='', $size=20, $rows=1, $cols=20, $hint='', $validator=null )
    {
        parent::__construct( $name, $value, $label, $size, $hint, $validator );

        $this->type = 'multiline';
        $this->rows = $rows;
        $this->cols = $cols;
        
        if ( $this->getClass() == '' )
        {
            $this->setClass('mMultilineField');
        }
    }
    
    /**
     * Set true or false to indicate that it should generate a Html editor
     *
     * @param (boolean) $isHtmlEditor Enable or disable Html editor
     */
    public function setHtmlEditor($plugins="['undo','redo','|','cut','copy','paste','|','bold','italic','underline','|','insertOrderedList','insertUnorderedList','|','justifyLeft','justifyRight','justifyCenter','justifyFull','|','fontSize']")
    {
            $this->page->addDojoRequire("dijit.Editor");
            $this->page->addDojoRequire("dijit._editor.plugins.FontChoice");
           
            $this->addAttribute("dojoType", "dijit.Editor");
            $this->addAttribute("plugins", $plugins);
            $this->page->onload("dojo.parser.parse(\"dojo.byId({$this->name})\");");
    }
}

?>