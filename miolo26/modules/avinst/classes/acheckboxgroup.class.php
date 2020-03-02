<?php 

class ACheckBoxGroup extends MBaseGroup
{
    // options:
    //    - a simple array of values
    //    - a array of key/value pairs
    //    - an array of Option objects
    //    - an array of CheckBox objects
    public function __construct( $name = '', $label = '', $options = '', $hint = '', $disposition = 'horizontal', $border = 'none' )
    {
        $controls = array();

        if (is_array($options))
        {
            foreach ($options as $option)
            {
                if ( $option instanceof AOption )
                {
                    $oName    = $name . '_' . $option->name;
                    $oLabel   = $option->label;
                    $oValue   = $option->value;
                    $oChecked = $option->checked || ( $oValue == MIOLO::_REQUEST($oName) );
                    $descriptiveField = ACheckBoxGroup::getDescriptiveFieldName($name, $option->name);
                    
                    $optionDCheck = new MCheckBox( $oName, $oValue, $oLabel, $oChecked, $oLabel );                    
                    $optionDCheck->attrs = $option->attrs;
                    $optionDCheck->addAttribute('onClick', 'checkDescriptive(this, \''.$descriptiveField.'\');');
                    $optionD[] = clone($optionDCheck);
                    if ($option->descriptive == true)
                    {
                        $valueDescriptive = strlen(MIOLO::_REQUEST($descriptiveField))>0 ? MIOLO::_REQUEST($descriptiveField) : $option->descriptiveValue;
                        $optionDescriptive = new MTextField($descriptiveField, $valueDescriptive, null, 25);
                        if ($oChecked == false)
                        {
                            $optionDescriptive->addAttribute('style', 'display: none');
                        }
                        $optionD[] = clone($optionDescriptive);
                    }
                    $optionData = new MSpan($option->name.'_span', $optionD);
                    unset($optionD);
                }
                $controls[] = clone($optionData);
            }
        }
        parent::__construct($name, $label, $controls, $disposition, $border);
        $this->setShowChildLabel( false, true );
    }
    
    public static function getDescriptiveFieldName($name, $option)
    {
        return $descriptiveField = trim(str_replace('[]', '', $name.$option.'_descriptive'));
    }
}
?>