<?php
class ThemePortal extends MTheme
{
    public function init()
    {
        include($this->manager->getConf('home.classes') . '/contrib/Mobile_Detect.php');
        $this->manager->mobileDetect = $detect = new Mobile_Detect();

        $this->manager->setMobile($detect->isMobile());

        $this->setElement('navigation', new MNavigationBar(), 'mContainerTopMenu');
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
        return $template->fetch('base.php');
    }

    public function generateContent()
    {
        $template = $this->getTemplate();
        return $template->fetch('content.php');
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
}
?>
