<?php
class MContentHeader extends MControl
{
    public $object;
    public $target;
    public function __construct($object, $target)
    {
        parent::__construct();
        $this->object = $object;
        $this->target = $target;
    }

    public function generate()
    {
        $div = new MDiv('', $this->object . ': ' . $this->target, 'mContentHeader');
        return $div->generate();
    }
}
?>