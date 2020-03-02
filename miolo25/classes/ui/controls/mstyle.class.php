<?php
class MStyle
{
    /** 
     * CSS selector.
     */
    public $cssClass;

    /** 
     * A list with style attributes.
     */
    public $style;

    /** 
     * Is the control absolutely positioned?
     */
    public $cssp;

    public function __construct()
    {
        $this->cssClass  = '';
        $this->cssp      = false;
        $this->style     = new MStringList();
    }

    public function __set( $name, $value )
    {
        switch ( $name )
            {
            case 'color':
            case 'font':
            case 'border':
            case 'cursor':
                $this->addStyle( $name, $value );

                break;

            case 'padding':
            case 'margin':
            case 'width':
            case 'height':
            case 'float':
            case 'clear':
            case 'visibility':
                $this->addStyle( $name, $value );
                $this->addStyle( 'display', 'block' );

                break;

            case 'top':
            case 'left':
            case 'position':
                $this->addStyle( $name, $value );
                $this->cssp = true;

                break;

            case 'fontSize':
                $this->addStyle( 'font-size', $value );

                break;

            case 'fontStyle':
                $this->addStyle( 'font-style', $value );

                break;

            case 'fontFamily':
                $this->addStyle( 'font-family', $value );

                break;

            case 'fontWeight':
                $this->addStyle( 'font-weight', $value );

                break;

            case 'textAlign':
                $this->addStyle  ( 'text-align', $value );

                break;

            case 'textIndent':
                $this->addStyle  ( 'text-indent', $value );

                break;

            case 'lineHeight':
                $this->addStyle( 'line-height', $value );

                break;

            case 'zIndex':
                $this->addStyle( 'z-index', $value );

                break;

            case 'backgroundColor':
                $this->addStyle( 'background-color', $value );

                break;

            case 'verticalAlign':
                $this->addStyle( 'vertical-align', $value );

                break;

            default:
//                $this->addStyle( $name, $value );
$MIOLO = MIOLO::getInstance(); $MIOLO->tracestack(); mdump($name . ' - ' . $value);

                break;
            }
    }


    public function __get( $name )
    {
        switch ( $name )
            {
            case 'top':
            case 'left':
            case 'width':
            case 'height':
            case 'padding':
            case 'float':
            case 'position':
                return $this->style->get( $name );
                break;
            }
    }


    /** 
     * The setter method.
     */

    public function set($name, $value)
    {
        if ( $value != '' )
        {
            $this->style->addValue($name, $value);
        }
    }

    public function get($name)
    {
        return ( $name != '' ) ? $this->style->get($name) : '';
    }

    public function addStyle($name, $value)
    {
        if ( $value != '' )
        {
            $this->style->addValue($name, $value);
        }
    }

    public function setClass( $cssClass, $add = true )
    {
        if ( $add )
        {
            $this->cssClass .= MUtil::ifNull($this->cssClass, '', ' ') . $cssClass;
        }
        else
        {
            $this->cssClass = $cssClass;
        }
    }

    public function insertClass( $cssClass )
    {
        $this->cssClass = $cssClass . MUtil::ifNull($this->cssClass, '', ' '.$this->cssClass);
    }

    public function addStyleFile( $styleFile )
    {
        $this->page->addStyle($styleFile);
    }

    public function getClass()
    {
        return $this->cssClass;
    }

    /* TODO: tokenizer */
    public function setStyle($style)
    {
        $this->style->items = $style;
    }

    public function getStyle()
    {
        return $this->style->hasItems() ? ' style="' . $this->style->getText(':', ';') . '"' : '';
    }

    public function setPosition($left, $top, $position = 'absolute')
    {
        $this->addStyle('position', $position);
        $this->addStyle('left', "{$left}px");
        $this->addStyle('top', "{$top}px");
    }

    public function setWidth($value)
    {
        if ( ! $value )
        {
            return;
        }

        if ( strpos($value, '%') === false )
        {
            $v = "{$value}px";
        }
        else
        {
            $v = $value;
        }
        $this->addStyle( 'display', 'block' );
        $this->addStyle('width', $v);
    }

    public function setHeight($value)
    {
        if ( ! $value )
        {
            return;
        }

        if ( strpos($value, '%') === false )
        {
            $v = "{$value}px";
        }
        else
        {
            $v = $value;
        }
        $this->addStyle( 'display', 'block' );
        $this->addStyle('height', $v);
    }

    public function setColor($value)
    {
        $this->addStyle('color', $value);
    }

    public function setVisibility($value)
    {
        $value = ($value ? 'visible' : 'hidden');
        $this->addStyle('visibility', $value);
    }

    public function setFont($value)
    {
        $this->addStyle('font', $value);
    }
}
?> 