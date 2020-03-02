<?php
class MThemeBox extends MContainerControl
{
    public $title;
    public $content;

    public function __construct($title, $content = '')
    {
        parent::__construct();
        $this->title = $title;
        $this->content = $content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function generateInner()
    {
        $t[] = new MSpan('', $this->title, 'title');
        $t[] = new MDiv('', $this->content, 'content');
        $this->inner = $t;
        $this->setClass('mThemeBox');
    }
}

?>