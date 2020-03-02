<?php

class MDHTMLMenu2 extends MOptionList
{
    private static $order = 0;
    private $menuOptions;
    private $nOrder;
    private $template;
    private $action;
    private $target;
    private $items;
    private $jsItems;

    private $themeStyle;

    private $jsMenu;

    public $isSubMenu;

    public function __construct($name='', $template=0, $action='', $target='_blank')
    {
        parent::__construct($name);
        $page = $this->page;

        $this->themeStyle = $this->manager->getConf('options.mainmenu.style');

 //old       $this->addStyleFile('m_dhtmlmenu2_'.$this->themeStyle.'.css');
//        $page->addScript('jscookmenu/jscookmenu.js');
//        $page->addScript('jscookmenu/jscookmenu_'.$this->themeStyle.'.js');

        $this->items = NULL;
        $this->template = $template;
        $this->action = $action; 
        $this->target = $target;
        $this->isSubMenu = false;
        $this->menuOptions = array();
        $this->nOrder = MDHTMLMenu2::$order++;
    }

    public function getTitle()
    {
        return $this->caption;
    }

    public function setTitle($title, $image1=null, $image2=null, $module=null, $action=null, $item=null, $args=null)
    {
        if ( sizeof($this->menuOptions) )
        {
            $this->menuOptions[0][1] = $title;
            $this->menuOptions[0][3] = 'root';
            $this->menuOptions[0][4] = $image1;
        }
        else
        {
            if ( $module && $action )
            {
                $control = new MLink(NULL, $label);
                $control->setAction($module, $action, $item, $args);
                $link = $control->href;
            }

            $this->menuOptions[] = array('0', $title, '', 'root', $image1, $image2, $link);
        }
    }

    public function addOption($label, 
                       $module = 'common', 
                       $action = 'main', 
                       $item = null, 
                       $args = null, 
                       $normalImage = '',
                       $overImage = '')
    {
        $control = new MLink(NULL,$label);
        $control->setAction($module,$action,$item,$args);
        $this->menuOptions[] = array($control->href, $label, $normalImage,$overImage);
    }

    public function addUserOption( $transaction, 
                                   $access, 
                                   $label, 
                                   $module = 'common', 
                                   $action = 'main', 
                                   $item = '', 
                                   $args = null, 
                                   $normalImage = '',
                                   $overImage = '' )
    {
        if ( $this->manager->perms->checkAccess( $transaction, $access ) )
        {
            $this->addOption($label, $module, $action, $item, $args, $normalImage, $overImage);
        }
    }

    public function addGroupOption($transaction, 
                            $access,
                            $label,
                            $module = 'common',
                            $action = 'main',
                            $item = '',
                            $args = null, 
                            $normalImage = '',
                            $overImage = '')
    {
        if ( $this->manager->perms->checkAccess($transaction, $access, false, true) )
        {
            $this->addOption($label,$module,$action,$item,$args,$normalImage, $overImage);
        }
    }

    public function addLink($label, $link = '#', $target = '_self', $normalImage=null,$overImage=null)
    {
        $this->menuOptions[] = array($link, $label, "link", $target, $normalImage, $overImage);
    }

    public function addMenu( $menu )
    {
        $this->menuOptions[] = $menu;
    }

    public function getMenu($label)
    {
        $subMenu = new MDHTMLMenu2($label);
        $this->menuOptions[] = $subMenu;

        return $subMenu;
    }

    public function addSeparator()
    {
        $this->menuOptions[] = array('-', '', '', 'separator');
    }

    public function hasOptions()
    {
        return ( count($this->menuOptions) > 0 );
    }

    private function getOptionImage($menu, $start)
    {
        global $theme;
        $img   = '';
        $start = (int) $start;

        if ( $menu[$start] )
        {
            if ( $menu[$start+1] )
            {
                $seq1 = 'class=\"seq1\"';
            }

            $img = '<img '. $seq1 .' src='. $this->manager->getUI()->getImageTheme($theme->name, $menu[$start]) .' />';
        }

        if ( $menu[$start+1] )
        {
            $img .= '<img class=\"seq2\" src='. $this->manager->getUI()->getImageTheme($theme->name, $menu[$start+1]) .' />';
        }

        if ( $img == '' )
        {
            $img = null;
        }
        else
        {
            $img = "'". $img ."'";
        }

        return $img;
    }

    private function createMenu()
    {
        global $theme;
        $module = MIOLO::getCurrentModule();
        $MIOLO = $this->manager;
        $start = $startSub = true;

        //$themeDir = $this->manager->getConf('home.themes'). PATH_SEPARATOR .$theme->name. PATH_SEPARATOR . 'images';

        foreach ( $this->menuOptions as $menu )
        {
            if ( ! $startSub )
            {
                $compl =  ',';
            }
            else
            {
                $compl = '';
            }

            if ( is_object($menu) ) // sub-menu
            {
                $this->jsMenu .= $compl . $menu->createMenu();

                $startSub = false;
            }
            else if ( $menu[0] == '0' && $menu[3] ) //main option
            {
                if ( ! $start ) // close the existing option
                {
                    echo "],";
                }

                $img = $this->getOptionImage($menu, 4);

                // ['icon', 'title', 'url', 'target', 'description'],
                $this->jsMenu .= "[".$img.", \"".$menu[1]."\", \"".$menu[6]."\", null, null,\n";
                $start    = false;
                $startSub = true;
            }
            else if ( $menu[3] == 'separator' )
            {
                $this->jsMenu .= ', _cmSplit';

            }
            else if ( $menu[2] == 'link' )
            {
                //array($link, $label, "link", $target, $normalImage, $overImage);
                $img = $this->getOptionImage($menu, 4);

                $linkURL = "<a href=\"$menu[0]\" target=\"$menu[3]\">$menu[1]</a><br/>\n";

                // ['icon', 'title', 'url', 'target', 'description'],
                $this->jsMenu .=  $compl . "    [".$img.", \"$menu[1]\", \"$menu[0]\", \"$menu[3]\", null]";

                $startSub = false;
            }
            else
            {
                $img = $this->getOptionImage($menu, 2);

                $this->jsMenu .=  $compl . "    [".$img.", \"$menu[1]\", \"$menu[0]\", null, null]";

                $startSub = false;
            }

            //MIOLO::var_dump($this->jsMenu);
        }

        $this->jsMenu .=  ']';
        return $this->jsMenu;
    }

    public function generateInner()
    {
        if ($this->isSubMenu) return;

        $this->createMenu();

        $form = ($this->form == NULL) ? $page->name : $this->form->name;

        $themeName = 'Theme'.ucfirst($this->themeStyle);

        $html = "<td id=\"mThemeContainerMenu{$this->nOrder}\">";
        $html .= "</td>";
        $this->inner = $html;

        $code .= "var MAIN_MENU_JS_{$this->nOrder} = [ " . $this->jsMenu . "];";

        if ( MUtil::getBooleanValue( $this->manager->getConf('options.mainmenu.clickopen') ) == true )
        {
            $code .= "cm$themeName.clickOpen=2; ";
        }
        $code .= "cmDraw( 'mThemeContainerMenu{$this->nOrder}', MAIN_MENU_JS_{$this->nOrder}, 'hbr', cm$themeName, '$themeName');";
        $this->page->onload($code);


        //MIOLO::var_dump($this);
    }

}

?>
