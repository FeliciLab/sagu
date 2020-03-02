<?php

class MLink extends MFormControl
{
    public $target = '_self';
    public $href;
    public $onClick;


    public function __construct( $name = NULL, $label = NULL, $href = NULL, $text = NULL, $target = '_self' )
    {
        parent::__construct( $name, $href, $label );

        $this->caption = $text;
        $this->href    = $href;
        $this->onClick = '';
        $this->target  = $target;
    }


    public function setOnClick( $onClick )
    {
        if ( $this->onClick )
        {
            $this->onClick .= ';' . $onClick . '; return false;';
        }
        else
        {
            $this->onClick = $onClick . '; return false;';
        }
    }


    public function setText( $text )
    {
        $this->caption = $text;
    }

    public function setTarget( $target )
    {
        $this->target = $target;
    }

    public function setHREF( $href )
    {
        $this->href = $href;
    }

    public function setAction( $module = '', $action = '', $item = null, $args = null )
    {
        $goto = $this->manager->getActionURL( $module, $action, $item, $args );
        $formId = $this->page->getFormId();
        $onclick = "miolo.doLink('{$goto}','{$formId}');";

        $this->href = $goto;
        $this->setOnClick($onclick);
    }

    public function generateInner()
    {
        if ( $this->onClick == '' )
        {
            $formId = $this->page->getFormId();
            $this->setOnClick("miolo.doLink('{$this->href}','{$formId}')");
        }
        $this->href = '';

        if ( $this->readOnly )
        {
            $this->inner = MHtmlPainter::span( 'm-readonly', $this->name, $this->caption );

            return;
        }

        if ( $this->getClass() == '' )
        {
            $this->setClass( 'm-link' );
        }

        if ( $this->onClick != '' )
        {
            $this->addAttribute( 'onclick', $this->onClick );
        }

        if ( $this->target != '_self' )
        {
            $this->addAttribute( 'target', $this->target );
        }

        if ( $this->caption == '' )
        {
            $this->caption = $this->label;
        }

        $this->inner = $this->generateLabel() . $this->getRender('anchor');
    }
}


class MLinkButton extends MLink
{
    public function __construct( $name = '', $label = '', $action = '', $text = NULL )
    {
        parent::__construct( $name, $label, $action, $text );
//        $this->page->addScript('m_linkbutton.js');
    }

    public function generateLink()
    {
        $action = $this->href;
        $actionUpper = strtoupper($this->href);
        $formId = $this->page->formid;
        if ( $action == '' )
        {
            if ( count($this->eventHandlers) )
            {
                $param   = $this->eventHandlers['click']['param'];
                $onclick = "miolo.doPostBack('{$this->name}:click','{$param}','$formId')";
            }
            else
            {
                if ( $this->name != '' )
                {
                    $onclick = "miolo.doPostBack('{$this->name}:click','','$formId');";
                }
            }
        }
        else if ( $actionUpper == 'PRINT' )
        {
            $onclick = "miolo.doPrintForm();";
        }
        else if ( $actionUpper == 'PDF' )
        {
            if ( $this->name != '' )
            {
                $onclick = "miolo.doPostBack('{$this->name}:click',''); miolo.doShowPDF();";
            }
        }
        else if ( substr($actionUpper, 0, 7) == 'HTTP://' )
        {
            if ( count($this->eventHandlers) )
            {
                $param   = $this->eventHandlers['click']['param'];
                $onclick = "miolo.doLinkButton('$action','{$this->name}:click','{$param}','$formId')";
            }
            elseif ( $this->name != '' )
            {
                $onclick = "miolo.doLinkButton('$action','{$this->name}:click','','$formId');";
            }
            else
            {
                $onclick = "miolo.doLinkButton('$action','','','$formId');";
            }
            $goto = $action;
        }
        else if ( substr($actionUpper, 0, 3) == 'GO:' )
        {
            $goto = substr($action, 3);
            $onclick = "miolo.doLink('$goto','$formId');";
        }
        else if ( $action{0} == ':' )
        {
            $event = substr($action, 1);
            $onclick = $this->manager->getUI()->getAjax($event);
        }
        else
        {
            $onclick = $action;
        }
        
        $this->href = $goto ? $goto : '';
        $this->setOnClick($onclick);
    }


