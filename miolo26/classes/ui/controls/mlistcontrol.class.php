<?php
class MListControl extends MInputControl
{
    public $options;
    public $showValues;
    public $content;
    public $size;
    public $cols; 
    public $type; // used in readonly

    public function __construct($name='',$label='',$options='',$showValues=false,$hint='')
    {
        parent::__construct($name,'',$label);
        $this->options = $options;
        $this->showValues = $showValues;
        $this->hint = $hint;
        $this->formMode = 1;
    }

    public function generateOptions()
    {
        $content = '';

        foreach( array_keys($this->options) as $k )
        {            
           $o = $this->options[$k];
           if ( $o instanceof MOptionGroup )
           {
              foreach( $o->options as $oo )
              {            
                 $oo->setControl($this);
              }
              $content .= $o->generate();
           }
           else
           {
              if ( $o instanceof MOption )
              {
                 $oo = $o;
              }
              elseif ( is_array($o) ) 
              {
                 list ($value,$label) = $o;
                 $oo = new MOption('',$value,$label);
              }
              else
              {
                 $oo = new MOption('',$k,$o);
              } 
              $oo->checked = ($oo->value === 0) || ($oo->value === '0') ? ($this->value == '0') : ($oo->value == $this->value);
              $oo->setControl($this);
              $content .= $oo->generate();
           }
        }
        return $content;
    }
}
?>