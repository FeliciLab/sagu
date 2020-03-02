<?php

class MTextField extends MInputControl
{
    public $size; // =0 => hidden
    public $type; //text, multiline, password, file
    public $validator;
    public $rows;
    public $cols;
    public $mask;

    public function __construct( $name='',$value='',$label='', $size=10, $hint='', $validator=NULL, $isReadOnly=false )
    {
        parent::__construct( $name, $value, $label, '', $hint );

        $this->setReadOnly( $isReadOnly );
        $this->size     = $size;
        $this->type     = ( ($size > 0) ) ? 'text' : 'hidden';
        $this->setValidator( $validator );
        $this->rows     = 1;
        $this->cols     = $this->size;
        $this->mask = '';
        $this->formMode = MFormControl::FORM_MODE_SHOW_SIDE;
        $this->formName = $this->page->getName();
    }


    public function getValidator()
    {
        if($this->validator && $this->validator->name)
        {
            return $this->validator;
        }
        if ( method_exists($this->form, 'getFieldValidator') )
        {
            return $this->validator = $this->form->getFieldValidator($this->name);
        }
    }


    public function setValidator( $value )
    {
        $this->validator = is_string($value) ? new MMaskValidator( $name, $label, $value ) : $value;
    }

    public function addMask( $mask, $optional = true, $msg = '' )
    {
        $this->mask[0] = $mask; 
        $this->mask[1] = $optional ? 'true':'false'; 
        $this->mask[2] = $msg; 
    }

    public function generateInner()
    {
        if ( ( $this->label ) && ( $this->type == 'hidden' ) )
        {
            $span = new MSpan( $this->name, $this->value, 'm-caption' ) ;
            $html = $this->painter->span( $span );
        }

        if ( $this->mask[0] != '')
        {
//            $this->page->addScript('x/x_core.js');
//            $this->page->addScript('x/x_event.js');
            $this->page->addScript('m_editmask.js');
            $this->page->addJsCode("var editmask_{$this->name} = null;");
            $this->page->onLoad("editmask_{$this->name} = new Miolo.editMask('{$this->name}','{$this->mask[0]}',{$this->mask[1]},'{$this->mask[2]}');");
            $this->page->onSubmit("editmask_{$this->name}.onSubmit()");
        }

        if ( $this->autoPostBack )
        {
            $this->addAttribute( 'onblur', "document.{$this->page->name}.submit()" );
        }

        if ( $this->getClass() == '' )
        {
            $this->setClass( 'm-text-field' );
        }

    	if ( $this->readonly )
        {
            $this->setClass('m-readonly');
            $this->addAttribute('readonly');
            $this->setId($this->getId() . '_ro');
        }

        if ( ( $this->type=='text' )     ||
             ( $this->type=='password' ) ||
             ( $this->type=='file' )
            )
        {
            $size = '';

            if ( $this->type=='text' && $this->size )
            {
                $size = $this->size;
            }

            $text = $this->getRender('inputtext');
            $this->inner = $this->generateLabel() . $text;
        }
        else if ( ($this->type=='multiline') )
        {
            $text = $this->getRender('inputtextarea');
            $this->inner = $this->generateLabel() . $text;
        }
    }
}


class MPasswordField extends MTextField
{
    public function __construct( $name='', $value='', $label='', $size=20, $hint='', $validator=null )
    {
        parent::__construct( $name, $value, $label, $size, $hint, $validator );

        $this->type = 'password';
    }
}


class MHiddenField extends MTextField
{
    public function generate()
    {
        return $this->getRender('inputhidden');
    }
}


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
            $this->setClass('m-multiline-field');
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


class MEditor extends MDiv
{
    private $hidden;
    private $button;
   
    public function __construct( $name='', $value='', $width = '200px', $height = '250px')
    {
        parent::__construct( $name . '_editor', $value );

        $this->hidden = new MHiddenField($name,'');
        $this->button = new MButton($name . '_button', _M('Open Editor'));
        $this->button->addAttribute('onclick',"new dijit.Editor({height: '{$height}', extraPlugins: ['dijit._editor.plugins.AlwaysShowToolbar']}, dojo.byId('{$name}_editor')); dojo.query('#{$name}_button').orphan();");

        $this->width = $width;
        $this->height = $height;
        
        $plugins="['undo','redo','|','cut','copy','paste','|','bold','italic','underline','|','insertOrderedList','insertUnorderedList', '|','justifyLeft','justifyRight','justifyCenter','justifyFull','|','fontSize']";
        $this->page->addDojoRequire("dijit.Editor");
        $this->page->addDojoRequire("dijit._editor.plugins.FontChoice");
           
        $this->getBox()->addAttribute("dojoType", "dijit.Editor");
        $this->getBox()->addAttribute("plugins", $plugins);
$this->page->onLoad("dojo.require('dijit.Editor');");
$this->page->onLoad("dojo.require('dijit._editor.plugins.FontChoice');");
//$this->page->onLoad("alert(dojo.byId('{$name}_editor').id);");
        $this->page->onLoad("try {var {$name}_editor = new dijit.Editor({height: '{$height}', extraPlugins: []}, dojo.byId('{$name}_editor'));} catch (err) {console.log(err)}");
//$this->page->onLoad("alert('hhh');");
        $this->page->onLoad("dojo.parser.parse(dojo.byId(\"{$name}_editor\"));");
        $this->page->onSubmit("(dojo.byId('{$name}').value = dijit.byId('{$name}_editor').getValue(false))");
    }

