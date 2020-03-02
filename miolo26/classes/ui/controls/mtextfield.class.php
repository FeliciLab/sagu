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

        if ( $this->manager->checkMobile() )
        {
            $dojoType = 'dojox.mobile.TextBox';
            $this->page->addDojoRequire($dojoType);
            $this->addAttribute('dojoType', $dojoType);
        }
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
            $span = new MSpan( $this->name, $this->value, 'mCaption' ) ;
            $html = $this->painter->span( $span );
        }

        if ( $this->mask[0] != '')
        {
            $this->page->addScript('m_editmask.js');
            $this->page->addJsCode("var editmask_{$this->name} = null;");
            $this->page->onLoad("editmask_{$this->name} = new Miolo.editMask('{$this->name}','{$this->mask[0]}',{$this->mask[1]},'{$this->mask[2]}');");
            $this->page->onSubmit("editmask_{$this->name}.onSubmit()");
        }

        if ( $this->autoPostBack )
        {
            $this->addEvent( 'blur', "miolo.submit();" );
        }

        if ( $this->getClass() == '' )
        {
            $this->setClass( 'mTextField' );
        }

    	if ( $this->readonly )
        {
            $this->setClass('mReadOnly');
            $this->addAttribute('readonly');
            //$this->setId($this->getId() . '_ro');
        }

        if ( ( $this->type=='text' )     ||
             ( $this->type=='password' ) ||
             ( $this->type=='file' ) ||
             ( $this->type=='number' )
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


?>
