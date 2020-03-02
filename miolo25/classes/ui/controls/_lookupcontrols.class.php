<?

//vgartner
class MLookupFieldAjax extends MTextField
{
    public $action;
    public $related;
    public $module;
    public $item;
    public $info;
    public $autocomplete=false;
    public $event;
    public $filter;
    public $baseModule;
    public $lookup_name;
    public $showButton=true;
    public $lookupType;
    private $windowType   = 'popup';
    private $windowWidth  = '';
    private $windowHeight = '';
    private $windowTop    = '';
    private $windowLeft   = '';


    function __construct($name='',$value='',$label='',$hint='', 
                 $related='',$module='',$item='', $event='filler', $filter='')
    {   
        parent::__construct($name,$value,$label,0,$hint);
        $page = $this->page;
        $page->AddScript('m_lookup.js');

        if(is_array($related))
        {
            ksort($related);
        }
        else
        {
            $related = array(str_replace(' ','',$related));
        }

        $baseModule = MUtil::NVL($this->manager->GetConf("mad.module"),"admin");
        $event = MUtil::NVL($event,'filler');
        $this->setContext($baseModule,$module,$item,$event,$related,$filter);
        $this->lookupType = 'window';  
        $page = $this->page;
        $form = ($this->form == NULL) ? $page->name : $this->form->name;
        $this->lookup_name = $lookup_name = "lookup_{$form}_{$this->name}";
    }
    
    function setContext($baseModule='admin',$module='admin',$item='',$event='',$related='',$filter='')
    {
        $this->baseModule = $baseModule;
        $this->module  = $module;
        $this->item    = $item;
        $this->event = $event;
        $this->related = $related;
        $this->filter  = MUtil::NVL($filter,$this->filter);
    }

    public function getModuleItem()
    {
        return $this->module . '.' . $this->item;
    }
    
    public function setModuleItem($module, $item)
    {
        $this->module = $module;
        $this->item = $item;
    }

    public function setWindowSize( $width, $height )
    {
        $this->windowWidth  = $width;
        $this->windowHeight = $height;
    }

    public function setWindowType( $windowType='iframe', $width='', $height='', $top='', $left='')
    {
        $this->windowType   = $windowType;
        $this->windowTop    = $top;
        $this->windowLeft   = $left;
        $this->setWindowSize( $width, $height);
    }
    
    /*
        Method to set if the look button should be displayed
    */
    public function setShowButton( $show=true )
    {
        $this->showButton = $show;
    }

   public function generateInner()
   {
        $this->label = $this->label ? '&nbsp;' : '';
        
        $base = $this->baseModule;

        $filter = is_array($this->filter) ? $this->filter : array($this->filter);
        $lookup_name = $this->lookup_name;
        
        $attr = $this->getAttributes();

        if ($this->showButton )
        {
//            $content[] = new MSpan('','&nbsp;');
            $button = new MButtonFind("javascript:{$this->lookup_name}.open();");
            $content[] = $button->generate();
        }

        $html =  $this->painter->generateToString($content);

        $aFilter = implode(',',$filter);
        $akFilter = implode(',',array_keys($filter));
        $jsCode =
<<< HERE
        {$this->lookup_name}.setContext({
             name    : '{$lookup_name}',
             module  : '{$this->module}',
             item    : '{$this->item}',
             related : '{$this->related}',
             filter  : '{$aFilter}',
             idxFilter : '{$akFilter}', 
             form    : miolo.getForm(),
             field   : '{$this->name}',
             event   : '{$this->event}',
             wType   : '{$this->windowType}',
             wWidth  : '{$this->windowWidth}',
             wHeight : '{$this->windowHeight}',
             wTop    : '{$this->windowTop}',
             wLeft   : '{$this->windowLeft}',
             autoPost: '{$this->autoPostBack}'
        }, '{$base}');
HERE;
        $this->page->onLoad($jsCode);
        $this->page->addJsCode("{$this->lookup_name} = new Miolo.lookup();"); 
        $this->inner = $this->generateLabel() . $html;        
   }
}

class MLookupTextFieldAjax extends MLookupFieldAjax
{
    public $autocomplete;
    
    public function __construct($name='',$value='',$label='', 
                 $size=10,$hint='',$validator=null,$related='',
                 $module='',$item='', $event='filler', $filter='', $autocomplete=false)
    { 
        $filter = MUtil::NVL($filter,$name);  
        parent::__construct($name,$value,$label,$hint,$related, $module, $item, $event, $filter); //$validator);
        $this->size = $size;
        $this->autocomplete = $autocomplete ? true : false;
        $this->validator = is_string($validator) ? 
                               Validator::MASKValidator($validator) : $validator;
    }

