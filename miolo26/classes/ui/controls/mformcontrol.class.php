<?php
/*
    MFormControls are controls sourrounded by a box (a MDiv control)
    - It can be a Input or Output control
*/
 
class MFormControl extends MControl
{
    /** 
     * if the $label must be showed with the control
     */
    public $label;

    /** 
     * if the $label must be showed with the control
     */
    public $showLabel; 

    /**
     *  Center alignment
     */
    const ALIGN_CENTER = 'center';

    /**
     * Left alignment
     */
    const ALIGN_LEFT = 'left';

    /**
     * Right alignment
     */
    const ALIGN_RIGHT = 'right'; 

    /**
     * Horizontal layout
     */
    const LAYOUT_HORIZONTAL = 'horizontal';
    
    /**
     * Vertical layout
     */
    const LAYOUT_VERTICAL = 'vertical';
    
    /**
     * The form where this control is inserted (if there is one...).
     */
    public $form;
    public $formName;

    /**
     * The box that contains the control.
     * It is used, primarily, in CSS Positioning.
     */
    public $controlBox;
    public $box;

    /**
     * The value of attribute
     */
    public $value;


    public function __construct( $name, $value = '', $label = '', $color = '', $hint = '' )
    {
        parent::__construct( $name );
        $this->setId($name == '' ? $this->getUniqueId() : $name);
        $this->label = $label;
        $this->hint  = $hint;
        $this->color = $color;
        $this->showLabel = true;
        $this->setValue($value);
    }


    public function setValue( $value )
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setSubmittedValue()
    {
        $default = $this->getValue();
        $value = $this->page->request($this->name);
        $this->setValue(isset($value) ? $value : $default);
    }

    public function setLabel( $label )
    {
        $this->label = $label;
    }

    protected function generateLabelMode($label)
    {
        if ( $this->formMode == MFormControl::FORM_MODE_SHOW_ABOVE )
        {
            $label .= $this->painter->BR;
        }
        elseif ( $this->formMode == MFormControl::FORM_MODE_SHOW_NBSP )
        {
            $label .= "&nbsp;&nbsp;";
        }
        return $label;
    }

    public function generateLabel()
    {
        $label = '';
        $this->showLabel = ( $this->formMode >= MFormControl::FORM_MODE_SHOW_ABOVE );

        if ( ( $this->showLabel ) && ( $this->label != '' ) )
        {
            $span  = new MSpan( '', $this->label, 'mCaption' );
            $label = $this->generateLabelMode($this->painter->span( $span ));

        }
        return $label;
    }

    public function setFormMode( $mode )
    {
        $this->formMode = $mode;
    }
    
    public function setReadOnly( $status )
    {
        parent::setReadOnly( $status );
        if ( $status )
        {
            $this->addAttribute('tabindex', '-1');
        }
    }
}
?>
