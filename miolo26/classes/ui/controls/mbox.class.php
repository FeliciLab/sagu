<?php

class MBox extends MContainerControl
{
    /**
     * @var object MBoxTitle instance.
     */
    public $boxTitle;

    /**
     * MBox constructor.
     * Even if the close parameter is true, the button is only generated if the 
     * configuration at miolo.conf (or module.conf) is enabled (true).
     *
     * @param string $caption Title string.
     * @param string $close Close action.
     * @param string $icon Icon path.
     */
    public function __construct($caption=NULL, $close='', $icon='')
    {
        $boxId = 'box' . MControl::$_number++;
        parent::__construct($boxId);
        $this->boxTitle = $caption ? new MBoxTitle('', $caption, $close, $icon) : NULL;
        $this->setClass('mBox');
    }

    /**
     * @param string $close Set close action.
     */
    public function setClose($close)
    {
        if ( $this->boxTitle InstanceOf MBoxTitle )
        {
            $this->boxTitle->setClose($close);
        }
    }

    /**
     * @param string $caption Title string.
     */
    public function setCaption($caption)
    {
        if ( is_null($caption) )
        {
            $this->boxTitle = NULL;
        }
        elseif ( $this->boxTitle InstanceOf MBoxTitle )
        {
            $this->boxTitle->setCaption($caption);
        }
    }

    /**
     * Generate inner content.
     */
    public function generateInner()
    {
        $this->insertControl($this->boxTitle);
        $this->inner = new MDiv(NULL, $this->getControls(), 'mBoxInner');
    }
}

?>