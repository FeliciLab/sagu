<?php
define ('PAGE_ISPOSTBACK', '__ISPOSTBACK');

class MPage extends MControl
{
    public $compliant;
    public $CSS;
    public $styleCode;
    public $scripts;
    public $customScripts;
    public $metas;
    public $title;
	public $action;
	public $enctype;
    public $isPostBack = false;
	public $onload;
	public $onsubmit;
	public $onunload;
	public $onfocus;
    public $onerror;
    public $hasReport;
	public $state;
    public $jscode;
    public $goto;
    public $generateMethod = 'generateAjax';
    public $theme;
    public $ajax;
    public $redirect = false;
	public $file; // object to use with downloads
    public $dojoRequire;
    public $form;
    private $formid;
    public $stdout;
    public $layout;

    public function __construct()
    {   global $state;

        parent::__construct('page' . uniqid());
        $this->compliant     = true;
		$this->enctype       = '';
      	$this->onsubmit      = new MStringList();
      	$this->onload        = new MStringList(false);
      	$this->onerror       = new MStringList(false);
        $this->onunload      = new MStringList();
        $this->onfocus       = new MStringList();
      	$this->jscode        = new MStringList();
      	$this->CSS        = new MStringList(false);
      	$this->styleCode     = new MStringList();
      	$this->scripts       = new MStringList(false);
      	$this->dojoRequire   = new MStringList(false);
      	$this->customScripts = new MStringList(false);
      	$this->metas         = new MStringList();
        $this->title         = $this->manager->getConf('theme.title');
		$this->action        = $this->manager->getCurrentURL();
        $this->isPostBack    = (MIOLO::_REQUEST($this->manager->formSubmit.'__ISPOSTBACK') != '');
        $back = $this->manager->history->back('context');
        $top = $this->manager->history->top('context');
        $this->ajax = $this->manager->ajax;
        $this->formid = $this->manager->formSubmit ? $this->manager->formSubmit : '__mainForm';
        $this->layout  = $this->manager->_request('__THEMELAYOUT');
        $state = $this->state = new MState($this->formid);
        $this->loadViewState();
        $this->loadPostData();
        $this->generateMethod = $this->manager->getIsAjaxCall() ? 'generateAJAX' : 'generateBase';
        $this->manager->trace(print_r($_REQUEST,true));
	}

    public function addStyle($url)
    {   
        $url = $this->manager->getThemeURL($url);
        $this->CSS->add($url);            
    }
    
    public function addStyleURL($url)
    {   
        $this->CSS->add($url);            
    }

    public function addStyleCode($code)
    {   
      $this->styleCode->add($code);
    }

    public function addScript($url, $module=null)
    {
        if ( $module )
        {
            $url = $this->manager->getActionURL( $module, 'scripts:' . $url);
        }
        else
        {
            $url = $this->manager->getAbsoluteURL('scripts/' . $url);
        }
        $this->scripts->add($url);            
    }
    
    public function addScriptURL($url)
    {
        $this->scripts->add($url);            
    }

    /**
     * Include an external JavaScript file.
     *
     * @param string $url URL of the JavaScript file.
     * @param string $onLoad Function to call on load.
     * @param boolean $persistent Whether to don't reload it each request.
     */
    public function addExternalScript($url, $onLoad='null', $persistent=false)
    {
        $persistent = $persistent ? 'true' : 'false';

        $this->onload("miolo.page.includeexternaljs('$url', $onLoad, $persistent);");
    }

    public function insertScript($url)
    {
        $url = $this->manager->getAbsoluteURL('scripts/' . $url);
        $this->scripts->insert($url);            
    }

    public function addDojoRequire($dojoModule)
    {
        $this->jscode->insert("dojo.require(\"{$dojoModule}\");");            
    }

    public function addMeta($name,$content)
    {
      $this->metas->add("<meta name=\"$name\" content=\"$content\">");
    }
    
    public function addHttpEquiv($name,$content)
    {
      $this->metas->add("<meta http-equiv=\"$name\" content=\"$content\">");
    }
    
    public function getStyles()
    {
        return $this->CSS;
    }

    public function getStyleCode()
    {
        return $this->styleCode;
    }
    
    public function setStyles($value)
    {
        $this->CSS->items = is_array($value) ? $value : array($this->manager->getThemeURL($value));
    }

    public function getScripts()
    {
        return $this->scripts;
    }
    
    public function getCustomScripts()
    {
        return $this->customScripts;
    }

    public function getMetas()
    {
        return $this->metas;
    }

    public function getOnLoad()
    {
        return $this->onload;
    }

    public function getOnError()
    {
        return $this->onerror;
    }

    public function getOnSubmit()
    {
        return $this->onsubmit;
    }

    public function getOnUnLoad()
    {
      return $this->onunload;
    }

    public function getOnFocus()
    {
      return $this->onfocus;
    }

