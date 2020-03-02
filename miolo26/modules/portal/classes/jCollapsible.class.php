<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of jCollapsible
 *
 * @author daniel
 */
class jCollapsible extends MDiv
{
    private $sections;

    public function __construct($name, $sections=array())
    {
        parent::__construct($name);

        $this->addAttribute('data-role', 'collapsible-set');
        //$this->addAttribute('data-content-theme', 'd');
        
        #FIXME: para trocar o tema do componente
        $this->addAttribute('data-theme', 'd');
        $this->addAttribute('data-content-theme', 'd');
        
        #FIXME: devido ao layout
        $this->addStyle('width', '98%');

        $this->sections = $sections;
    }

    public function addSection($title, $controls, $expanded=FALSE)
    {
        $this->sections[] = new jCollapsibleSection($title, $controls, $expanded);
    }

    public function generateInner()
    {
        $this->setInner($this->sections);
        return parent::generateInner();
    }
}

class jCollapsibleSection extends MDiv
{
    public function __construct($title, $controls, $expanded=FALSE, $id=null)
    {
        parent::__construct($id, array( "<h3>$title</h3>", $controls ));
        $this->addAttribute('data-role', 'collapsible');

        if ( $expanded )
        {
            $this->addAttribute('data-collapsed', 'false');
        }        
        
    }
}

?>
