<?php
class MContent extends MControl
{
    public $path;
    public $home;

    public function __construct($module = false, $name = false, $home = false)
    {
        parent::__construct();
        $this->path = $this->manager->getModulePath($module, $name);
        $this->home = $home;
    }

    public function generateInner()
    {
        $content_array = file($this->path);
        $content = implode("", $content_array);
        $this->inner = new MDiv('', $content, 'mThemeContent');
    }
}

?>