    public function getJsCode()
    {
        return $this->jscode;
    }

    public function getFormId()
    {
        return $this->formid;
    }

    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function onSubmit($jscode)
    {
      $this->onsubmit->add($jscode);
    }

    public function onLoad($jscode)
    {
      $this->onload->add($jscode);
    }

    public function onUnLoad($jscode)
    {
      $this->onunload->add($jscode);
    }

    public function onError($jscode)
    {
        $this->onerror->add($jscode);
    }

    public function onFocus($jscode)
    {
      $this->onfocus->add($jscode);
    }

    public function addJsCode($jscode)
    {
      $this->jscode->add($jscode);
    }

    public function isPostBack()
    {  
		return $this->isPostBack;
    }

    public function setPostBack($postback)
    {
        $this->isPostBack = $postback;
    }
    
    /* Used at main form */
	function setAction($action)
	{
		$this->action = $action;
	}

    /* Used at main form */
	function setEnctype($enctype)
	{
		$this->enctype = $enctype;
	}

	function setCompliant($value=true)
	{
		$this->compliant = $value;
	}

	function setFile($name,$content,$type,$length)
	{
        $this->file->name = $name;
        $this->file->content = $content;
        $this->file->type = $type;
        $this->file->length = $length;
	}

    public function request($vars, $component_name = '', $from='ALL')
    {  
        $value = '';
        if ( ($vars != '') )
        {
           $value = MIOLO::_REQUEST($vars, $from);
           if (!isset($value)) 
           {
              if (!$component_name)
              {
                $value = $this->state->get($vars);
              }
              else
              {
                $value = $this->state->get($vars, $component_name);
              }
           }
        }
        return $value;
    }

    public function setViewState($var, $value, $component_name = '')
    {
        $this->state->set($var, $value, $component_name);
    }

    public function getViewState($var, $component_name = '')
    {
        return $this->state->get($var, $component_name);
    }

    public function loadViewState()
    {
        $this->state->loadViewState();
    }

    public function saveViewState()
    {
        $this->state->saveViewState();
    }

    public function loadPostData()
    {
       
    }

    // Set a value for a client element, using DOM
    // This method use a javascript code that is execute on response
    public function setElementValue($element, $value)
    {
        $this->onLoad("miolo.getElementById('{$element}').value = '{$value}';");
    }

    public function copyElementValue($element1, $element2)
    {
        $this->onLoad("miolo.getElementById('{$element1}').value = miolo.getElementById('{$element2}').value;");
    }

    public function setElementAttribute($element, $attribute, $value)
    {
        if (is_string($value))
        {
            $value = "'{$value}'";
        }
        $this->onLoad("miolo.setElementAttribute('{$element}', '{$attribute}', {$value});");
    }

    public function redirect($url)
    { 
         $this->manager->getSession()->freeze();
         $this->goto = str_replace('&amp;','&',$url);
         $this->generateMethod = 'generateRedirect';
    }

    public function window($url)
    { 
         $this->manager->getSession()->freeze();
         $this->goto = str_replace('&amp;','&',$url);
         $this->generateMethod = 'generateWindow';
    }

    public function forward($url)
    { 
         $this->isPostBack = false;
         $_REQUEST['__MIOLOTOKENID'] = $this->manager->getSession()->get('__MIOLOTOKENID');
         $this->goto = str_replace('&amp;','&',$url);
         $this->manager->forward = $this->goto;
         $this->manager->context->parseUrl($this->goto);
    }

    public function insert($url)
    { 
         $this->goto = str_replace('&amp;','&',$url);
         $context = clone $this->manager->context;
         $this->manager->context->parseUrl($this->goto);
         $this->manager->invokeHandler($this->manager->context->module,$this->manager->context->action);
         $this->manager->context = $context;
    }

    /* 
        deprecated at 2.5

    public function refresh()
    { 
       $this->onLoad('document.' . $this->name . '.submit();'); 
    }
    */

    public function generate()
    {
        $styleCode = $this->getStyleCode()->getTextByTemplate("/:v/");
        if ( $styleCode )
        {
            $this->onLoad("miolo.page.setDynamicStyle('$styleCode');");
        }

        $this->manager->logMessage('[PAGE] Generating Page : ' . $this->generateMethod);
	    return $this->{$this->generateMethod}();
    }

    public function generateRedirect()
    {
        if ( $this->manager->getIsAjaxCall() )
        {
            $tokenId = $this->manager->getSession()->get('__MIOLOTOKENID');
            $scripts = array('',"miolo.page.tokenId = '$tokenId'; miolo.doRedirect('{$this->goto}','__mainForm');",'','','');
            $this->ajax->setResponseScripts($scripts);
            $response = $this->ajax->response;
            $this->ajax->set_data($response);
            $this->ajax->return_data();
        }
        else
        {
            header('Location:'.$this->goto);
        }
    }

