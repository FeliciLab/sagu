<?
class MContainer extends MContainerControl
{
    /* disposition of the content: horizontal|none or vertical */
    public $disposition;

//    public $separator;
//    public $spaceHeight; // espaÃ§amento em pixels entre os campos no disposition=vertical
//    public $spaceWidth='&nbsp;&nbsp;'; //espaÃ§amento em pixels entre os campos no disposition=horizontal

    /* How the labels are showed */
    public $formMode;
    
    /*  se label deve ser exibido junto com os campos
     *  Esse atributo foi modificado para private para forÃ§ar a 
     *  utilizaÃ§Ã o do mÃ©todo setShowLabel. Esta modificaÃ§Ã£o foi
     *  necessÃ¡ria para os casos em que o programador necessite
     *  que os labels dos conteÃºdos fossem exibidos
     */
    public $showLabel;

    /*   esta propriedade controla a exibiÃ§Ã£o ou nÃ£o do label dos
     *   conteÃºdos de um container. Ã necessÃ¡rio utilizar o mÃ©todo
     *   setShowChildLabel para modificar esta propriedade.
     */
    public $showChildLabel = true; //se o label dos conteiner conteÃºdos deste serÃ£o exibidos

    public function __construct($name = NULL, $controls = NULL, $disposition = 'none', $formMode = MFormControl::FORM_MODE_SHOW_ABOVE)
    {
        parent::__construct($name);
        $this->formMode = $formMode;
        $controls = (($controls != '') && is_array($controls)) ? $controls : array();
        $this->showLabel = false;
//        $this->spaceHeight = '3px';
//        $this->spaceWidth = '5px';
        $this->setControls($controls);
        $this->setDisposition($disposition);
    }

/*
    public function setSpaceHeight($value)
    {
        $this->spaceHeight = $value;
    }

    public function setSpaceWidth($value)
    {
        $this->spaceWidth = $value;
    }
*/

    public function setDisposition($disposition)
    {
        $this->disposition = ($disposition == 'none') ? 'horizontal' : $disposition;

/* o uso do separator foi substituido pelos atributos css - ely
        switch ($this->disposition)
            {
            case 'vertical':
                $div = new MSpacer($this->spaceHeight);

                break;

            case 'horizontal':
                $div = new MDiv('', $this->spaceWidth);

                break;

            default:
                $div = NULL;

                break;
            }

        $this->separator = $div;
*/
    }

    public function isShowLabel()
    {
        return $this->showLabel;
    }

    public function isShowChildLabel()
    {
        return $this->showChildLabel;
    }

    public function setShowChildLabel( $visible=true, $recursive=true )
    {
        $this->showChildLabel = $visible;
        $controls = $this->getControls();
        $this->setControls($controls,$recursive);
    }

    public function setShowLabel( $visible=true, $recursive=true )
    {
        $this->showLabel = $visible;

        if( $recursive )
        {
            $this->setShowChildLabel( $visible, $recursive );
        }
    }

    public function setControls($controls,$recursive=false)
    {
        $this->clearControls();

        foreach ( $controls as $c )
        {
            if ( $recursive && ($c instanceof MContainer) )
            {
                $c->setShowChildLabel($this->showChildLabel,true);
            }
            if( is_object($c) )  //acrescentado devido ao erro!
            {
                if ($c instanceof MFormControl)
                {
                    $c->showLabel = $this->showChildLabel;
                }
                $this->addControl($c);
            }
            /*else
            {
                trigger_error( _M('Trying to access a property on a non-object'), E_USER_WARNING);
            }*/
        }
    }

    public function generateInner()
    {
        $float = false;
        $t = array();

        $controls = $this->getControls();

        foreach ($controls as $control)
        {
            $c = clone $control;
            if ($c instanceof MInputControl)
            {
                $c->setAutoPostBack($this->autoPostBack || $c->autoPostBack);
            }
            if ( $c->showLabel )
            {
               $c->formMode = $this->formMode;
            }

            if ($this->disposition == 'horizontal')
            {
//old                $c->float = $this->separator->float = 'left';
//                $c->float = 'left';
//                $c->addBoxStyle('margin-right', $this->spaceWidth);

                if ( ($this->formMode == MFormControl::FORM_MODE_SHOW_SIDE) && !($c instanceof MContainer) )
                {
                    $c = new MFormContainer('', array( $c ));
                }
                else
                {
                    $c = ($c instanceof MDiv) ? $c : new MDiv('',$c);
                }

                $c->setClass('mContainerHorizontal');
                $float = true;
            }
            else
            {
                if ( $this->formMode == MFormControl::FORM_MODE_SHOW_SIDE )
                {
                    $c = MForm::generateLayoutField($c);
                }
                else
                {
                    $c = ($c instanceof MDiv) ? $c : new MDiv('',$c);
                    $c->setClass('mContainerVertical');
//                    $c->addBoxStyle('margin-bottom', $this->spaceHeight);
                }
            }

            $t[] = MHtmlPainter::generateToString($c);
//            $t[] = $this->separator;
        }

        if ($float)
        {
            $t[] = new MSpacer();
        }

        $this->inner = $t;
//        $this->getBox()->setAttributes($this->getAttributes());
    }
}

class MVContainer extends MContainer
{
    public function __construct($name = NULL, $controls = NULL, $formMode = MFormControl::FORM_MODE_SHOW_ABOVE)
    {
        parent::__construct($name, $controls, 'vertical', $formMode);
    }
}

class MHContainer extends MContainer
{
    public function __construct($name = NULL, $controls = NULL)
    {
        parent::__construct($name, $controls, 'horizontal');
    }
}

class MFormContainer extends MContainer
{
    public function __construct($name=NULL, $controls=NULL)
    {
        parent::__construct($name, $controls, 'vertical', MFormControl::FORM_MODE_SHOW_SIDE);
    }
}

/**
 * Container class which creates the label of the first control aligned as any other input control.
 */
class MRowContainer extends MContainer
{
    /**
     * @var string Left padding between elements.
     */
    public $paddingLeft = '20px';

    /**
     * @return string Generate a label span with the label of the first control.
     */
    public function generateLabel()
    {
        $label = '';
        $controls = $this->getControls();
        $mainField = $controls[0];

        if ( strlen(trim($mainField->label)) )
        {
            $span = new MSpan('', $mainField->label . ':', 'label');

            if ( $this->attrs->items['required'] || ($mainField->validator && $mainField->validator->type == 'required') )
            {
                $span->setClass('mCaptionRequired');
            }

            $label = $this->painter->span($span);
        }

        return $label;
    }

    /**
     * @return string Generate a field span with all container fields, except the label of the first control.
     */
    public function generateFieldSpan()
    {
        $controls = $this->getControls();

        foreach ( $controls as $index => $control )
        {
            if ( strlen(trim($control->label)) && $index != 0 )
            {
                $span = new MSpan('', $control->label . ':', 'mText');
                $span->addStyle('padding-left', $this->paddingLeft);

                if ( $this->attrs->items['required'] || ($control->validator && $control->validator->type == 'required') )
                {
                    $span->setClass('mCaptionRequired');
                }

                $label = $this->painter->span($span);

                $fields[] = $label;
            }

            $fields[] = $control;
        }

        $span = new MSpan(NULL, $fields, 'field');
        return $span->generate();
    }

    /**
     * @return string Generate component.
     */
    public function generate()
    {
        return $this->generateLabel() . $this->generateFieldSpan();
    }
}

?>