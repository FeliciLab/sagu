<?php

class MBaseGroup extends MContainer
{
    public $borderType;
    public $scrollable;
    public $scrollHeight;

    public function __construct( $name = '', $caption = '', $controls = '', $disposition = 'none', $border = 'css', $formMode = MFormControl::FORM_MODE_SHOW_ABOVE )
    {
        parent::__construct( $name, $controls, $disposition );

        $this->scrollable   = false;
        $this->scrollHeight = '';
        $this->borderType   = $border;
        $this->caption      = $caption;
        $this->formMode     = $formMode;
    }


    public function setScrollHeight( $height )
    {
        $this->scrollable   = true;
        $this->scrollHeight = $height;
    }


    public function setBorder( $border )
    {
        $this->borderType = $border;
    }


    public function generateInner()
    {
        switch ( $this->borderType )
        {
            case 'none':
            case '':
                $this->border = '0';

                break;

            case 'css': break;

            default: $this->addStyle('border', $this->border);
        }

        $attrs = $this->getAttributes();

        parent::generateInner();

    }

    public function generate()
    { 
        $this->generateInner();   
        if ( $this->scrollable )
        {
            $f[]  = new MDiv( '', $this->caption, 'mScrollableLabel' );
            $html = $this->getInnerToString();
//            $this->setClass('field');
            $f[]  = $div = new MDiv( '', $html, 'mScrollableField' );
            $div->height = $this->scrollHeight;
        }
        else
        {
//            $this->width = MUtil::NVL( $this->width, '98%' );
            $class = $this->getClass();
            $this->setClass('mBaseGroup', false);
            $f = $this->getRender( 'fieldset' );
        }
        $outer = new MDiv( '', $f, $class );
        return $outer->generate();
    }
}

?>