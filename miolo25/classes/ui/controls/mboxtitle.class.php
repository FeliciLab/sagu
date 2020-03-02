<?php

class MBoxTitle extends MDiv
{
    /**
     * @var string Icon path.
     */
    protected $icon;

    /**
     * @var string Close action.
     */
    protected $close;

    /**
     * MBoxTitle constructor.
     *
     * @param string $cssClass Style class.
     * @param string $caption Title string.
     * @param string $close Close action.
     * @param string $icon Icon path.
     */
    public function __construct($cssClass, $caption, $close='', $icon='')
    {
        parent::__construct('', '', $cssClass);

        $this->close = $close;
        $this->icon = $icon;
        $this->caption = $caption;
    }

    /**
     * @param string $icon Icon path.
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @param string $action Close action.
     */
    public function setClose($action)
    {
        $this->close = $action;
    }

    /**
     * Generate inner content.
     */
    public function generateInner()
    {
        $this->page->onload("miolo.box.setBoxPositions();");
        $title = $this->caption;

        if ( $this->icon == '' )
        {
            $title = '&nbsp;&nbsp;' . $title;
            $icon = NULL;
        }
        else
        {
            $icon = new MImage('', '', $this->icon);
            $icon->setClass('mBoxTitleIcon');
        }

        $caption = new MSpan('', $title, 'mBoxTitleCaption');
        $close = '';

        if ( $this->close != '' &&
                MUtil::getBooleanValue($this->manager->getConf('theme.options.close')) )
        {
            $close = new MButtonClose($this->close);
            $close->setClass('mBoxTitleButton');
        }

        $spacer = new MSpacer();

        if ( $this->getClass() == '' )
        {
            $this->setClass('mBoxTitle');
        }

        $this->inner = array( $icon, $caption, $close );
    }
}

?>