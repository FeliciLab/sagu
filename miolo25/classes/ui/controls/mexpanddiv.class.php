<?php

/**
 * Expandable div component.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2010/07/13
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2010-2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */

$MIOLO = MIOLO::getInstance();
$MIOLO->page->addScript('m_expanddiv.js');

class MExpandDiv extends MDiv 
{
    /**
     * CSS classes used by the component.
     */
    const STYLE_COLLAPSED = 'mExpandDivButton mExpandDivButtonCollapsed';
    const STYLE_EXPANDED = 'mExpandDivButton mExpandDivButtonExpanded';
    const STYLE_TEXT = 'mExpandDivText';

    /**
     * @var object MDiv instance containing the content.
     */
    private $box;

    /**
     * @var integer Height of the div collapsed.
     */
    private $collapsedHeight;

    /**
     * @var string Div content.
     */
    private $content;

    /**
     * @var object MDiv instance which represents the expand/collapse button.
     */
    private $expandButton;

    /**
     * @var boolean Define if the div must be expanded by default.
     */
    private $expandedOnLoad = false;

    /**
     * MExpandDiv constructor.
     *
     * @param string $name Name.
     * @param sring $content Content.
     * @param string $class Style class name.
     * @param array $attributes Attributes array.
     * @param integer $collapsedHeight Height of the div collapsed.
     */
    public function __construct($name, $content, $class=NULL, $attributes=NULL, $collapsedHeight=16)
    {
        $id = $name ? "{$name}box" : 'box' . rand();

        $this->setContent($content);
        $this->collapsedHeight = $collapsedHeight;
        $this->expandButton = new MDiv("{$id}Button", '', self::STYLE_COLLAPSED);
        $this->box = new MDiv($id, $this->content, self::STYLE_TEXT);

        $div[] = new MDiv("{$id}Div", array( $this->expandButton, $this->box ));

        parent::__construct($name, $div, $class, $attributes);
    }

    /**
     * @return object Get the MDiv instance which contains the content.
     */
    public function getBox()
    {
        return $this->box;
    }

    /**
     * @param object $box Set the MDiv instance to contain the content.
     */
    public function setBox($box)
    {
        $this->box = $box;
    }

    /**
     * @return integer Get the div height when collapsed.
     */
    public function getCollapsedHeight()
    {
        return $this->collapsedHeight;
    }

    /**
     * @param integer $collapsedHeight Set the div height when collapsed.
     */
    public function setCollapsedHeight($collapsedHeight)
    {
        $this->collapsedHeight = $collapsedHeight;
    }

    /**
     * @return string Get the div content.
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the div content.
     * Removes line breaks from the beginning and the end of the content and 
     * then change them to '<br />'.
     *
     * @param string $content Div content.
     */
    public function setContent($content)
    {
        $this->content = nl2br(trim((string) $content));
    }

    /**
     * @return object Get the MDiv instance which represents the expand/collapse button.
     */
    public function getExpandButton()
    {
        return $this->expandButton;
    }

    /**
     * @param object $expandButton Set MDiv instance which represents the expand/collapse button.
     */
    public function setExpandButton($expandButton)
    {
        $this->expandButton = $expandButton;
    }

    /**
     * @return boolean Get if the div must be expanded by default. 
     */
    public function getExpandedOnLoad()
    {
        return $this->expandedOnLoad;
    }

    /**
     * @param boolean $expandedOnLoad Set if the div must be expanded by default.
     */
    public function setExpandedOnLoad($expandedOnLoad)
    {
        $this->expandedOnLoad = $expandedOnLoad;
    }

    /**
     * @return string Generate the MExpandDiv.
     */
    public function generate()
    {
        if ( !$this->expandedOnLoad )
        {
            $this->box->addStyle('height', "{$this->collapsedHeight}px");
        }
        else
        {
            $this->expandButton->setClass(self::STYLE_EXPANDED);
        }

        $this->box->setInner($this->content);
        $this->expandButton->addEvent('click', "mexpanddiv.expand('{$this->box->id}', '{$this->collapsedHeight}');");

        return parent::generate();
    }
}

?>