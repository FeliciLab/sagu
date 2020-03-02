<?php
class MCurrencyField extends MTextField
{
    private $ISOCode = 'REAL';

    public function __construct( $name='', $value='', $label='', $size=10, $hint='' )
    {
        // este campo vai usar validacao/formatacao javascript 
        parent::__construct( $name, $value, $label, $size, $hint );

        $page = $this->page;

        $page->addScript( 'm_utils.js' );
        $page->addScript( 'm_currency.js' );
    }


    public function getValue()
    {
        // internal ($this->value): float - 12345.67
        // external (return): formated string - R$ 12.345,67
        if (strpos($this->value, "%") === false)
        {
            $format = new MCurrencyFormatter();
            $value = $format->formatWithSymbol( $this->value, $this->ISOCode );
        }
        else
        {
            $value = $this->value;
        }
        return $value;
    }


    public function setValue( $value )
    {
        // internal ($this->value): float - 12345.67
        // external ($value): formated string - 12.345,67

        if (strpos($value, "%") === false)
        {
            $format = new MCurrencyFormatter();

            $value = $format->toDecimal($value, $this->ISOCode);
            $this->value = (float)$value;
        }
        else
        {
            $this->value = $value;
        }
    }


    public function generateInner()
    {
        $this->addEvent('blur',"miolo.currency('{$this->name}');");
        parent::generateInner();
    }
}

?>