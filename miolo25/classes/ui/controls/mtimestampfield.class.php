<?php

/**
 * Timestamp field with date and time components.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
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
 * \b Copyright: \n
 * Copyright (c) 2010-2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */

class MTimestampField extends MTextField
{
    /**
     * @var object MCalendarField instance representing the date part of the component.
     */
    private $dateField;

    /**
     * @var object MTimeField instance representing the time part of the component.
     */
    private $timeField;

    /**
     *
     * @param string $name Name of the main field.
     * @param string $value Date and time value. Format: DD/MM/YYYY HH:MM
     * @param string $label Label of the main field.
     * @param boolean $readonly Whether the component must be rendered as read only.
     */
    public function __construct($name='', $value='', $label='', $readonly=false)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('ui/controls/mtimefield.class.php');

        if ( $value != '' )
        {
            list($date, $time) = explode(' ', $value);
        }

        $this->dateField = new MCalendarField($name . 'Date', $date, NULL);
        $this->timeField = new MTimeField($name . 'Time', $time);

        $refreshEvent  = "miolo.getElementById('{$name}').value = miolo.getElementById('{$name}Date').value + ' ' + miolo.getElementById('{$name}Time').value.replace('T', '');";
        $refreshEvent .= "miolo.getElementById('{$name}').value = miolo.getElementById('{$name}').value.trim();";
        $refreshEvent .= "miolo.getElementById('{$name}Time').value = miolo.getElementById('{$name}Time').value.replace('T', '');";

        $this->dateField->addAttribute('onchange', $refreshEvent);
        $this->timeField->addAttribute('onchange', $refreshEvent);

        parent::__construct($name, $value, $label);
        $this->setReadOnly($readonly);
        $this->addStyle('display', 'none');
    }

    /**
     * @return object Get the MCalendarField instance.
     */
    public function getDateField()
    {
        return $this->dateField;
    }

    /**
     * @param object $dateField Set the MCalendarField instance.
     */
    public function setDateField($dateField)
    {
        $this->dateField = $dateField;
    }

    /**
     * @return object Get the MTimeField instance.
     */
    public function getTimeField()
    {
        return $this->timeField;
    }

    /**
     * @param object $timeField Set the MTimeField instance.
     */
    public function setTimeField($timeField)
    {
        $this->timeField = $timeField;
    }

    /**
     * @param string $value Set field value. Format: DD/MM/YYYY HH:MM
     */
    public function setValue($value)
    {
        list($date, $time) = explode(' ', $value);
        $this->dateField->setValue($date);
        $this->timeField->setValue($time);
        $this->value = $value;

        if ( MUtil::isAjaxEvent() )
        {
            $this->page->onload("setTimeout(function () { dijit.byId('{$this->dateField->name}').setJsValue('$date'); }, 0);");
        }
        else
        {
            $this->dateField->setJsValue($date);
        }
    }

    /**
     * @param boolean $readonly Set read only.
     */
    public function setReadOnly($readonly)
    {
        $this->dateField->setReadOnly($readonly);
        $this->timeField->setReadOnly($readonly);
        $this->readonly = $readonly;
    }

    /**
     * Connect a Javascript/AJAX action to an event.
     *
     * @param string $event Input event that will call the action.
     * @param string $action Action that will be called.
     */
    public function addEvent($event, $action)
    {
        $this->dateField->addEvent($event, $action);
        $this->timeField->addEvent($event, $action);
    }

    /**
     * Generate inner content.
     */
    public function generateInner()
    {
        parent::generateInner();
        $this->dateField->generateInner();
        $this->timeField->generateInner();
        $this->timeField->generateEvent();
        $this->inner = $this->dateField . $this->timeField->inner . $this->inner;
    }
}
?>
