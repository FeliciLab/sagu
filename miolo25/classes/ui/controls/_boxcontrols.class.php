<?php

/**
 *
 */
class MBoxTitle extends MDiv
{
    protected $icon;
    protected $close;
    protected $help;
    protected $minimize;
    protected $move;
    protected $MIOLO;
    protected $boxId;

    /**
     *
     */
    public function __construct( $cssClass, $caption, $close = '', $icon = '', $help = NULL, $showHelp=true, $showMinimize=true, $showClose=true, $boxId = '' )
    {
        $this->MIOLO = MIOLO::getInstance();

        parent::__construct( '' , $cssClass );
        $this->boxId = $boxId;

        $this->close = $close;

        if ( MUtil::getBooleanValue($showHelp) && 
             MUtil::getBooleanValue( $this->MIOLO->getConf('theme.options.help') )
            )
        {
            $this->help  = $help;
        }
        else
        {
            $this->help  = '';
        }

        if ( MUtil::getBooleanValue($showMinimize) && 
             MUtil::getBooleanValue( $this->MIOLO->getConf('theme.options.minimize') )
            )
        {
            $this->minimize = true;
        }
        else
        {
            $this->minimize = false;
        }

        $this->move = MUtil::getBooleanValue( $this->MIOLO->getConf('theme.options.move') );

        $this->icon  = $icon;
        $this->setCaption( $caption );
    }

    /**
     *
     */
    public function setIcon( $icon )
    {
        $this->icon = $icon;
    }

    /**
     *
     */
    public function setClose( $action )
    {
        $this->close = $action;
    }

    /**
     *
     */
    public function setHelp( $help, $module=null, $action=null )
    {
        $this->help = $help;
    }

    /**
     *
     */
    public function generateInner()
    {
        //$title = str_replace( ' ', '&nbsp;', $this->caption ) . "&nbsp;&nbsp;";
//        $this->page->addScript('m_box.js');
        $this->page->onload("miolo.box.setBoxPositions();");
        $title = $this->caption;

        if ( $this->icon == '' )
        {
            $title = '&nbsp;&nbsp;' . $title;
            $icon  = NULL;
        }
        else
        {
            $icon = new MSpan( '', new MImage('', '', $this->icon ), 'icon' );
        }

        $caption = new MSpan( '', $title, 'caption' );
        $help = $close = $minimize = '';


        if ( $this->help != '' &&
             MUtil::getBooleanValue( $this->MIOLO->getConf('theme.options.help') ) )
        {
                $help = new MSpan( '', new MButtonHelp( $this->help ), 'button' );
        }

        if ( $this->close != '' &&
             MUtil::getBooleanValue( $this->MIOLO->getConf('theme.options.close') ) )
        {
            if ( strpos( $this->close, 'javascript') === false)
            {
                if ($this->minimize )
                {
                    $button = new MButtonMinimize( '' );
                    $button->onMouseUp($this->boxId);
                    $minimize = new MSpan( '', $button, 'button' );
                }
            }

            $close    = new MSpan( '', new MButtonClose( $this->close ), 'button' );
        }
        elseif ($this->minimize )
        {
            $button = new MButtonMinimize( '' );
            $button->onMouseUp($this->boxId);
            $minimize = new MSpan( '', $button , 'button' );
        }

        $spacer = new MSpacer();

        if ( $this->getBoxClass() == '' )
        {
            $this->setBoxClass( 'm-box-title' );
            $box = $this->getBox( );

            if ( $this->move )
            {
                $box->addAttribute('title', _M('Double click to hide') . " | " . _M('Click and drag to move') . " | " . _M('Right click to minimize') );
                $box->addAttribute('onMouseDown', 'return miolo.box.moveBox(event, this.parentNode.parentNode, true )');
                $box->addAttribute('onMouseUp'  , 'miolo.box.moveBox(event, this.parentNode.parentNode, false)');
                $box->addAttribute('onDblClick' , 'miolo.box.hideBoxContent(this.parentNode.parentNode)');
            }
        }

        $this->inner = array( $icon, $caption, $close, $minimize, $help );
    }
}


/**
 *
 */
class MBox extends MDiv
{
    public $boxTitle;
    protected $boxInner;

    /**
     * Some controls, like message boxes, don't require some buttons, the parameters are used to identify wich should or not be created.
     * Even if the parameter is true, the button is only generated if the configuration in miolo.conf (or module.conf) is enabled (true)
     *
     * @param $showHelp     (boolean) true if the help button should be created
     * @param $showMinimize (boolean) true if the minimize button should be created
     * @param $showClose    (boolean) true if the help button should be created
     */

    public function __construct( $caption = NULL, $close = '', $icon = '', $help = '', $showHelp=true, $showMinimize=true, $showClose=true )
    {
        $boxId = 'm' . MControl::$_number++;
        parent::__construct($boxId);

        if ( is_null( $caption ) )
        {
            $this->boxTitle = NULL;
        }
        else
        {
            $this->boxTitle = new MBoxTitle( 'boxTitle', $caption, $close, $icon, $help, $showHelp, $showMinimize, $showClose, $boxId );
        }
        $this->setBoxClass('m-box-box');
    }

    /**
     *
     */
    public function setClose( $close )
    {
        if ( $this->boxTitle InstanceOf MBoxTitle )
        {
            $this->boxTitle->setClose( $close );
        }
    }

    /**
     *
     */
    public function setHelp( $help )
    {
        if ( $this->boxTitle InstanceOf MBoxTitle )
        {
            $this->boxTitle->setHelp( $help );
        }
    }

    /**
     *
     */
    public function setCaption( $caption )
    {
        if ( is_null( $caption ) )
        {
            $this->boxTitle = NULL;
        }
        elseif ( $this->boxTitle InstanceOf MBoxTitle )
        {
            $this->boxTitle->setCaption( $caption );
        }
    }

    /**
     *
     */
    public function generateInner()
    {
        $this->insertControl( $this->boxTitle );
        $class = $this->getBoxClass();

        $attributes = $this->getAttributes();

        $this->inner = new MDiv( NULL, $this->getControls(), 'm-box-box' );
        $this->setBoxId($this->getId());
        $this->setBoxClass( str_replace( 'm-box-box', 'm-box-outer', $class ), false );
        $this->setBoxAttributes( $attributes );
    }
}
?>