    public function generateInner()
    {
        $this->generateLink();

        parent::generateInner();
    }

}


class MActionHyperLink extends MLink
{
    public function __construct( $name, $label, $module = '', $action = '', $item = null, $args = null )
    {
        parent::__construct( $name, $label );

        $this->setAction( $module, $action, $item, $args );
    }
}

class MImageLink extends MLink
{
    public $location;
    public $image;

    public function __construct( $name = '', $label = '', $action = '', $location = '', $attrs = NULL )
    {
        parent::__construct( $name, $label, $action );

        $this->location = $location;
        $this->setAttributes( $attrs );

        $this->image = new MImage( $name, $label, $location, array('border' => '0') );
    }


    public function generateLink()
    {
        $this->caption = $this->image->generate();
    }

    public function generateInner()
    {
        $this->setClass( 'm-image-link' );
        $this->generateLink();

        parent::generateInner();
    }
}


class MImageLinkLabel extends MImageLink
{
    private $imageType = 'normal';


    public function setImageType( $type = 'normal' )
    {
        $this->imageType = $type;
    }


    public function generateLink()
    {
        if ( $this->imageType == 'normal' )
        {
            $this->image->generateInner();
            $image = new MDiv( '', $this->image->getInner(), 'm-image-centered' );
            $text = new MSpan( '', $this->label, 'm-image-link-label m-image-label' );
            $this->caption = $image->generate() . $text->generate();
        }
        elseif ( $this->imageType == 'icon' )
        {
            $this->image->setClass( 'm-image-icon' );
            $this->image->generateInner();
            //          $image = new Span('', $this->image->getInner());  
            $text = new MSpan( '', $this->label, 'm-image-link-label' );
            $this->caption = $this->image->generate() . $text->generate();
        }
    }
}


class MImageButton extends MImageLink
{
    public function generateLink()
    {
        parent::generateLink();

        $action = $this->href;
        $actionUpper = strtoupper($this->href);
        $formId = $this->page->formid;

        if ( $action == '' )
        {
            if ( count($this->eventHandlers) )
            {
                $param   = $this->eventHandlers['click']['param'];
                $onclick = "miolo.doPostBack('{$this->name}:click','{$param}','$formId')";
            }
            else
            {
                if ( $this->name != '' )
                {
                    $onclick = "miolo.doPostBack('{$this->name}:click','','$formId');";
                }
            }
        }
        else if ( substr($actionUpper, 0, 7) == 'HTTP://' )
        {
            if ( count($this->eventHandlers) )
            {
                $param   = $this->eventHandlers['click']['param'];
                $onclick = "miolo.doLinkButton('$action','{$this->name}:click','{$param}','$formId')";
            }
            elseif ( $this->name != '' )
            {
                $onclick = "miolo.doLinkButton('$action','{$this->name}:click','','$formId');";
            }
            else
            {
                $onclick = "miolo.doLinkButton('$action','','','$formId');";
            }
            $goto = $action;
        }
        else if ( substr($actionUpper, 0, 3) == 'GO:' )
        {
            $goto = substr($action, 3);
            $onclick = "miolo.doLink('$goto','$formId');";
        }
        else if ( $action{0} == ':' )
        {
            $event = substr($action, 1);
            $onclick = $this->manager->getUI()->getAjax($event);
        }
        else
        {
            $onclick = $action;
        }

        if ( $goto )
        {
            $this->href = $goto;
            if ( $this->onClick )
            {
                $this->onClick .= ';' . $onclick . '; return false;';
            }
            else
            {
                $this->onClick = $onclick . '; return false;';
            }
        }
        else
        {
            $this->href = "javascript:$onclick;";
        }

        $this->target = '';
    }
}

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
            $this->image->generateInner();
            $image = new MDiv( '', $this->image->getInner(), 'm-image-centered' );
            $text = new MSpan( '', $this->label, 'm-image-link-label m-image-label' );
            $this->caption = $image->generate() . $text->generate();
        }
        elseif ( $this->imageType == 'icon' )
        {
            $this->image->setClass( 'm-image-icon' );
            $this->image->generateInner();
            //          $image = new Span('', $this->image->getInner());  
            $text = new MSpan( '', $this->label, 'm-image-link-label' );
            $this->caption = $this->image->generate() . $text->generate();
        }
    }
}

?>
