<?php

/**
 * Time component
 *
 * @author Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2010/07/26
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2010 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */

class MTimeField extends MTextField
{
    private $timePattern;
    private $clickableIncrement;
    private $visibleIncrement;
    private $visibleRange;
    private $invalidMessage;

    public function __construct($name='', $value='', $label='', $size=10, $hint='')
    {
        parent::__construct($name, $value, $label, $size, $hint);

        $this->page->addScript("dojoroot/miolo/MTimeTextBox.js");

        $this->setTimePattern('HH:mm:ss');
        $this->setClickableIncrement('00:15:00');
        $this->setVisibleIncrement('00:15:00');
        $this->setVisibleRange('01:00:00');
        $this->setInvalidMessage(_M('Invalid format. Use @1.', $this->timePattern));
        $this->addAttribute('dojoType', 'MTimeTextBox');
        $this->addAttribute('hasDownArrow', 'false');

        $this->addEvent('change', "miolo.getElementById('{$name}').value = miolo.getElementById('{$name}').value.replace('T', '');");
    }

    public function setValue($value)
    {
        if ( strlen($value) == 0 )
        {
            $this->value = '';
        }
        elseif ( $value[0] == 'T' )
        {
            $this->value = $value;
        }
        else
        {
            $this->value = 'T' . $value;
        }
    }

    public function getParsedValue()
    {
        return substr($this->value, 1);
    }

    public function getTimePattern()
    {
        return $this->timePattern;
    }

    public function setTimePattern($pattern)
    {
        $this->timePattern = $pattern;
        $this->setInvalidMessage(_M('Invalid format. Use @1.', 'miolo', $this->timePattern));
    }

    public function getClickableIncrement()
    {
        return $this->clickableIncrement;
    }

    public function setClickableIncrement($clickableIncrement)
    {
        $this->clickableIncrement = $clickableIncrement;
    }

    public function getInvalidMessage()
    {
        return $this->invalidMessage;
    }

    public function setInvalidMessage($invalidMessage)
    {
        $this->invalidMessage = $invalidMessage;
    }

    public function getVisibleIncrement()
    {
        return $this->visibleIncrement;
    }

    public function setVisibleIncrement($visibleIncrement)
    {
        $this->visibleIncrement = $visibleIncrement;
    }

    public function getVisibleRange()
    {
        return $this->visibleRange;
    }

    public function setVisibleRange($visibleRange)
    {
        $this->visibleRange = $visibleRange;
    }

    public function generateInner()
    {
        $this->addAttribute('constraints', "{timePattern:'{$this->timePattern}', clickableIncrement:'T{$this->clickableIncrement}', visibleIncrement:'T{$this->visibleIncrement}', visibleRange:'T{$this->visibleRange}'}");
        $this->addAttribute('invalidMessage', $this->invalidMessage);

        parent::generateInner();

        if ( ! $this->readonly )
        {
            $text = $this->getRender('inputtext');
            $this->inner = $this->generateLabel() . $text;
        }
    }
}
?>