    public function generateWindow()
    {
        if ( $this->manager->getIsAjaxCall() )
        {
            $scripts = array('',"miolo.doWindow('{$this->goto}','__mainForm');",'','','');
            $this->ajax->setResponseScripts($scripts);
            $response = $this->ajax->response;
            $this->ajax->set_data($response);
            $this->ajax->return_data();
        }
        else
        {
            header('Location:'.$this->goto);
        }
    }

    private function sendTokenId()
    {
        $tokenId = $this->manager->getSession()->get('__MIOLOTOKENID');
        mdump('sending token id = ' . $tokenId);
        $this->onload("miolo.page.tokenId = '$tokenId';");
    }

    public function generateForm($htmlContent)
    {
//        $this->sendTokenId();
        $formId = $this->getFormId();
        $this->form = new MHtmlForm('frm_'.$formId);
        $content[] = $htmlContent;
        $content[] = new MHiddenField($formId.'__VIEWSTATE',$this->state->getViewState());
        $content[] = new MHiddenField($formId.'__ISPOSTBACK');
        $content[] = new MHiddenField($formId.'__EVENTTARGETVALUE');
        $content[] = new MHiddenField($formId.'__EVENTARGUMENT');
        $this->form->setContent($content);
        $this->form->setEnctype($this->enctype);
        $this->form->setAction($this->action);
        $this->form->setOnSubmit("miolo.submit(); return false;");
        return $this->form;
    }

    public function generateBase()
    {  
        $this->sendTokenId();
        $this->theme = $this->manager->getTheme();
        $this->theme->setLayout('base');
        $content = $this->theme->generate($this->getFormId());               
        return $content;
    }

    public function generateAJAX()
    {
        $this->theme = $this->manager->getTheme();
        $this->saveViewState();
        $this->setElementValue($this->state->getIdElement(),$this->state->getViewState());
        
        if ($this->ajax->response_type == 'TEXT')
        {
            $response = $this->theme->generateElementInner('ajax'); 
            $this->ajax->set_data($response);
        } 
        elseif ($this->ajax->isEmpty())
        {
            $this->sendTokenId();
            
            //Hack feito para resolver problema de navegação da lookup relatado pelo ticket #11680
            if ( $this->ajax->response != NULL )
            {
                if( trim($this->ajax->response->html[0]) == "\\" && trim($this->ajax->response->element[0]) == "stdout" )
                {
                    $this->ajax->response = NULL;
                }
            }
            
            if ($this->ajax->response == NULL)
            {
                $this->theme->setLayout($this->layout);
                $content = $this->theme->generate($this->getFormId());               
                $element = array_keys($content);

                $this->ajax->setResponseControls($this->stdout, "stdout");
                ob_end_clean();                 
                $this->ajax->setResponseControls($content, $element);
            } 

            $scripts[0] = $this->getScripts()->getTextByTemplate("<script type=\"text/javascript\" src=\"/:v/\"></script>\n");
            $scripts[1] = $this->getJsCode()->getValueText('',chr(13));
            $scripts[2] = ($onload = $this->getOnLoad()->getValueText('',chr(13))) ? "{$onload}" : '';
            $scripts[3] = ($this->getOnUnLoad()->getValueText('',chr(13))) . " var result = " . (($o = $this->getOnSubmit()->getValueText('',' && ' . chr(13))) ? $o : 'true') . ";return result;";
            $scripts[4] = ($onerror = $this->getOnError()->getValueText('',chr(13))) ? "{$onerror}" : '';

            // Fix to make the browser not face the string </script> as end of script tag
            $scripts[2] = str_replace('</script>', '<\/script>', $scripts[2]);

//mdump($scripts[0]);
//mdump($scripts[1]);
//mdump($scripts[2]);
//mdump($scripts[3]);
//mdump($scripts[4]);

            $this->ajax->setResponseScripts($scripts);
            $this->ajax->setResponseForm($this->getFormId());
//      $this->manager->trace($this->ajax->response->html[0]);
//      $this->manager->trace($this->ajax->response->html[1]);
//      $this->manager->trace($this->ajax->response->html[2]);

            $response = $this->ajax->response;
            $this->ajax->set_data($response);
        } 
        $this->ajax->return_data();
    }

    public function generateFile()
    {
       $this->sendTokenId();
       $response = $this->manager->response;
	   $response->setContentType($this->file->type);
       $response->setContentLength($this->file->length);
       $response->setFileName($this->file->name);
       $response->sendBinary($this->file->content); 
    }

    public function generateDOMPdf()
    {
       $this->theme = $this->manager->getTheme();
       $this->addHttpEquiv('Content-Type','text/html; charset=ISO-8859-1');
       $this->addStyle('miolo.css');
       return $this->painter->dompdf($this); 
    }
}
?>