    public function getAutocompleteData()
    {
        $autocomplete = new MAutoComplete($this->module,$this->item,$this->value,$this->related);
        $info = $autocomplete->getResult();
        return $info;
    }
    
   public function generateInner()
   {
      $field = new MTextField($this->name,$this->value,$this->label,$this->size,$this->hint, $this->validator);
      $field->attrs = $this->attrs;
      if ( $this->autocomplete )
      {
        $field->setAttribute('onChange',"MIOLO_AutoComplete_Ajax({$this->lookup_name},'$this->baseModule')");
      }
      $field->validator = $this->validator;
      $field->form      = $this->form;

      if($this->value && $this->autocomplete)
      {
        $MIOLO = $this->manager;

        $info = $this->getAutoCompleteData();

        $info_ = '';

        if ( $info && is_array($info) )
        {
            foreach($info as $i )
            {
                $info_ .= "'$i',";
            }
        }
        $info_ = substr($info_, 0, -1);

        if(MUtil::getBooleanValue($MIOLO->getConf('options.debug')) && $MIOLO->getConf('logs.handler') == 'screen')
        {
            $msg = _M('[autocomplete]: Field @1 not found!', $this->baseModule) . '<br/>';
        }

        if ( ! $this->readonly )
        {
            $this->page->addJSCode("MIOLO_AutoCompleteDeliver_Ajax(document, {$this->lookup_name}.form, '$msg', '$this->related', new Array($info_) );");
        }
      }

      $field->setClass('m-text-field'); 
      $field->showLabel = $this->showLabel;
      $field->formMode = $this->formMode;
      $field->addBoxStyle('float','left');
      if ( $this->readonly )
      {
          $field->setClass('m-readonly');
          $field->addAttribute('readonly');
      }
      $html = $field->generate();
      parent::generateInner();
      $htmlInner = $this->getInner();
      $this->inner = $html . ( $this->readonly  ? '' : $htmlInner) ;        
   }

}

// end - vgartner

class MLookupField extends MLookupFieldAjax
{}

class MLookupField1 extends MTextField
{
    public $action;
    public $related;
    public $module;
    public $item;
    public $info;
    public $autocomplete=false;
    public $event;
    public $filter;
    public $baseModule;
    public $lookup_name;
    public $showButton=true;
    private $windowType   = 'popup';
    private $windowWidth  = '';
    private $windowHeight = '';
    private $windowTop    = '';
    private $windowLeft   = '';


    public function __construct($name='',$value='',$label='',$hint='', 
                 $related='',$module='',$item='', $event='filler', $filter='')
    {   
        parent::__construct($name,$value,$label,0,$hint);
        $page = $this->page;
        $page->addScript('m_lookup.js');
        
        if(is_array($related))
        {
            ksort($related);
        }
        else
        {
            $related = array(str_replace(' ','',$related));
        }
        $this->related = implode(',',$related);

        $this->module  = $module;
        $this->item    = $item;
        $this->event   = $event ? $event : 'filler';
        $this->filter  = $filter;
        $module = $this->manager->getConf("mad.module");  
        
        $page = $this->page;
        $form = ($this->form == NULL) ? $page->name : $this->form->name;
        $this->lookup_name = $lookup_name = "lookup_{$form}_{$this->name}";
        $this->baseModule = ($module) ? $module : "admin";
    }
    
	function getModuleItem()
    {
        return $this->module . '.' . $this->item;
    }
    
    public function setModuleItem($module, $item)
    {
        $this->module = $module;
        $this->item = $item;
    }

    public function setWindowSize( $width, $height )
    {
        $this->windowWidth  = $width;
        $this->windowHeight = $height;
    }

    public function setWindowType( $windowType='iframe', $width='', $height='', $top='', $left='')
    {
        $this->windowType   = $windowType;
        $this->windowTop    = $top;
        $this->windowLeft   = $left;
        $this->setWindowSize( $width, $height);
    }
    
    /*
        Method to set if the look button should be displayed
    */
    public function setShowButton( $show=true )
    {
        $this->showButton = $show;
    }

