<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MOption extends MControl
{
/**
 * Attribute Description.
 */
    public $label;

/**
 * Attribute Description.
 */
    public $name;

/**
 * Attribute Description.
 */
    public $value;

/**
 * Attribute Description.
 */
    public $checked;

/**
 * Attribute Description.
 */
    public $type='circle';

/**
 * Attribute Description.
 */
    public $control;  // owner control of this Option

    
/**
 * Brief Description.
 * Complete Description.
 *
 * @param $name' (tipo) desc
 * @param $value= (tipo) desc
 * @param $label='' (tipo) desc
 * @param $checked= (tipo) desc
 * @param $id=false (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function __construct($name='',$value=null,$label='',$checked=false,$id=false)
    {
        $this->label   = $label;
        $this->name    = $name;
        $this->value   = $value;
        $this->checked = $checked;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $control (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function setControl($control)
    {
        $this->control = $control;
    }
 
/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function generate()
    {
        if (is_array($this->control->value))
        {
            $found = array_search($this->value,$this->control->value);
            $checked = (!is_null($found)) && ($found !== false);
        }
        else
        {
            $checked = ($this->value == $this->control->value);
        }
        $this->checked = $this->checked || $checked;
        return HtmlPainter::option($this->value, $this->label, $this->checked, $this->control->showValues);
    }

}

/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MOptionGroup extends MControl
{
/**
 * Attribute Description.
 */
    public $label;

/**
 * Attribute Description.
 */
    public $name;

/**
 * Attribute Description.
 */
    public $options; // array of option objects

    
/**
 * Brief Description.
 * Complete Description.
 *
 * @param $name (tipo) desc
 * @param $label' (tipo) desc
 * @param $options= (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function __construct($name,$label='',$options=NULL)
    {
        $this->label   = $label;
        $this->name    = $name;
        $this->options = $options;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function generate()
    {
        foreach( $this->options as $o )
        {            
            $content .= $o->generate();
        }
        return HtmlPainter::optionGroup('formCombo',"$this->label", $content);
    }
}
?>
