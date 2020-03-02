<?php

class MImageButtonLabel extends MImageButton
{
    private $imageType = 'normal';


    public function setImageType( $type = 'normal' )
    {
        $this->imageType = $type;
    }

    public function generateLink()
    {
        parent::generateLink();
        if ( $this->imageType == 'normal' )
        {
            $this->setClass('mImageCentered');
            $text = new MSpan( '', $this->label, 'mImageLinkLabel mImageLabel' );
        }
        elseif ( $this->imageType == 'icon' )
        {
            $this->image->setClass( 'mImageIcon' );
            $text = new MSpan( '', $this->label, 'mImageLinkLabel' );
        }
        $this->caption = $this->image->generate() . $text->generate();
    }
}

?>