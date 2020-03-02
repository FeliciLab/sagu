<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class ADescriptiveOption extends MOption
{
    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function generate()
    {
        if (is_array($this->control->value))
        {
            $found = array_search($this->value,$this->control->value);
            $checked = (!is_null($found)) && ($found !== false);
        }
        else
        {
            $checked = ($this->value == $this->control->value);
        }
        $this->checked = $this->checked || $checked;
        $html = HtmlPainter::option($this->value, $this->label, $this->checked, $this->control->showValues);
        $text = new MTextField($this->name);
        $html .= "\n".$text->generate();
        return $html; 
    }
}
?>