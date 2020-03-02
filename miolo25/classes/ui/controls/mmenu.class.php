<?php
class MMenu extends MOptionList
{
    public $home;
    public $base;

    public function __construct($name = '', $base = '', $home = null)
    {
        parent::__construct($name);
        $this->caption = $name;
        $this->base = $base;

        if ( $home == null )
        {
            $this->home = $this->manager->getConf('options.common');
        }
        else
        {
            $this->home = $home;
        }
    }

    public function getTitle()
    {
        return $this->caption;
    }

    public function setTitle($title)
    {
        $this->caption = $title;
    }

    public function getHome()
    {
        return $this->home;
    }

    public function setHome($home)
    {
        $this->home = $home;
    }

    public function getBase()
    {
        return $this->base;
    }

    public function setBase($base)
    {
        $this->base = $base;
    }

    public function generateInner()
    {
        if ($this->hasOptions())
        {
            $ul = new MUnorderedList();

            if ($title = $this->getTitle())
                $ul->addOption(new MDiv('', $title, 'mMenuTitle'));

            $options = $this->generateUnOrderedList();
            $ul->options = array_merge($ul->options, $options->options);
            $this->inner = $ul->generate();
            $this->setClass('mMenuBox');
        }
    }
}

?>