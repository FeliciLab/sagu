<?php
class MFileContent extends MContent
{
    public $isSource;

    public function __construct($filename = null, $isSource = false, $home = false)
    {
        parent::__construct();
        $this->path = $filename;
        $this->isSource = $isSource;
    }

    public function setFile($filename)
    {
        $this->path = $filename;
    }

    public function generateInner()
    {
        if ($this->isSource)
        {
            $content = highlight_file($this->path, true);
            $t[] = new MDiv('', $this->path, '');
            $t[] = new MDiv('', $content, 'mFileContent');
            $this->inner = $t;
        }
        else
        {
            parent::generateInner();
        }
    }
}

?>