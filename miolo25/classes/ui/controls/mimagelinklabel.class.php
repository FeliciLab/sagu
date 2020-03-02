<?php

class MImageLinkLabel extends MImageLink
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
            $this->image->generateInner();
            $image = new MDiv( '', $this->image->getInner(), 'mImageCentered' );
            $text = new MSpan( '', $this->label, 'mImageLinkLabel mImageLabel' );
            $this->caption = $image->generate() . $text->generate();
        }
        elseif ( $this->imageType == 'icon' )
        {
            $this->image->setClass( 'mImageIcon' );
            $this->image->generateInner();
            //          $image = new Span('', $this->image->getInner());  
            $text = new MSpan( '', $this->label, 'mImageLinkLabel' );
            $this->caption = $this->image->generate() . $text->generate();
        }
    }
}

?>