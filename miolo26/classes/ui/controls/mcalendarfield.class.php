<?php

class MCalendarField extends MTextField
{
    public function __construct( $name='', $value='', $label='', $size=20, $hint='', $type='text', $class='mTextField' )
    {
        parent::__construct( $name, $value, $label, $size, $hint);

        $this->type = $type;
        $this->setClass($class);

        $this->page->addScript("dojoroot/miolo/MDateTextBox.js");

        $this->addAttribute('dojoType', 'MDateTextBox');
        $this->addAttribute('promptMessage',"dd/mm/yyyy");
        // this attribute is valid only to "show" the date (it doesn't define the internal format)
        $this->addAttribute('constraints',"{datePattern:'dd/MM/yyyy'}");

        // Hide down arrow button
        $this->addAttribute('hasDownArrow', 'false');

        $this->page->onload("dojo.parser.parse(dojo.byId('{$name}'));");

        $this->setValidator( new MDATEDMYValidator( $name, $label ) );
    }


    public function generateInner()
    {
        // dijit.form.DateTextBox has a standard date format (ansi) : yyyy-mm-dd
        // convert Miolo internal date format (dd/mm/yyyy) to Dojo date format (yyyy-mm-dd)
        $k = new MKrono();
        if ($this->value != '')
        {
            $this->setValue($k->KDate('%Y-%m-%d', $k->dateToTimestamp($this->value)));
        }

        parent::generateInner();

        if ( ! $this->readonly )
        {
            $text = $this->getRender('inputtext');
            $this->inner = $this->generateLabel() . $text;
        }

    }

    /**
     * Call javascript function to set the displayed value.
     *
     * @param string $value Date in the format defined by MDateTextBox JS class's 
     *                       datePattern attribute. Default is dd/mm/yyyy.
     */
    public function setJsValue($value)
    {
        $this->page->onload("if (dijit.byId('$this->name')) { dijit.byId('$this->name').setJsValue('$value'); }");
    }
}

?>
