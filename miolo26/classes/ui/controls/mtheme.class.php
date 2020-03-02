<?php
class MTheme extends MContainerControl
{
    public $layout;
    public $halted;
    public $path;
    public $module;
    public $form;
    public $instances;

    public function __construct($id, $module = '')
    {
        parent::__construct($id);
        $this->instances = array();
        $this->halted = false;
        if ($module != '')
        {
            $this->setModule($module);
        }
    }
    
    public function googleAnalytics()
    {
        $MIOLO = MIOLO::getInstance();

        static $called = false;
        
        if ( !$called && $MIOLO->getConf('temp.is.from.sagu') == true && $MIOLO->getIsAjaxCall() )
        {
            $called = true;
            $code = SAGU::getAnalyticsCode();
            
            if ( strlen($code) > 0 )
            {
                $MIOLO->page->addJsCode($code);
            }
        }
    }

    public function getId()
    {
        $this->googleAnalytics();
        
        return ($this->id == '' ? 'miolo' : $this->id);
    }

    public function getFavicon()
    {
        $favicon = "";
        
        $path = "{$this->manager->getConf('home.html')}/themes/{$this->id}/images/favicon.ico";
            
        if( file_exists($path) )
        {
            $url = $this->manager->GetThemeURL("images/favicon.ico");

            $favicon = "<link rel='icon' href='$url' />";

        }
        
        return $favicon;
        
    }
    
    public function setPage($page)
    {
        $this->page = $page;
    }

    public function getLayout()
    {
        return $this->layout;
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function setModule($module)
    {
        $this->module = $module;
    }

    public function setHalted($value)
    {
        $this->halted = $value;
    }

    public function getInstance($element)
    {
        $element = strtolower($element);

        if (($instance = $this->getControlById($element)) == NULL)
        {
            $instance = new MThemeElement($element);
            $this->addControl($instance);
        }

        return $instance;
    }

    public function getElement($element, $key = 0)
    {
        return $this->getInstance($element)->get($key);
    }

    public function getElementById($element, $id)
    {
        return $this->getInstance($element)->getElementById($id);
    }

    public function setElement($element, $content, $id = '', $key = NULL)
    {
        $this->getInstance($element)->set($content, $id, $key);
    }

    public function setElementId($element, $id)
    {
        $instance = $this->getInstance($element);
        $instance->setId($id);
        $this->setControlById($instance, $id);
    }

    public function setElementClass($element, $cssClass)
    {
        $this->getInstance($element)->setClass($cssClass);
    }

    public function clearElement($element, $ignoreHalted = false)
    {
        $halted = $ignoreHalted ? false : $this->halted;
        $this->getInstance($element)->clear($halted);
    }

    public function insertElement($element, $content, $key = NULL, $ignoreHalted = false)
    {
        $halted = $ignoreHalted ? false : $this->halted;
        $this->getInstance($element)->insert($content, $key, $halted);
    }

    public function appendElement($element, $content, $key = NULL, $ignoreHalted = false)
    {
        $halted = $ignoreHalted ? false : $this->halted;
        $this->getInstance($element)->append($content, $key, $halted);
    }

    public function countElement($element)
    {
        return $this->getInstance($element)->count();
    }

    public function generateElement($element)
    {
        return $this->getInstance($element)->generate();
    }

    public function generateElementInner($element)
    {
        return $this->getInstance($element)->generateInner();
    }

    // element: menus

    public function clearMenus()
    {
        $this->clearElement('menus');
    }

    public function getMenu($name)
    {
        $menus = $this->getInstance('menus');

        if ( ( $menu = $menus->getElementById($name) ) == NULL )
        {
            if ( $this->manager->getConf('options.mainmenu') == 3 )
            {
                $menuClass = 'MDHTMLMenu2';
            }
            else
            {
                $menuClass = 'Menu';
            }
            $menu = new $menuClass($name);
            $menu->setTitle($name);
            $menus->append($menu, $name);
        }

        return $menu;
    }

    public function getMainMenu()
    {
        return $this->getMenu('miolo_main_menu');
    }

    public function hasMenuOptions()
    {
        $menus = $this->getInstance('menus')->getControls();

        if (count($menus))
        {
            foreach ($menus as $menu)
            {
                if ($menu->hasOptions() && (!$menu->isSubMenu))
                {
                    return true;
                }
            }
        }

        return false;
    }

    // element: content

    public function isEmptyContent()
    {
        return ($this->getElement('content') == '');
    }

    public function clearContent($ignoreHalted = false)
    {
        return $this->clearElement('content', $ignoreHalted);
    }

    public function getContent()
    {
        return $this->getElement('content');
    }

    public function setContent($content)
    {
        if ( $this->getContent() == NULL )
        {
            return $this->setElement('content', $content);
        }
        else
        {
            $this->clearContent();
            $this->appendContent($content);
        }
    }

    public function insertContent(&$element, $ignoreHalted = false)
    {
        return $this->insertElement('content', $element, NULL, $ignoreHalted);
    }

    public function appendContent(&$element, $ignoreHalted = false)
    {
        return $this->appendElement('content', $element, NULL, $ignoreHalted);
    }

    public function breakContent($space = '20px')
    {
        return $this->getInstance('content')->space($space);
    }

    public function setAjaxContent($content)
    {
        $this->setElement('ajax', $content);
    }

    /* get a CSS file content */
    public function getCSSFileContent($CSSFileName)
    {
        if ( MUtil::getBooleanValue( $this->manager->getConf('options.performance.uri_themes') ) == true )
        {
            $path = $this->manager->getConf('home.html').'/themes/'.$this->name.'/';
        }
        else
        {
            $path = $this->manager->getConf('home.themes').'/'.$this->name.'/';
        }
        if (file_exists($path . $CSSFileName))
        {
            $arquivo_css = $path . $CSSFileName;
        }
        $path = $this->manager->getConf('home.modules').'/'.$this->manager->getContext()->module. $this->manager->getConf('home.module.themes') . '/' . $this->name . '/'; 
        if (file_exists($path . $CSSFileName))
        {
            $arquivo_css = $path . $CSSFileName;
        }
        $fp = fopen ($arquivo_css, "r") or die($CSSFileName . " Arquivo nÃ£o encontrado");
        $conteudo = fread($fp, filesize ($arquivo_css));
        fclose($fp);
        return $conteudo;
    }

    #++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    # Internal function used to generate some useful informations for the
    # MIOLO developer
    #----------------------------------------------------------------------
    public function generateTraceStatus()
    {
        $status = $this->manager->getTraceStatus();

        if ($this->traceStatus)
        {
            if (is_object($status) || ($status && !is_array($status)))
            {
                $status = array($status);
            }

            if (is_array($this->traceStatus))
            {
                if ($status)
                {
                    $status = array_merge($status, $this->traceStatus);
                }
                else
                {
                    $status = $this->traceStatus;
                }
            }
            else
            {
                $status[] = $this->traceStatus;
            }
        }
    }
}
?>