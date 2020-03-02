<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class AOption extends MOption
{
    public $descriptive;
    public $descriptiveValue;
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $name' (tipo) desc
     * @param $value= (tipo) desc
     * @param $label='' (tipo) desc
     * @param $checked= (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function __construct($name = '', $value = null, $label = '', $checked = false, $descriptive = false, $valorDescritivo = '')
    {
        $this->descriptive = $descriptive;
        $this->descriptiveValue = $valorDescritivo;
        parent::__construct($name, $value, $label, $checked);
    }
 }
?>