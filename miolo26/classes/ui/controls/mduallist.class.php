<?php

$MIOLO = MIOLO::getInstance();
$MIOLO->page->addScript('m_duallist.js');

/**
 * Dual list component.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2011/10/19
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class MDualList extends MSelection
{
    /**
     * Constants with the CSS styles used by the component.
     */
    const MAIN_STYLE = 'mSelection mDualList';
    const DIV_STYLE = 'mDualListDiv';
    const COUNTER_STYLE = 'mDualListCounter';

    /**
     * @var object MSelection instance which represents the left list.
     */
    private $leftList;

    /**
     * @var object MSelection instance which represents the right list.
     */
    private $rightList;

    /**
     * @var object MButton instance to add an element from the left list to the right.
     */
    private $addButton;

    /**
     * @var object MButton instance to remove the right list elements.
     */
    private $removeButton;

    /**
     * @var object MButton instance to move up the right list elements.
     */
    private $moveUpButton;

    /**
     * @var object MButton instance to move down the right list elements.
     */
    private $moveDownButton;

    /**
     * @var object MIOLO component instance to put the left list counter.
     */
    private $leftCounter;

    /**
     * @var object MIOLO component instance to put the right list counter.
     */
    private $rightCounter;

    /**
     * @var array Array containing all the options (from both left and right lists).
     */
    private $allOptions;

    /**
     * @var boolean Whether to show the element counters below the lists.
     */
    public $showCounter = false;

    /**
     * MDualList constructor.
     *
     * @param string $name Field id.
     * @param array $value Default value.
     * @param string $label Label.
     * @param array $options Options array. E.g. array('value' => 'Label').
     * @param integer $size Number of lines to display.
     */
    public function __construct($name='', $value=array(), $label='', $options=array(), $size=6)
    {
        $this->allOptions = $options;

        $leftListId = "{$name}_left";
        $rightListId = "{$name}_right";
        
        $this->leftList = new MSelection($leftListId, NULL, NULL, NULL);
        $this->leftList->options = $options;

        $this->rightList = new MSelection($rightListId, NULL, NULL, NULL);
        $this->rightList->options = array();

        // JavaScript actions for the buttons below
        $addAction = "mduallist.add('$leftListId', '$rightListId', '$name');";
        $removeAction = "mduallist.remove('$leftListId', '$rightListId', '$name');";
        $moveUpAction = "mduallist.moveUp('$rightListId', '$name');";
        $moveDownAction = "mduallist.moveDown('$rightListId', '$name');";

        // Buttons to move the elements between the lists
        $this->addButton = new MButton("{$name}_addButton", _M('Add'), $addAction);
        $this->removeButton = new MButton("{$name}_removeButton", _M('Remove'), $removeAction);

        // Buttons to move the elements inside the right list
        $this->moveUpButton = new MButton("{$name}_moveUpButton", _M('Move up'), $moveUpAction);
        $this->moveDownButton = new MButton("{$name}_moveDownButton", _M('Move down'), $moveDownAction);

        // Divs with the list counters
        $this->leftCounter = new MDiv("{$leftListId}_counter", NULL, self::COUNTER_STYLE);
        $this->rightCounter = new MDiv("{$rightListId}_counter", NULL, self::COUNTER_STYLE);

        // Add [] to the name to get the values as an array on REQUEST
        parent::__construct($name.'[]', NULL, $label, NULL);
        $this->id = $name;
        $this->options = array();

        $this->setValue($value);
        $this->setupListsStyle($size);

        // Select all options from the hidden list
        $this->page->onload("mduallist.selectAll(dojo.byId('$name'));");
    }

    /**
     * @return array Array containing the current values (all the options of the right list).
     */
    public function getValue()
    {
        $this->value = array();
        foreach ( $this->options as $value => $option )
        {
            if ( is_object($option) )
            {
                $this->value[] = $option->value;
            }
            else
            {
                $this->value[] = $value;
            }
        }
        return $this->value;
    }

    /**
     * Set the right list options according to the given array of values.
     *
     * @param array $value Array containing the values.
     */
    public function setValue($value)
    {
        if ( isset($value) )
        {
            $valueKeys = (array) $value;
            $valueOpts = array();

            $this->leftList->options = $this->allOptions;

            // Get the options based on the given values and remove them from the left list
            foreach ( $this->allOptions as $key => $option )
            {
                if ( in_array($key, $valueKeys) )
                {
                    $valueOpts[$key] = $option;
                    unset($this->leftList->options[$key]);
                }
            }

            $values = array();

            // Sort them according to the user values order
            foreach ( $valueKeys as $key )
            {
                $values[$key] = $valueOpts[$key];
            }

            $this->options = $values;
            $this->rightList->options = $values;

            // Update counters
            $this->leftCounter->setInner(count($this->leftList->options));
            $this->rightCounter->setInner(count($values));
        }
    }

    /**
     * Configure the style of the lists.
     *
     * @param integer $size Number of lines to display.
     */
    private function setupListsStyle($size)
    {
        $this->leftList->addAttribute('multiple');
        $this->leftList->addAttribute('size', $size);
        $this->leftList->setClass(self::MAIN_STYLE);

        $this->rightList->addAttribute('multiple');
        $this->rightList->addAttribute('size', $size);
        $this->rightList->setClass(self::MAIN_STYLE);

        $this->addAttribute('multiple');
    }

    /**
     * @return string Generated dual list.
     */
    public function generate()
    {
        // Left list
        $bottomDiv = new MDiv(NULL, $this->addButton->generate(), self::DIV_STYLE);

        if ( $this->showCounter )
        {
            $bottomDiv->addStyle('min-width', '190px');
            $bottomDiv->addStyle('float', 'left');

            $leftContent = $this->leftList->generate() . '<br/>' . $bottomDiv->generate() . $this->leftCounter->generate();
        }
        else
        {
            $leftContent = $this->leftList->generate() . '<br/>' . $bottomDiv->generate();
        }

        $leftListDiv = new MDiv(NULL, $leftContent, self::DIV_STYLE);
        $leftListDiv->addStyle('float', 'left');

        // Right list
        $bottomDiv = new MDiv(NULL, $this->removeButton->generate(), self::DIV_STYLE);

        if ( $this->showCounter )
        {
            $bottomDiv->addStyle('width', '190px');
            $bottomDiv->addStyle('float', 'left');

            $rightContent = $this->rightList->generate() . '<br/>' . $bottomDiv->generate() . $this->rightCounter->generate();
        }
        else
        {
            $rightContent = $this->rightList->generate() . '<br/>' . $bottomDiv->generate();
        }

        $rightListDiv = new MDiv(NULL, $rightContent, self::DIV_STYLE);
        $rightListDiv->addStyle('float', 'left');

        // Order buttons
        $orderContent = '<br/>' . $this->moveUpButton->generate() . '<br/>' . $this->moveDownButton->generate();
        $orderDiv = new MDiv(NULL, $orderContent, self::DIV_STYLE);
        $orderDiv->addStyle('float', 'left');

        // Hidden list which is always selected
        $div = new MDiv("{$this->id}_div", parent::generate());
        $div->addStyle('display', 'none');

        return $leftListDiv->generate() . $rightListDiv->generate() . $orderDiv->generate() . $div->generate();
    }
}

?>