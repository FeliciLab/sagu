<?php

class MRawText extends MLabel
{
    public function generateInner()
    {
        $this->inner = trim($this->value);
    }
}

?>