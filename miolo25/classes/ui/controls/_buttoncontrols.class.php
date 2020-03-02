<?php

class MButton extends MFormControl
{
    public $action;
    public $event;
    public $onclick;
    public $target = '_self';
    public $type;
    public $image;
    public $text;
    public $onclickdisable;

    public function __construct( $name = '', $label = '', $action = NULL, $image = NULL, $onclickdisable = FALSE )
    {
        parent::__construct( $name, '', $label );

        $this->action  = $action ? $action : 'submit';
        $this->event   = '';
        $this->onclick = '';
        $this->image   = $image;
        $this->onclickdisable = $onclickdisable;
        $this->setClass('m-button');
    }


    public function __set( $name, $value )
    {
        switch ( $name )
            {
            case 'padding':
            case 'width':
            case 'height':
            case 'visibility':
                $this->_addStyle( $name, $value );
                break;

            default: 
                parent::__set( $name, $value );
                break;
            }
    }


    public function setImage( $location )
    {
        $this->image = $location;
    }


    public function setOnClick( $onclick )
    {
        $this->onclick = $onclick;
    }

    public function setAction( $action )
    {
        $this->action = $action;
    }

    public function generateButton()
    {
        $action = strtoupper($this->action);
        $onclick = $this->getAttribute('onclick');

        $formId = $this->page->formid;
        if ( $action == 'SUBMIT' )
        {
//            $type = 'submit';
            $type = 'button';

            if ( count($this->eventHandlers) )
            {
                $param   = $this->eventHandlers['click']['param'];
                $onclick = "miolo.doPostBack('{$this->name}:click','{$param}','$formId')";
            }
            elseif ( $this->event != '' )
            {
                $eventTokens = explode( ';', $this->event );
                $onclick     = "miolo.doPostBack('{$eventTokens[0]}','{$eventTokens[1]}','$formId')";
            }
            else
            {
                if (( $this->name != '' ) && ($onclick == ''))
                {
                    $onclick = "miolo.doPostBack('{$this->name}:click','','$formId');";
                }
            }
            if ( $this->onclickdisable )
            {
                $onclick .= ";miolo.doDisableButton('{$this->name}');";
            }
        }
        else if ( $action == 'RESET' )
        {
            $type = 'reset';
        }
        else if ( $action == 'PRINT' )
        {
            $type = 'button';
            $onclick = "miolo.doPrintForm();";
        }
        else if ($action == 'REPORT')
        {
            $type = 'button';

            if ( $this->name != '' )
            {
                $onclick = "miolo.doPostBack('{$this->name}:click',''); miolo.doPrintFile();";
            }
        }
        else if ( $action == 'PDF' )
        {
            $type = 'button';

            if ( $this->name != '' )
            {
//                $onclick = "miolo.doPostBack('{$this->name}:click',''); miolo.doShowPDF();";
                $onclick = "miolo.doShowPDF('{$this->name}:click','','$formId');";
            }
        }
        else if ( $action == 'RETURN' )
        {
            global $history;

            $type = 'button';
            $href = $history->back('action');
            $onclick = "miolo.doHandler('$href','$formId');";
        }
        else if ( $action == 'NONE' )
        {
            $type = 'button';
            $onclick = "";
        }
        else if ( substr($action, 0, 7) == 'HTTP://' )
        {
            if ( $this->onclick != '' )
            {
                $this->action = $this->action . "&onclick=$this->onclick";
            }

            $type = 'button';
            $onclick = "miolo.doHandler('$this->action','$formId');";
        }
        else if ( substr($action, 0, 3) == 'GO:' )
        {
            if ( $this->onclick != '' )
            {
                $this->action = $this->action . "&onclick=$this->onclick";
            }

            $type = 'button';
            $goto = substr($this->action, 3);
            $onclick = "miolo.doHandler('$goto','$formId');";
        }
        else if ( $action{0} == ':' )
        {
            $type = 'button';
            $event = substr($this->action, 1);
            $onclick = $this->manager->getUI()->getAjax($event);
        }
        else if ($onclick == '')
        {
            $type = 'button';
            $onclick = $this->action;
        }

        if ( $this->label == '' )
        {
            $this->label = 'Enviar';
        }

        if ( $this->value == '' )
        {
            $this->value = $this->label;
        }

        $this->type = $type;
        $this->onclick = $onclick;
        $this->addAttribute('onclick', $this->onclick);
        $this->text = $this->label;
        $this->label = '&nbsp;';
    }


    public function generateInner()
    {
        if ( $this->visible )
        {
            $this->generateButton();
            $this->inner = $this->generateLabel() . $this->getRender('button');
        }
    }

}


class MInputButton extends MButton
{
    public function generateInner()
    {
        if ( $this->visible )
        {
            parent::generateButton();
            $this->value = $this->text = '';
            $this->inner = $this->generateLabel() . $this->getRender('button');
        }
    }
}

class MButtonImage extends MButton
{
    public function generateInner()
    {
        if ( $this->visible )
        {
            parent::generateButton();
            $this->value = $this->text = '';
            $this->setClass('m-button-image');
            $this->_addStyle('background-image',"url({$this->image})");
            $this->inner = $this->generateLabel() . $this->getRender('inputButton');
        } 
    }
}

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
        $this->setClass('m-button');
    }

    public function generateInner()
    {
        if ( $this->visible )
        {
            $jsName = "ajax{$this->name}";
            $parameters = $this->parameter ? ",parameters: function(){return miolo.getElementById(\"{$this->parameter}\").value;}" : '';
            $this->value = $this->label;
            $this->action = $jsName . ".call()";

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

class MDropDownButton extends MButton
{
    protected $itens;
    public $name;
    public $label;
    public $iconClass;

    public function __construct( $name = '', $label = '', $iconClass = '')
    {
        parent::__construct( $name, $label );    
        $this->name = $name;
        $this->label = $label;
        $this->iconClass = $iconClass;
    }
    
    public function addItem($label, $iconClass, $onclick)
    {
        $div = new MDiv('', $label, NULL, "dojoType=\"dijit.MenuItem\"  iconClass=\"{$iconClass}\"");
        $div->getBox()->setAttribute('onclick', $onclick);
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
        $jsCode =  "dojo.parser.parse(dojo.byId('{$this->name}_div'));";

        $this->page->onLoad($jsCode);
    }
}

?>
