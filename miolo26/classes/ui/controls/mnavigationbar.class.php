<?php
class MNavigationBar extends MMenu
{
    public $labelHome = 'Home';
    private $separator = '&raquo;&raquo;';

    public function setLabelHome($label)
    {
        $this->labelHome = $label;
    }

    public function generateInner()
    {
        if ($this->base)
        {
            $base = $this->base;
        }
        else
        {
            $base = $this->manager->dispatch;
        }

        $this->setCssClassItem('link', 'mTopMenuLink');
        $this->setCssClassItem('option', 'mTopMenuLink');

        $ul = new MUnorderedList();
        $options = $this->getOptions();

        if ($count = count($options))
        {
            if ( $this->labelHome )
            {
                $url = $this->manager->getActionURL($this->home,'main','','',$base);
                $link = new MLink('', $this->labelHome, $url);
                $link->setClass('mTopMenuLink');
                $ul->addOption($link->generate());
                $ul->addOption($this->separator);
            }

            foreach ($options as $o)
            {
                if (--$count)
                {
                    $ul->addOption($o->generate());
                    $ul->addOption($this->separator);
                }
                else
                {
                    $span = new MSpan('', $o->control->label, 'mTopMenuCurrent');
                    $ul->addOption($span->generate());
                }
            }
        }
        else // root item
        {
            $ul->addOption($this->caption);
        }

//        $this->setClass('mTopmenuBox');
        $box = new MDiv('',$ul,'mTopMenuBox',"dojoType=\"dojox.layout.ContentPane\"");
        $this->inner = $box;
    }

    public function getSeparator()
    {
        return $this->separator;
    }

    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }
}
?>
