<?php

class MCalendarMobileField extends MTextField
{
    public function __construct( $name='', $value='', $label='', $size=20, $hint='', $type='text', $class='mTextField' )
    {
        parent::__construct( $name, $value, $label, $size, $hint);

        $this->type = $type;
        $this->setClass($class);

        $this->page->addScript("datepicker/datepickr.js");
        $this->page->onload("
        new datepickr('{$name}', {
            fullCurrentMonth: true,
            dateFormat: 'd/m/Y',
            weekdays: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
            months: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro']
        });");

        $this->addAttribute('onKeyPress', 'MascaraData(this)');
        $this->addAttribute('onBlur', 'ValidaData(this)');
        $this->addAttribute('maxlength', '10');
        
        $this->setValidator( new MDATEDMYValidator( $name, $label ) );
    }


    public function generateInner()
    {
        
        
        /**
         * Retirada validação abaixo. Recebia o valor já formatado em dd/mm/yyyy
         * e acabava convertendo para um formato errado. - ticket #37578
         */
        
        // dijit.form.DateTextBox has a standard date format (ansi) : yyyy-mm-dd
        // convert Miolo internal date format (dd/mm/yyyy) to Dojo date format (yyyy-mm-dd)
//        $k = new MKrono();
//        if ($this->value != '')
//        {
//            $this->setValue($k->KDate('%Y-%m-%d', $k->dateToTimestamp($this->value)));
//        }

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
        $this->page->onload("dijit.byId('$this->name').setJsValue('$value');");
    }
}

?>
