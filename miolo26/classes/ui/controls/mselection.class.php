<?php
class MSelection extends MListControl
{
    public $event; // trigger a event on selection - autopostback true

    /**
     * MSelection constructor.
     *
     * @param string $name Field name and id.
     * @param string $value Initial value.
     * @param string $label Field label.
     * @param array $options Options.
     * @param boolean $showValues Sets whether the options must show their values.
     * @param string $hint Helpful hint.
     * @param integer $size Field size.
     * @param boolean $allowNullValue Sets whether it must show a blank option at the beginning.
     */
    public function __construct($name='', $value='', $label='', $options, $showValues=FALSE, $hint='', $size='', $allowNullValue=TRUE)
    {
        parent::__construct($name,$label,$options,$showValues,$hint,$size);

        $options = is_array($options) ? $options : array( _M('No'), _M('Yes') );
        $this->setOptions($options, $allowNullValue);

        $this->setValue($value);
		$this->size = $size;
        $this->autoPostBack = false;
        $this->event = '';
    }

    /**
     * Set the options array.
     *
     * @param array $options Options.
     * @param boolean $allowNullValue Sets whether it must show a blank option at the beginning.
     */
    public function setOptions($options, $allowNullValue=TRUE)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->Assert(is_array($options), _M('$options needs to be an array'));

        if ( $allowNullValue )
        {
            if ( is_array($options[0]) )
            {
                $keys = array_keys($options[0]);
                $s = array( '' => array( '', _M('--Select--') ) );
            }
            else
            {
                $keys = array_keys($options);
                $s = array( '' => _M('--Select--') );
            }

            $options = $keys[0] !== '' ? $s + $options : $options;
        }

        $this->options = $options;
    }
    
	function getOption($value)
    {
        foreach( array_keys($this->options) as $k )
        {            
            $o = $this->options[$k];
            if ( $o instanceof MOptionGroup )
            {
                foreach( $o->options as $oo )
                {            
                    if (trim($value) == trim($oo->getValue()))
                       $r = $oo->label;
                }
            }
            elseif ( $o instanceof MOption )
            {
                if (trim($value) == trim($o->getValue()))
                   $r = $o->label;
            }
            elseif ( is_array($o) ) 
            {
                list($id, $name) = $o;
                if (trim($value) == trim($id))
                   if ($this->showValues) $r = $id . ' - ' . $name;
                   else $r = $name;
            }
            elseif (trim($value) == trim($k)) 
            {
                if ($this->showValues) $r = $k . ' - ' . $o;
                else $r = $o;
            }
        }
        return $r;
    }
    
    public function setOption($option,$value)
    {
        $this->options[$option] = $value;
    }

    public function setCols($value)
    {
        $this->cols = $value;
    }

	function setAutoSubmit($state)
    {
        $this->autoPostBack = $state;
    }

   public function generateInner()
   {
        $selected = false;
        if ( $this->autoPostBack )
        {
            $this->addEvent('change',"miolo.submit();");
        }

        if ($this->getClass() == '')
        {
            $this->setClass('mSelection');
        }

        if ( $this->readonly )
        {
            $this->setClass('mReadOnly');
            $this->addAttribute('readonly');
            //$this->setId($this->getId() . '_ro');
            $this->setValue($this->getOption($this->getValue()));
            $this->size = $this->cols ? $this->cols : strlen(trim($this->getValue())) + 10;
            $this->type = 'text';
            $this->inner = $this->generateLabel() . $this->getRender('inputtext');
        }
        else
        {
            $this->content = $this->generateOptions();
            if ($this->size != '')
            { 
                $this->addAttribute('size', $this->size);
            }
            $this->inner = $this->generateLabel() . $this->getRender('select');
        }
    }
}

?>
