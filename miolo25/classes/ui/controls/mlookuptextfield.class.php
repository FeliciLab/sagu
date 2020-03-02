<?
class MLookupTextField extends MLookupField
{
    public $autocomplete;

    /**
     * @var boolean Sets if the field must be rendered as a MIntegerField.
     */
    private $isInteger;
    
    public function __construct($name='',$value='',$label='', 
                 $size=10,$hint='',$validator=null,$related='',
	             $module='',$item='', $event='filler', $filter='', $autocomplete=false)
    {   
        parent::__construct($name,$value,$label,$hint,$related, $module, $item, $event, $filter); //$validator);
        $this->size = $size;
        $this->filter = $filter ? $filter : $this->name;
        $this->autocomplete = $autocomplete ? true : false;
        $this->validator = is_string($validator) ? MValidator::MASKValidator($validator) : $validator;
        $this->showLabel = false;
    }

    public function getAutocompleteData()
    {
        $autocomplete = new MAutoComplete($this->module,$this->item,$this->value,$this->related);
        $info = $autocomplete->getResult();
        return $info;
    }

    /**
     * @param boolean $isInteger Sets if the field must be rendered as a MIntegerField.
     */
    public function setInteger($isInteger)
    {
        $this->isInteger = $isInteger;
    }

    public function generateInner()
    {
        if ( $this->isInteger )
        {
            $field = new MIntegerField($this->name, $this->value, $this->label, $this->size, $this->hint, $this->validator);
            $field->attrs->setAttributes($this->attrs->getAttributes());
        }
        else
        {
            $field = new MTextField($this->name, $this->value, $this->label, $this->size, $this->hint, $this->validator);
            $field->attrs = $this->attrs;
        }

        if ( $this->autocomplete )
        {
            $field->addAttribute('onchange', "{$this->lookup_name}.start(true);");
            $this->page->onLoad("if (miolo.getElementById('{$this->name}').value) { {$this->lookup_name}.start(true); }");
        }
        $field->validator = $this->validator;
        $field->form      = $this->form;

        $field->setClass('mTextField');
        $field->showLabel = $this->showLabel;
        $field->formMode = $this->formMode;
	    $field->setReadOnly( $this->readonly );

//      $html = $field->generate();
        $div = new MDiv('', $field );

        parent::generateInner();
        $lookupField = $this->getInner();
        $c = new MHContainer('', array($field, ( $this->readonly  ? '' : $lookupField)));
        $c->setClass('mLookupField');
        $c->setShowChildLabel( false, true );
        $this->inner = $c;
   }

}

?>