   public function generateInner()
   {
        $this->label = $this->label ? '&nbsp;' : '';
        
        $base = $this->baseModule;

        $filter = is_array($this->filter) ? $this->filter : array($this->filter);
        $lookup_name = $this->lookup_name;
        
        $attr = $this->getAttributes();

        if ($this->showButton )
        {
            $content[] = new MSpan('','&nbsp;');
            $button = new MButtonFind("javascript:MIOLO_Lookup($this->lookup_name,'$base', event);");
            $content[] = $button->generate();
        }

        $html =  $this->painter->generateToString($content);
        $html .= "<script language=\"JavaScript\">\n";
        $html .= "    var $this->lookup_name     = new LookupContext();\n";
        $html .= "    $lookup_name.name      = '{$lookup_name}';\n";
        $html .= "    $lookup_name.module    = '{$this->module}';\n";
        $html .= "    $lookup_name.item      = '{$this->item}';\n";
        $html .= "    $lookup_name.related   = '{$this->related}';\n";
        $html .= "    $lookup_name.filter    = '" . implode(',',$filter) . "';\n";
        $html .= "    $lookup_name.idxFilter = '" . implode(',',array_keys($filter)) . "';\n";
        $html .= "    $lookup_name.form      = document.{$this->form->name};\n";
        $html .= "    $lookup_name.field     = '{$this->name}';\n";
        $html .= "    $lookup_name.event     = '{$this->event}';\n";
        $html .= "    $lookup_name.wType     = '{$this->windowType}';\n";
        $html .= "    $lookup_name.wWidth    = '{$this->windowWidth}';\n";
        $html .= "    $lookup_name.wHeight   = '{$this->windowHeight}';\n";
        $html .= "    $lookup_name.wTop      = '{$this->windowTop}';\n";
        $html .= "    $lookup_name.wLeft     = '{$this->windowLeft}';\n";
        $html .= "    $lookup_name.autoPost  = '{$this->autoPostBack}';\n";
        $html .= "    document.lookup        = $lookup_name;\n";
        $html .= "</script>\n";
        $this->inner = $this->generateLabel() . $html;        
   }
}

class MLookupTextField extends MLookupTextFieldAjax
{}

class MLookupTextField1 extends MLookupField
{
    public $autocomplete;
    
    public function __construct($name='',$value='',$label='', 
                 $size=10,$hint='',$validator=null,$related='',
	             $module='',$item='', $event='filler', $filter='', $autocomplete=true)
    {   
        parent::__construct($name,$value,$label,$hint,$related, $module, $item, $event, $filter); //$validator);
        $this->size = $size;
        $this->filter = $filter ? $filter : $this->name;
        $this->autocomplete = $autocomplete ? true : false;
        $this->validator = is_string($validator) ? 
                               Validator::MASKValidator($validator) : $validator;
    }

    public function getAutocompleteData()
    {
        $autocomplete = new MAutoComplete($this->module,$this->item,$this->value,$this->related);
        $info = $autocomplete->getResult();
        return $info;
    }
    
   public function generateInner()
   {
      $field = new MTextField($this->name,$this->value,$this->label,$this->size,$this->hint, $this->validator);
      $field->attrs = $this->attrs;
      if ( $this->autocomplete )
      {
        $field->setAttribute('onChange',"MIOLO_AutoComplete({$this->lookup_name},'$this->baseModule')");
      }
      $field->validator = $this->validator;
      $field->form      = $this->form;
      
      if($this->value && $this->autocomplete)
      {
        $MIOLO = $this->manager;
        
        $info = $this->getAutoCompleteData();

        $info_ = '';
        foreach($info as $i )
        {
            $info_ .= "'$i',";
        }
        $info_ = substr($info_, 0, -1);

        if(MUtil::getBooleanValue($MIOLO->getConf('options.debug')) && $MIOLO->getConf('logs.handler') == 'screen')
        {
            $msg = _M('[autocomplete]: Field @1 not found!', $this->baseModule) . '<br/>';
        }
        $this->page->addJSCode("MIOLO_AutoCompleteDeliver(document, {$this->lookup_name}.form, '$msg', '$this->related', new Array($info_) );");
      }
      
      $field->setClass('m-text-field'); 
      $field->showLabel = $this->showLabel;
      $field->formMode = $this->formMode;
      $field->addBoxStyle('float','left');
	  if ( $this->readonly )
      {
          $field->setClass('m-readonly');
          $field->addAttribute('readonly');
      }
      $html = $field->generate();
      parent::generateInner();
      $htmlInner = $this->getInner();
      $this->inner = $html . ( $this->readonly  ? '' : $htmlInner) ;        
   }

}