    public function generate()
    {
        return parent::generate() . $this->hidden->generate();
    }
}



class MFileField extends MTextField
{
    public function __construct( $name='', $value='', $label='', $size=40, $hint='' )
    {
        parent::__construct( $name, $value, $label, $size, $hint );

        $this->type = 'file';
    }
    public function copyFile( $path )
    {
        if( $f=$_FILES[$this->name]['tmp_name'])
        {
            copy( $f, $path );
            return true;
        }
        else
        {
            return false;
        }
    }

    public function getFileName( )
    {
        return $_FILES[$this->name]['name'];
    }

    public function getFileType( )
    {
        return $_FILES[$this->name]['type'];
    }
}


class MCalendarField extends MTextField
{
    public function __construct( $name='', $value='', $label='', $size=20, $hint='', $type='text', $class='m-text-field' )
    {
        parent::__construct( $name, $value, $label, $size, $hint);

        $this->type = $type;
        $this->setClass($class);

        // dijit.form.DateTextBox has a standard date format (ansi) : yyyy-mm-dd
        // so, build a new Dijit Widget to force date format to 'dd/mm/yyyy' on submit

        $js = <<< HERE
dojo.declare("MDateTextBox", [dijit.form.DateTextBox], {
   serialize: function(d, options) {
     return dojo.date.locale.format(d, {selector:'date', datePattern:'dd/MM/yyyy'}).toLowerCase();
   }
});

HERE;
        $this->page->addJsCode($js);

        $this->addAttribute('dojoType', 'MDateTextBox');
        $this->addAttribute('promptMessage',"dd/mm/yyyy");
        // this attribute is valid only to "show" the date (it doesn't define the internal format)
        $this->addAttribute('constraints',"{datePattern:'dd/MM/yyyy'}");

        $this->page->addDojoRequire("dijit.form.DateTextBox");
        $this->page->onload("dojo.parser.parse(\"dojo.byId('{$name}')\");");
//        $this->page->onload("dojo.byId('{$name}').value = '';");

        $this->setValidator( new MDATEDMYValidator( $name, $label ) );
    }


    public function generateInner()
    {
        // dijit.form.DateTextBox has a standard date format (ansi) : yyyy-mm-dd
        // convert Miolo internal date format (dd/mm/yyyy) to Dojo date format (yyyy-mm-dd)
        $k = new MKrono();
        if ($this->value != '')
        {
            $this->setValue($k->KDate('%Y-%m-%d', $k->dateToTimestamp($this->value)));
        }

        parent::generateInner();

        if ( ! $this->readonly )
        {
            $text = $this->getRender('inputtext');
            $this->inner = $this->generateLabel() . $text;
        }

    }
}

class MCurrencyField extends MTextField
{
    private $ISOCode = 'REAL';

    public function __construct( $name='', $value='', $label='', $size=10, $hint='' )
    {
        // este campo vai usar validacao/formatacao javascript 
        parent::__construct( $name, $value, $label, $size, $hint );

        $page = $this->page;

        $page->addScript( 'm_utils.js' );
        $page->addScript( 'm_currency.js' );
    }


    public function getValue()
    {
        // internal ($this->value): float - 12345.67
        // external (return): formated string - R$ 12.345,67
        if (strpos($this->value, "%") === false)
        {
            $format = new MCurrencyFormatter();
            $value = $format->formatWithSymbol( $this->value, $this->ISOCode );
        }
        else
        {
            $value = $this->value;
        }
        return $value;
    }


    public function setValue( $value )
    {
        // internal ($this->value): float - 12345.67
        // external ($value): formated string - 12.345,67

        if (strpos($value, "%") === false)
        {
            $format = new MCurrencyFormatter();

            $value = $format->toDecimal($value, $this->ISOCode);
            $this->value = (float)$value;
        }
        else
        {
            $this->value = $value;
        }
    }


    public function generateInner()
    {
        $this->addAttribute('onblur',"miolo.currency('{$this->name}')");
        parent::generateInner();
    }
}

?>
