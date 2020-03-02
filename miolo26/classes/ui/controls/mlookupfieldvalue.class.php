<?
class MLookupFieldValue extends MLookupField
{
	function __construct($name='',$value='',$label='', 
                 $size=10,$hint='',$validator=null,$related='',
	             $module='',$item='', $event='', $filter='', $autocomplete=false)
    {   
        parent::__construct($name,$value,$label,$hint,$validator);
        $this->size = $size;
        $this->filter = $this->name;
        $this->validator = is_string($validator) ? MValidator::MASKValidator($validator) : $validator;
    }
    
    public function generateInner()
    {
        parent::generateInner();
        $htmlInner = $this->getInner();
        $field = new MTextField($this->name,$this->value,$this->label,$this->size,$this->hint, $this->validator);
        $field->setClass('mTextField'); 
        $field->showLabel = $this->showLabel;
        $field->formMode = $this->formMode;
        $field->setClass('ReadOnly');
        $field->addAttribute('readonly');
//        $html = $field->generate();
//        $this->inner = ( $this->readonly  ? '' : $htmlInner) . $html;   

        $div = new MDiv('', $field );

        $lookupField = $this->getInner();
        $c = new MHContainer('', array($lookupField, $field));
        $c->setClass('mLookupField');
        $c->setShowChildLabel( false, true );
        $this->inner = $c;
     
    }
}
?>