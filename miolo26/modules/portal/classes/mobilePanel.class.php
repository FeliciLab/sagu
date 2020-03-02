<?php

/**
 * Description of mobilePanel
 *
 * @author jonas
 */
class mobilePanel extends MActionPanel
{
    public $width = '30%';
    public $height = '150px';
    public $widthIcon = '100px';
    public $heightIcon = '100px';
    
    public function _getControl($label, $image, $actionURL, $target = NULL, $size = '20')
    {
	$label = '<div style=font-size:'.$size.'px;font-weight:bold;letter-spacing:-1px;text-decoration:none>'.$label.'</div>';
        $control = new MImageLinkLabel('', $label, $actionURL, $image);
	$control->image->addStyle('width', $this->widthIcon);
	$control->image->addStyle('height', $this->heightIcon);
	
        return $control;
    }

    public function addControl($control, $width = '', $float = 'left', $class = '', $onclick = null)
    {
        if ( is_array($control) )
        {
            foreach ($control as $c)
            {
                $this->addControl($c, $width, $float);
            }
        }
        else
        {
            $cell = ($control instanceof MDiv) ? $control : new MDiv('',$control);
            $cell->setClass($class . ' ' . 'mPanelCellBox mPanelCell' . ucfirst($float));
            $cell->addAttribute('data-role', 'button');
            $cell->addStyle('width', $this->width);
            $cell->addStyle('height', $this->height);
            
            $cell->addAttribute('onclick', $onclick);

            parent::addControl($cell);
        }
    }
    
    public function addAction($label, $image, $module = 'main', $action = '', $item = NULL, $args = NULL, $onclick = null)
    {
        $actionURL = $this->manager->getActionURL($module, $action, $item, $args);
        $control = $this->_getControl($label, $image, $actionURL);
        $class = 'mPanelCell'.ucfirst($this->iconType);
        if ( !$onclick && $module == 'portal' )
        {
            $onclick = "miolo.doLink('" . $actionURL . "','__mainForm'); return false;";
        }
        $this->addControl($control,'','left',$class, $onclick);
    }
    
    public function addActionAJAX($label, $image, $action, $args = null)
    {
        $label = '<div style=font-size:20px;font-weight:bold;letter-spacing:-1px;text-decoration:none>'.$label.'</div>';
        $control = new MImageLinkLabel('', $label, '', $image);
        $control->image->addStyle('width', $this->widthIcon);
	$control->image->addStyle('height', $this->heightIcon);
        $class = 'mPanelCell'.ucfirst($this->iconType);
        
        $this->addControl($control,'','left',$class, MUtil::getAjaxAction($action, $args));
    }

    public function addActionBiblioteca($description)
    {
        $MIOLO = MIOLO::getInstance();
        $module = 'portal';
     
        $url = SAGU::getParameter('BASIC', 'GNUTECA_URL');
        
        if ( strlen($url) == 0 )
        {
            $url = $MIOLO->getConf('home.url') . '/biblioteca';
            $url = str_replace('//', '/', $url);
        }
                
        $goto = "window.open('{$url}', '_blank'); return false";
        
        $this->addAction($description, $MIOLO->getUI()->getImageTheme($module, 'library.png'), $module, null, null, null, $goto);
    }
    
    public function addActionMoodle($description)
    {
        $MIOLO = MIOLO::getInstance();
        $module = 'portal';
        
        $this->addAction($description, $MIOLO->getUI()->getImageTheme($module, 'moodle.png'), $module, 'main:acessoMoodle');
    }
}

?>
