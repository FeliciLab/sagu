<?php
class ThemeAvinst extends MTheme
{
    public function init()
    {
        $MIOLO = MIOLO::getInstance();
    	$this->setElement('navigation', new MNavigationBar(), 'mContainerTopMenu');
        $this->manager->uses('classes/atopmenu.class.php', 'avinst');
        $this->setElement('topmenu', new ATopMenu(), 'topMenu');
        $this->setElementClass('topmenu', 'mDivTopSystem');
    }

    public function generate($element='')
    {
        $method = "generate" . $this->layout;
        $this->manager->trace("Theme generate: " . $method);
        return $this->$method($element);
    }

    public function getTemplate()
    {
        $pathName = $this->manager->getConf('home.themes') . '/avinst/template/';
        $template = new MBTemplate($pathName);
        $template->set('miolo', $this->manager);
        $template->set('theme', $this);
        $template->set('form', $this->manager->getPage()->getFormId());
        return $template;
    }

    public function generateBase()
    {
        $template = $this->getTemplate();
        return $template->fetch('base.php');
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

    public function generateTopMenu()
    {
        $template = $this->getTemplate();
        return $template->fetch('topmenu.php');
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
