<?php
class ThemebModern extends MTheme
{
    public function __construct()
    {
        parent::__construct('bmodern');
    }
    
    public function init()
    {
        $this->manager->getPage()->addStyle('miolo.css');
        $this->manager->getPage()->addScript('dojoroot/config.js');
        $this->manager->getPage()->addScript('dojoroot/dojo/dojo.js');

        if ( strlen((string) $this->manager->getConf('theme.client')) )
        {
            $this->manager->getPage()->addStyle( $this->manager->getConf('theme.client') .'.css' );
        }
        
        MToolBarButton::setType(MToolBar::TYPE_ICON_TEXT);
        
        $ui = $this->manager->GetUI();

        // navigation
        $this->setElement('navigation', new MNavigationBar(), 'm-container-topmenu');
    }

    public function generate($element='')
    {
        $method = "generate" . $this->layout;
        $this->manager->trace("Theme generate: " . $method);
        return $this->$method($element);
    }

    public function getTemplate()
    {
        $theme = $this->manager->getConf('theme.main');
        $pathName = $this->manager->getConf('home.themes') . "/$theme/template/";
        $template = new MBTemplate($pathName);
        $template->set('miolo', $this->manager);
        $template->set('theme', $this);
        $template->set('form', $this->manager->getPage()->getFormId());
        return $template;
    }

    public function generateBase()
    {
        $template = $this->getTemplate();
        
        if ( $this->manager->checkMobile() )
        {
            return $template->fetch('mobile.php');
        }
        else
        {
            return $template->fetch('base.php');
        }
    }


    public function generateContent()
    {
        $template = $this->getTemplate();
        return $template->fetch('content.php');
    }

    public function generateNavBar()
    {
        $template = $this->getTemplate();
        return $template->fetch('navbar.php');
    }
    
    public function generateMenu()
    {
        $template = $this->getTemplate();
        return $template->fetch('menu.php');
    }

    public function getWebForm($templateFile)
    {
        $template = $this->getTemplate();
        $content = $template->fetch($templateFile); 
        return $this->manager->getPage()->generateForm($content);
    }

    public function generateDefault($element)
    {
        $webForm = $this->getWebForm('default.php');
        $elements[$element] = $webForm->generate();
        return $elements;
    }


    public function generateDynamic($element)
    {
        $elements[$element.'_content'] = $this->generateContent();
        if ($this->hasMenuOptions())
        {
           $elements[$element.'_menu'] = $this->generateMenu();
        }

        return $elements;
    }

    public function generateWindow()
    {
        $webForm = $this->getWebForm('window.php');
        $formId = $this->manager->getPage()->getFormId();
	$elements[$formId] = $webForm->generate();
        return $elements;
    }

    public function generateLookup()
    {
        // only 'content' element
        $html = $this->generateElement('content');
        $div = new MDiv('m-container', $html);
        $div->addStyle('width', '100%');
        return $this->painter->generateToString($div);
    }

    public function generatePopup()
    {
        $page = $this->manager->getPage();
        $this->setElementId('content', 'mThemeContainerContentPopup');
        $html = $this->generateElement('content');
        $divContainer = new MDiv('', $html,  'mContainer');
        $divContainer->addStyle('width', '100%');
        return $this->painter->generateToString($divContainer);
    }

    public function generatePrint()
    {
        $this->generateDefault();
    }

    public function generateDOMPdf()
    {
        // only 'content' element
        $html = $this->generateElement('content');
        $div = new MDiv('mThemeContainer', $html);
        $div->addStyle('width', '100%');
        return $this->painter->generateToString($div);
    }
}
?>
