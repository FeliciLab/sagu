<?php
class MJavascriptCode extends MControl
{
    public $code;

    public function __construct($code = '')
    {
        parent::__construct();
      	$this->code = new MStringList(false);
        $this->addCode($code);
    }

    public function addCode($code = '')
    {
        if ($code != '')
        {
            $this->code->add($code);
        }
    }

    public function generate()
    {
        return $this->code->getTextByTemplate("\n<script type=\"text/javascript\">\n <!--\n/:v/\n//-->\n</script>\n");
    }
}
?>