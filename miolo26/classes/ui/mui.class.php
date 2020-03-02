<?php

class MUI extends MService
{
    public $icons;
    public $php;

    public function __construct()
    {
        parent::__construct(); 
        $this->loadIcons($this->manager->getTheme()->getPath() . '/' . 'miolo.css');
        $this->php = $this->manager->getConf("options.fileextension") == '2' ? '' : '.php';
    }

    public function loadIcons($file)
    {
        $css = file_get_contents($file);
        preg_match_all("/icon-(.*?)[\s]*\{[\s]*background-image:[\s]*url\((.*?)\);[\s]*\}/i", $css, $match);
        $n = count($match[1]);
        for($i=0; $i < $n; $i++)
        {
            $this->icons[$match[1][$i]] = $match[2][$i];
        }
    }

    public function getIcon($icon)
    {
        return $this->icons[$icon];
    }

    public function alert( $msg, $info, $href = '' )
    {
        $this->manager->error( $msg, $info, $href );
    }


    public function createForm( $title = '' )
    {
        return new MForm($title);
    }

    /**
     * Get form object.
     *
     * @param string $module Module name.
     * @param string $name Form name.
     * @param object $data Form data.
     * @param string $dir Form directory inside $module/forms/.
     * @return MForm Form instance.
     */
    public function getForm( $module, $name, $data = NULL, $dir = NULL )
    {  
        $path = $this->manager->getModulePath( $module, 'types.class' . $this->php );

        if ( file_exists( $path ) )
        {
            $this->manager->uses( 'types.class' . $this->php, $module );
        }

        $file = is_null($dir) ? "forms/$name.class" .$this->php : "forms/$dir/$name.class" .$this->php;

        $path = $this->manager->getModulePath( $module, $file );

        if ( file_exists( $path ) )
        {
            $this->manager->uses( $file, $module );
            $className = $name;
            $form = new $className( $data );

            return $form;
        }
        else
        {
            throw new EFileNotFoundException( $file, "UI::getForm() :" );
        }
    }


    public function getFormIn($module, $class, $name, $data = NULL, $dir = NULL )
    {
        $path = $this->manager->getModulePath( $module, 'types.class' );

        if ( file_exists( $path ) )
        {
            $this->manager->uses( 'types.class', $module );
        }

        $file = is_null($dir) ? "forms/$class.class" .$this->php : "forms/$dir/$classe.class" .$this->php;

        $path = $this->manager->getModulePath( $module, $file );

        if ( file_exists( $path ) )
        {
            $this->manager->uses( $file, $module );
            $className = $name;

            $form = new $className( $data );

            return $form;
        }
        else
        {
            throw new EFileNotFoundException($file, 'UI::getForm() :');
        }
    }


    public function getMenu( $module = 'main', $name = 'UIMainMenu', $dir = NULL )
    {
//        $this->manager->uses( "menus/$name.class" .$this->php, $module );
//        $menu = new $name();

        $file = is_null($dir) ? "menus/$name.xml" : "menus/$dir/$name.xml";
        $path = $this->manager->getModulePath( $module, $file );
        if ( file_exists( $path ) )
        {
            $mxml = new MSimpleXML($path);
            $xml = $mxml->xml;
//$this->manager->trace(print_r($xml,true));
            foreach($xml->menu as $m)
            {
//$this->manager->trace(print_r($m,true));
                $menu = $this->manager->getTheme()->getMenu((string)$m->id);
                $menu->setTitle($m->caption);
                $menu->isSubMenu = MUtil::getBooleanValue($m->submenu);
                foreach($m->items->item as $item)
                {
                    if($item->menu)
                    {
                        $subMenu = $this->manager->getTheme()->getMenu((string)$item->menu);
                        $subMenu->isSubMenu = true;
                        $menu->addMenu($subMenu);
                    }
                    else
                    {
                        if ($item->transaction)
                        {
                            $menu->addOption( _M($item->caption), $item->module, $item->action, '', '', $item->icon );
                        }
                        else
                        {
                            $menu->addUserOption( $item->transaction, $item->access, _M($item->caption), $item->module, $item->action, '', '', $item->icon );
                        }
                    }
                    if ($item->separator)
                    {
                        $menu->addSeparator();
                    }
                }
            }
            return $menu;
        }
        else
        {
            throw new EFileNotFoundException($file, 'UI::getMenu() :');
        }
    }


