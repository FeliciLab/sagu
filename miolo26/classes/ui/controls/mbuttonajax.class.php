<?php

class MButtonAjax extends MButton
{
    public $updateElement;
    public $parameter;
    public $method;

    public function __construct( $name = '', $label = '', $updateElement, $parameter = NULL, $method = NULL )
    {
        parent::__construct( $name, $label );
        $this->updateElement = $updateElement;
        $this->method = $method ? $method : 'on'.$this->name.'_click';
        $this->parameter = $parameter;        
        $this->setClass('mButton');
    }

    public function generateInner()
    {
        if ( $this->visible )
        {
            $jsName = "ajax{$this->name}";
            $parameters = $this->parameter ? ",parameters: function(){return miolo.getElementById(\"{$this->parameter}\").value;}" : '';
            $this->value = $this->label;
            $this->action = $jsName . ".call();";

            $jsCode =
<<< HERE
            {$jsName} = new Miolo.Ajax({
         	   updateElement: '{$this->updateElement}',
        	   response_type: 'TEXT',
        	   remote_method: "{$this->method}"
               $parameters
            });
HERE;
            $this->page->addJsCode($jsCode); 
            parent::generateInner();
        }
    }


}

?>