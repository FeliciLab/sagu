<?php

class MHiddenField extends MTextField
{
    public function generate()
    {
        return $this->getRender('inputhidden');
    }
}

?>