    public function getListing( $module, $name, $data = null, $dir = null )
    {
        global $state;
        $MIOLO = MIOLO::getInstance();
        $session = $MIOLO->getSession();

        if ( $dir )
        {
            $file = "listings/$dir/$name.class" .$this->php;
        }
        else
        {
            $file = "listings/$name.class" .$this->php;
        }

        $MIOLO->assert( file_exists( $MIOLO->getModulePath( $module, $file ) ),
                        'UI::getListing() ' . _M( 'Error: @1 - file not found.', 'miolo', $file )
                       );

        $MIOLO->uses($file, $module);

        // Workaround: adds module's prefix to class name
        $class = strtoupper( substr( $module, 0, 1) ) . substr($module, 1) . $name;

        if ( ! class_exists( $class ) )
        {
            $MIOLO->deprecate( "You should name your listing class '$class' instead of '$name'!" );

            $class = $name;
        }

        if ( $data )
        {
            $stmt = "\$listing = new $class(\$data);";
        }
        else
        {
            $stmt = "\$listing = new $class();";
        }

        eval( $stmt );

        return $listing;
    }

    /**
     * Get grid object.
     *
     * @param string $module Module name.
     * @param string $name Grid name.
     * @param object $data Grid data.
     * @param string $dir Grid directory inside $module/grids/.
     * @return MGrid Grid instance.
     */
    public function getGrid( $module, $name, $data = NULL, $dir = NULL )
    {
        $file = is_null($dir) ? "grids/$name.class" .$this->php : "grids/$dir/$name.class" .$this->php;

        $path = $this->manager->getModulePath( $module, $file );

        if ( file_exists($path) )
        {
            $this->manager->uses( $file, $module );
            $className = $name;

            $grid = new $className( $data );

            return $grid;
        }
        else
        {
            throw new EFileNotFoundException( $file, 'UI::getGrid() :' );
        }
    }


    public function getReport( $module, $name,  $data = NULL, $dir = NULL )
    {
        $file = is_null($dir) ? "reports/$name.class" .$this->php : "reports/$dir/$name.class" .$this->php;

        $path = $this->manager->getModulePath( $module, $file );

        if ( file_exists($path) )
        {
            $this->manager->uses( $file, $module );
            $className = $name;

            $report = new $className( $data );

            return $report;
        }
        else
        {
            throw new EFileNotFoundException( $file, 'UI::getReport() :' );
        } 
    }


    public function getImage( $module, $name )
    {
        $MIOLO = $this->manager;

        if ( ($m = $module) == NULL )
        {
            $url = $MIOLO->getAbsoluteURL("/images/$name");
        }
        else
        {
            $url = $MIOLO->getActionURL( $m, "images:$name" );
        }

        return $url;
    }


    /**
     * Get theme image URL.
     *
     * @param string $theme Theme id. If is NULL, current theme id is used.
     * @param string $name Image name.
     * @return string Image URL.
     */
    public function getImageTheme($theme, $name)
    {
        if ( !isset($theme) )
        {
            $theme = $this->manager->theme->id;
        }

        return $this->manager->getAbsoluteURL("/themes/$theme/images/$name");
    }


    public function getImageSrc( $name, $module = '' )
    {
        $MIOLO = MIOLO::getInstance();

        $home = ($module == '') ? $MIOLO->getConf('home.images') : $MIOLO->getConf('home.modules') . '/'.$module . $MIOLO->getConf('home.module.images');

        return $home . '/' . $name;
    }

    public function getWindow($winId, $modal = false, $reload = false, $inset = false, $params = array())
    {
        $link = "javascript:miolo.getWindow('{$winId}').open();event.stopPropagation();";
        return $link;
    }

    public function closeWindow($winId = '')
    {
        $link = "javascript:miolo.getWindow('{$winId}').close();";
        return $link;
    }

    public function getAjax($action)
    {
        if ( $action{0} == ':' )
        {
            $action = substr($action, 1);
        }
        $eventTokens = explode(';', $action);
        $formId = $this->manager->getPage()->getFormId();

        $link = "javascript:miolo.doAjax('{$eventTokens[0]}','{$eventTokens[1]}','$formId');";
        return $link;
    }
}
?>