class MLookupFieldValue extends MLookupField
{
	function __construct($name='',$value='',$label='', 
                 $size=10,$hint='',$validator=null,$related='',
	             $module='',$item='', $event='', $filter='', $autocomplete=false)
    {   
        parent::__construct($name,$value,$label,$hint,$validator);
        $this->size = $size;
        $this->filter = $this->name;
        $this->validator = is_string($validator) ? 
                               Validator::MASKValidator($validator) : $validator;
    }
    
   public function generateInner()
   {
      parent::generateInner();
      $htmlInner = $this->getInner();
      $field = new MTextField($this->name,$this->value,$this->label,$this->size,$this->hint, $this->validator);
      $field->setClass('m-text-field'); 
      $field->showLabel = $this->showLabel;
      $field->formMode = $this->formMode;
//      $field->addBoxStyle('float','left');
      $field->setClass('m-readonly');
      $field->addAttribute('readonly');
      $html = $field->generate();
      $this->inner = ( $this->readonly  ? '' : $htmlInner) . $html;        
   }

}

class MActiveLookup extends MLookupTextField
{
    public $lheight;
    public $lwidth;
 
	function __construct($name='',$value='',$label='', 
                 $size=10,$hint='',$validator=null,$related='',
	             $module='',$item='', $event='', $filter='', $autocomplete=false)
    {   
        parent::__construct($name,$value,$label,$size,$hint,$validator,$related,$module,$item, $event,$filter,$autocomplete);
        $this->page->addScript('x/x_core.js');
        $this->page->addScript('m_lookup_ajax.js');
        $this->page->addScript('m_popup.js');
    }
    
   public function generateInner()
   {
        $page = $this->page;
        $this->showLabel = ($this->formMode == 2);
        $span = new Span('',$this->label,'m-caption');
        $label = $this->painter->span($span) . $this->painter->BR;
        $form = ($this->form == NULL) ? $page->name : $this->form->name;
        $lookup_name = "lookup_{$form}_{$this->name}";
        $base = $this->baseModule;
        
        $filter = is_array($this->filter) ? $this->filter : array($this->filter);
        $attr = $this->getAttributes();
        $content[] = new MSpan('','&nbsp;');
        $item =  $this->item;
        $w = ($this->lwidth != '') ? $this->lwidth : 400;
        $h = ($this->lheight != '') ? $this->lheight : 250;
        $params = array(
           "name" => $lookup_name,
           "lmodule" => $this->module,
           "event" => $this->event,
           "related" => $this->related,
           "lheight" => $this->lheight,
           "lwidth" => $this->lwidth
        );
        foreach($filter as $k=>$f)
        {
           $params["filter$k"] = $f;
        } 
           
        $url = $this->manager->getActionURL($this->baseModule, 'activelookup', $item, $params);
        $button = new MButtonFind("javascript:MIOLO_ActiveLookup('$lookup_name',200,200,$w,$h, '{$url}','','{$this->name}',1);");
        $content[] = $button->generate();
        if ( $this->readonly )
        {
            $ro = new MSpan($this->name,$this->value,'m-readonly');
            $this->inner = (($this->showLabel && ($this->label != '')) ? $label : '') . $this->painter->span($ro);
            return;
        }
        $field = new MTextField($this->name,$this->value,$this->label,$this->size,$this->hint, $this->validator);
        $field->attrs = $this->attrs;
        $field->setClass('m-text-field'); 
        $html = $field->getRender('inputtext') . $this->painter->generateToString($content);
        $this->inner = $html;        
   }
}



class MDialogLookup extends MLookupTextField
{

	function __construct($name='',$value='',$label='', 
                 $size=10,$hint='',$validator=null,$related='',
	             $module='',$item='', $event='', $filter='', $autocomplete=false)
    {   
        parent::__construct($name,$value,$label,$size,$hint,$validator,$related,$module,$item, $event,$filter,$autocomplete);
        $this->lookupType = 'dialog';
        $this->page->addScript('x/x_core.js');
        $this->page->addScript('x/x_dom.js');
        $this->page->addScript('x/x_event.js');
        $this->page->addScript('x/x_drag.js');
        $this->page->addScript('cpaint/cpaint2.inc.js');
        $this->page->addScript('m_dialog.js');
        $this->page->addScript('m_iframe.js');
//        $this->page->addStyle('m_forms.css'); 
    }
}

?>
