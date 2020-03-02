<?php
class MListControl extends MFormControl
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

class MSelection extends MListControl
{
    public $autoPostBack;
    public $event; // trigger a event on selection - autopostback true

    function __construct($name='',$value='',$label='', 
                $options,$showValues=false,$hint='',$size='')
    {
        parent::__construct($name,$label,$options,$showValues,$hint,$size);
        $this->setOptions(is_array($options) ? $options : array(_M('No'), _M('Yes')));
        $this->setValue($value);
		$this->size = $size;
        $this->autoPostBack = false;
        $this->event = '';
    }

    function setOptions($options)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->Assert(is_array($options),_M('$options needs to be an array'));
        if(is_array($options[0]))
        {
            $keys = array_keys($options[0]);
            $s = array(''=>array('',_M('--Select--')));
        }
        else
        {
            $keys = array_keys($options);
            $s = array(''=>_M('--Select--'));
        }
//        $options = str_replace('0','',$keys[0]) ? $s + $options : $options;
        $options = $keys[0] !== '' ? $s + $options : $options;
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
/*
      if ( count($this->eventHandlers) )
      {
           foreach($this->eventHandlers as $e=>$f)
           {
               $this->addAttribute($e,$f['handler']);
           }
      }
*/
        if ( $this->event )
        {
            $formId = $this->page->getFormId();
            if ( $this->event{0} == ':' )
            {
                $event = substr($this->event, 1);
                $onChange =  $this->manager->getUI()->getAjax($event);
            }
            else
            {
                $onChange = "miolo.doPostBack('{$this->event}','$formId');" ;
            }
            $this->addAttribute('onChange', $onChange);
      }
      elseif ( $this->autoPostBack )
      {
         $this->addAttribute('onchange',"miolo.getForm().submit();");
      }

      if ($this->getClass() == '') $this->setClass('m-combo');
	  if ( $this->readonly )
      {
          $this->setClass('m-readonly');
          $this->addAttribute('readonly');
          $this->setId($this->getId() . '_ro');
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

class MMultiSelection extends MListControl
{       
    public $size;

    public function __construct($name='', $values=Array('1','2','3'), 
               $label='&nbsp;', $options=Array('Option1','Option2','Option3'),
               $showValues=false, $hint='', $size='3')
    {
        parent::__construct($name,$label,$options,$showValues,$hint);
        $this->size       = $size;
        $this->value      = $values; // is this still necessary?
    }

    public function getName()
    {
        return (strpos($this->name, '[]') !== false ? $this->name : $this->name.'[]');
    }

   public function generateInner()
   {       
      if ( !is_array($this->value) )
         echo $this->painter->div(new MDiv('',_M("Values must be an single-dimensional array"),'alert'));
      if ( !is_array($this->options) )
         echo $this->painter->div(new MDiv('',_M("Options must be an {single,multi}-dimensional array"),'alert'));
      $this->content = $this->generateOptions();
      $this->addAttribute('multiple','');
      $this->addAttribute('size', $this->size);
      if ($this->getClass() == '') $this->setClass('m-combo');
      $this->inner = $this->generateLabel() . $this->getRender('select');
   }
}

class MComboBox extends MSelection
{
    public $size;

    public function __construct($name='',$value='',$label='',$options=Array('No','Yes'),$showValues=false,$hint='',$size=6)
    {
        parent::__construct($name,$value,$label,$options,$hint);
        $this->size = $size;
        $this->page->addScript('m_combobox.js');
        if (is_array($this->options)) {
           reset($this->options);
           $o = current($this->options);
        } 
    }

   public function generateInner()
   {
      if ( $this->autoPostBack )
      {
         $this->addAttribute('onchange',"miolo.getForm().submit()");
      }

	  if ( $this->readonly )
      {
          $this->setClass('m-readonly');
          $this->addAttribute('readonly');
          $this->setId($this->getId() . '_ro');
          $this->setValue($this->getOption($this->getValue()));
          $this->type = 'text';
          $this->size = '25';
          $this->inner = $this->generateLabel() . $this->getRender('inputtext');
          return;
      }
      if ($this->getClass() == '') $this->setClass('m-combo');
      $textName = $this->name;
      $selName = $this->name . '_sel';
      $text = new MTextField($textName,$this->value,'',$this->size);
      foreach ($this->attrs->getItems() as $key => $value)
      {
          $text->addAttribute($key, str_replace( "\"", '', trim($value) ));
      }
      $text->addAttribute('onBlur',"miolo.comboBox.onTextChange('{$this->label}', '{$textName}','{$selName}')"); 
      $this->setId($selName);
      $this->setName($selName);
      $this->addAttribute('onChange',"miolo.comboBox.onSelectionChange('{$this->label}', '{$selName}', '{$textName}')");

      $this->content = $this->generateOptions();
      $this->addAttribute('size', '1');
      $span = new MSpan('',array($text->generate(),' - ',$this->getRender('select')));
      $this->inner = $this->generateLabel() . $span->generate();
   }
}
?>
