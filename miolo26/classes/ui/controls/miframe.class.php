<?php
class MIFrame extends MControl
{
    public $src;

    public function __construct($name, $src)
    {
        parent::__construct($name);
        $this->src = $src;
    }

    public function setSource($src)
    {
        $this->src = $src;
    }

    public function generateInner()
    {
        $this->inner = $this->getRender('iframe');
    }
}
?>