<?
class MLookupField extends MTextField
{
    public $action;
    public $info;
    public $lookup_name;
    public $showButton=true;
    public $lookupType;
    // context
    public  $baseModule;
    public  $module;
    public  $item;
    public  $lookupEvent;
    public  $filter;
    public  $related;
    public  $autocomplete = false;
    public  $title;
    // window
    public $windowType   = 'popup';
    public $windowWidth  = '';
    public $windowHeight = '';
    public $windowTop    = '';
    public $windowLeft   = '';


    public function __construct($name='',$value='',$label='',$hint='', 
                 $related='',$module='',$item='', $event='filler', $filter='',$title='')
    {   
        parent::__construct($name,$value,$label,0,$hint);
        $this->page->addScript('m_window.js');
        $this->page->addDojoRequire("miolo.Dialog");
        $this->page->addScript('m_lookup.js');

        $related = str_replace(' ','',$related);

        $baseModule = MUtil::NVL($this->manager->GetConf("mad.module"),"admin");
//        $title = MUtil::NVL($title,_M('Lookup Dialog'));
        $event = MUtil::NVL($event,'filler');
        $this->setContext($baseModule,$module,$item,$event,$related,$filter,$autocomplete,$title);

        $this->lookup_name = "lookup_{$this->formId}_{$this->name}";
    }
    
    public function setContext($baseModule='admin',$module='admin',$item='',$event='',$related='',$filter='',$autocomplete=false,$title='')
    {
        $this->baseModule = $baseModule;
        $this->module  = $module;
        $this->item    = $item;
        $this->lookupEvent = $event;
        $this->related = $related;
        $this->filter  = MUtil::NVL($filter,$this->filter);
        $this->autocomplete = $autocomplete;
        $this->title = $title;
    }

    public function getModuleItem()
    {
        return $this->module . '.' . $this->item;
    }
    
    public function setTitle($title='')
    {
        $this->title = $title;
    }

    public function setAutoComplete($autocomplete=true)
    {
        $this->autocomplete = $autocomplete;
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
    
    public function setShowButton( $show=true )
    {
        $this->showButton = $show;
    }

   public function generateInner()
   {
        $this->label = $this->label ? '&nbsp;' : '';
        
        $filter = is_array($this->filter) ? $this->filter : array($this->filter);
        $lookup_name = $this->lookup_name;
        
        $attr = $this->getAttributes();

        if ($this->showButton )
        {
            $button = new MButtonFind("javascript:{$this->lookup_name}.start();");
            $content[] = $button->generate();
        }

        $html =  $this->painter->generateToString($content);

        $aFilter = implode(',',$filter);
        $akFilter = implode(',',array_keys($filter));
        $jsCode =
<<< HERE
        {$this->lookup_name}.setContext({
             baseModule: '{$this->baseModule}',
             name    : '{$lookup_name}',
             module  : '{$this->module}',
             item    : '{$this->item}',
             related : '{$this->related}',
             filter  : '{$aFilter}',
             idxFilter : '{$akFilter}', 
             form    : '{$this->formId}',
             field   : '{$this->name}',
             event   : '{$this->lookupEvent}',
             title   : '{$this->title}',
             autocomplete : '{$this->autocomplete}',
             wType   : '{$this->windowType}',
             wWidth  : '{$this->windowWidth}',
             wHeight : '{$this->windowHeight}',
             wTop    : '{$this->windowTop}',
             wLeft   : '{$this->windowLeft}',
             autoPost: '{$this->autoPostBack}'
        });
HERE;

        
        $this->page->addJsCode("{$this->lookup_name} = new Miolo.Lookup();");
//        $this->page->onLoad($jsCode);
        $this->page->addJsCode($jsCode);
        $div = new MDiv('',$this->generateLabel() . $html, '');
        $this->inner = $div;        
//        $this->inner = $this->GenerateLabel() . $html;
   }
}


?>