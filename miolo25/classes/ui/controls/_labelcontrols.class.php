<?php

class MBaseLabel extends MFormControl
{
    public function __construct( $name = NULL, $value = NULL, $label = NULL, $color = '', $hint = '', $bold = false )
    {
        parent::__construct( $name, $value, $label, $color, $hint );
        $bold ? $this->setBold(true):$bold;
    }


    public function setBold( $value = true )
    {
        $this->fontWeight = $value ? 'bold' : 'normal';
    }
}


class MPageComment extends MBaseLabel
{
    public function __construct( $text = NULL )
    {
        parent::__construct( NULL, $text );
    }


    public function generateInner()
    {
        $this->inner = $this->getRender( 'comment' );
    }


    public function generate()
    {
        $this->generateInner();

        return $this->getInner();
    }
}


class MSeparator extends MBaseLabel
{
    private $margin;
    private $lineHeight;

    public function __construct( $text = NULL, $margin = '', $color = '', $lineHeight = '1px' )
    {
        parent::__construct( NULL, $text, '', $color );

        $this->margin = $margin;
        $this->lineHeight = $lineHeight;
    }


    public function generateInner()
    {
        $color = MUtil::NVL( $this->color, "#999" );
        $this->setBoxClass( 'm-separator' );
        $this->addBoxStyle( 'margin-top', "{$this->margin}" );

        if( $this->lineHeight )
        {
            $this->addBoxStyle( 'border-bottom', $this->lineHeight." solid {$this->color}" );
        }

        if ( trim($this->value) != '' )
        {
            $this->addBoxStyle( 'line-height', "15px" );
            $this->inner = $this->value;
        }
        else
        {
            $this->addBoxStyle( 'line-height', "0px" );
            $this->inner = '&nbsp;';
        }
    }
}


class MLabel extends MBaseLabel
{
    public function __construct( $text = NULL, $color = '', $bold = false )
    {
        parent::__construct( NULL, $text, NULL, $color, NULL, $bold );
    }


    public function generateInner()
    {
        if ( $this->getClass() == '' )
        {
            $this->setClass( 'm-label' );
        }

//        $span = new MSpan( '', $this->value, $this->getClass() );

//        $this->inner = ( trim($this->value) != '' ) ? $span->getRender('text') : '';
        $this->inner = ( trim($this->value) != '' ) ? $this->getRender( 'text' ) : '';

    }
}

class MRawText extends MLabel
{
    public function generateInner()
    {
        $this->inner = trim($this->value);
    }
}

class MFieldLabel extends MBaseLabel
{
    public function __construct( $id, $text = NULL )
    {
        parent::__construct( NULL, $text );

        $this->setId( $id );
    }


    public function generateInner()
    {
        if ( $this->getClass() == '' )
        {
            $this->setClass( 'm-label' );
        }

        $this->inner = ( trim($this->value) != '' ) ? $this->getRender('label') : '';
    }
}


class MTextHeader extends MBaseLabel
{
    public $level;

    public function __construct( $name = '', $level = '1', $text = NULL, $color = '' )
    {
        parent::__construct( $name, $text, '', $color );

        $this->level = $level;
    }

    public function generateInner()
    {
        $this->inner = $this->getRender( 'header' );
    }
}


class MText extends MBaseLabel
{
    public function __construct( $name = '', $text = NULL, $color = '', $bold = false )
    {
        parent::__construct( $name, $text, '', $color, NULL, $bold );

        $this->formMode = 1;
    }


    public function generateInner()
    {
        if ( $this->getClass() == '' )
        {
            $this->setClass( 'm-text' );
        }

        $this->inner = $this->getRender( 'text' );
    }
}


class MTextLabel extends MText
{
    public function __construct( $name = '', $text = null, $label = '', $color = '', $bold = false )
    {
        parent::__construct( $name, $text, $color, $bold );

        $this->label = $label;
        $this->setClass( 'm-label-text' );
//        $this->formMode = 1;
    }

    public function generateInner()
    {
        if ( $this->getClass() == '' )
        {
            $this->setClass( 'm-text' );
        }

        $this->inner =  $this->generateLabel() . $this->getRender( 'text' );
    }
}

class MGDText extends MText
{
    public $ttfFont;
    public $key;
    public $location;
    public $size;
    public $foreColor;
    public $backColor;

    public function __construct( $name = '', $text = 'default', $size = 0, $fColor = array(0,0,0), $bColor = array(255,255,255), $ttfFont = 'arial', $bold = false )
    {
        parent::__construct( $name, $text, '', $color, NULL, $bold );
        $this->ttfFont = $ttfFont;
        $this->size = $size;
        $this->foreColor = $fColor;
        $this->backColor = $bColor;
        $this->manager->conf->loadConf('',$this->manager->getConf('home.classes') .'/etc/gdtext.xml');
        $this->key = $this->manager->getConf("gdtext.key");
    }

    public function generateInner()
    {
        $qs = 'key='.$this->key;
        $qs .= '&text='.$this->value;
        $qs .= '&size='.$this->size;
        $qs .= '&fcolor='.implode(',',$this->foreColor);
        $qs .= '&bcolor='.implode(',',$this->backColor);
        $qs .= '&font='.$this->ttfFont;
        $qs = 'qs='.base64_encode($qs);
        $a = $this->location = $this->manager->getConf('home.url').'/gdtext.php?'.$qs;
        $this->inner = $this->getRender( 'image' );
    }

}


?>