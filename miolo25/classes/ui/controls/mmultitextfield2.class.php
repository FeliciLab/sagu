<?php

class MMultiTextField2 extends MTextField
{
    protected $buttons;
    protected $fields;
    protected $showcode;
    protected $shownav;
    protected $layout;
    protected $codevalue;
    protected $colWidth;
    public $info;
    protected $numRows;
    public $fieldWidth;

    /**
     *
     */
    public function __construct( $name = '', $value = null, $label = '', $fields = '', $width = 200, $buttons = false, $layout = 'vertical', $hint = '' )
    {
        parent::__construct( $name, $value, $label, null, $hint );

        $this->page->addScript( 'm_multitext2.js' );

        $this->fields     = $fields;
        $this->colWidth   = $width;
        $this->buttons    = $buttons;
        $this->layout     = $layout;
        $this->fieldWidth = $width;
        $this->showcode   = false;
        $this->shownav    = false;
        $this->numRows    = 5;
        $this->formMode   = 0;
    }

    /**
     *
     */
    public function getCodeValue()
    {
        $r = array();

        $value = $this->value;

        if ( is_array( $value ) )
        {
            for ( $i = 0; $i < count( $value ); $i++ )
            {
                $s = substr( $value[$i], 1, strlen( trim( $value[$i] ) ) - 2 );
                $a = explode('] [', $s);

                if ( is_array( $a ) )
                {
                    for ( $j = 0; $j < count( $a ); $j++ )
                    {
                        $options = $this->fields[$j][3];

                        if ( $options )
                        {
                            $r[$i][$j] = array_search( $a[$j], $options );
                        }
                        else
                        {
                            $r[$i][$j] = $a[$j];
                        }
                    }
                }
            }
        }

        $this->codevalue = $r;

        return $this->codevalue;
    }

    /**
     *
     */
    public function setCodeValue( $value )
    {
        $this->codevalue = $value;
        $r = array();

        if ( is_array( $value ) )
        {
            for ( $i = 0; $i < count( $value ); $i++ )
            {
                $a = $value[$i];

                if ( is_array( $a ) )  // varios valores => varios fields
                {
                    for ( $j = 0; $j < count( $a ); $j++ )
                    {
                        $options = $this->fields[$j][3];

                        if ( $options )
                        {
                            $r[$i] .= '[' . $options[$value[$i][$j]] . '] ';
                        }
                        else
                        {
                            $r[$i] .= '[' . $value[$i][$j] . '] ';
                        }
                    }
                }
                else // valor unico => apenas um field (na posicao 0)
                {
                    $options = $this->fields[0][3];

                    if ( $options )
                    {
                        $r[$i] .= '[' . $options[$a] . '] ';
                    }
                    else
                    {
                        $r[$i] .= '[' . $a . '] ';
                    }
                }
            }
        }

        $this->value = $r;
    }

    /**
     *
     */
    public function generateInner()
    {
        $numFields = count( $this->fields );
        $mtfName = "mtf_{$this->formId}_{$this->name}";
        $this->page->addJsCode("{$mtfName} = new Miolo.MultiTextField2('{$this->name}');"); 

        $this->page->onSubmit("{$mtfName}.onSubmit('{$this->formId}','{$this->name}')");

        // select
        $labelS  = $this->info . "&nbsp;";
        $content = array();

        if ( is_array( $this->value ) )
        {
            foreach ( $this->value as $v )
            {
                $multiValue = '';

                if ( is_array( $v ) )
                {
                    foreach ( $v as $v2 )
                    {
                        $multiValue .= "[" . $v2 . "] ";
                    }
                }
                else
                {
                    $multiValue = $v;
                }

                $content[] = new MOption( '', $multiValue, $multiValue );
            }
        }

        $field = new MMultiSelection( "{$this->name}[]", array(), $labelS, $content, '', '', $this->numRows );

        $field->addStyle( 'width', "{$this->colWidth}px" );
        $field->addEvent( 'keydown', "return {$mtfName}.onKeyDown(this,this.form,'{$this->name}',event,$numFields);" );
        $field->addEvent( 'change', "{$mtfName}.onSelect(this.form,'{$this->name}', $numFields);" );
        $field->setClass( 'select', false );
        $field->formMode = 2;
        $select = $field;

        // fields
        $n = 1;
        unset ( $ref );

        foreach ( $this->fields as $f )
        {
            if ( $ref )
            {
                $ref .= ',';
            }

            $ref .= $f[0];
            $labelF = htmlspecialchars( $f[1] ) . ( $f[2] ? ' - ' . htmlspecialchars( $f[2]) : '' );

            // caso tenhamos opÃ§Ãµes para este campo ($f[3] Ã© o array de opÃ§Ãµes)
            // utilizamos um selection caso contrÃ¡rio um simples text field
            if ( $options = $f[3] )
            {
                $content = array();

                if ( $this->showcode )
                {
                    foreach ( $options as $code => $desc )
                    {
                        $content[] = new MOption( '', $code, "$code - $desc" );
                    }
                }
                else
                {
                    foreach ( $options as $code => $desc )
                    {
                        $content[] = new MOption( '', $code, $desc );
                    }
                }

                $field = new MSelection( "{$this->name}_options{$n}", '', $labelF, $content );
                $field->setClass( 'combo' );
            }
            else
            {
                $field = new MTextField( "{$this->name}_text{$n}",'',$labelF );
                $field->addEvent( 'keydown', "return  {$mtfName}.onKeyDown(this,this.form,'{$this->name}',event,$numFields);" );
                $field->setClass( 'textfield' );
            }
            $field->_AddStyle( 'width', "{$this->fieldWidth}px" );
            $field->formMode = 2;
            $fields[] = $field;
            $n++;
        }

        // buttons
        $disposition = ( $this->layout == 'horizontal' ) ? 'vertical' : 'horizontal';
        $button[] = new MButton( "{$mtfName}_add", _M("Add"), "{$mtfName}.add($numFields)" );
        $button[] = new MButton( "{$mtfName}_modify", _M("Modify"), "{$mtfName}.modify($numFields)" );
        $button[] = new MButton( "{$mtfName}_remove", _M("Delete"), "{$mtfName}.remove($numFields)" );

        if ( $this->shownav )
        {
            $button[] = new MButton( "{$mtfName}_up", '/\\', "{$mtfName}.moveUp(this.form,$numFields)" );
            $button[] = new MButton( "{$mtfName}_down", '\\/', "{$mtfName}.moveDown(this.form,$numFields)" );
        }

        foreach ( $button as $b )
        {
            $b->setClass( 'button' );
        }

        $buttons = new MContainer( '', $button, $disposition );
		$buttons->setShowLabel(false);

        $commentIn  = $this->painter->comment( 'START OF Field MultiTextField2' );
        $commentOut = $this->painter->comment( 'END OF Field MultiTextField2' );

        // layout

        $t = array();
        $cFields = new MContainer( '', $fields, 'vertical' );
        if ( $this->layout == 'vertical' )
        {
            $t[] = $select;
            $t[] = $cFields;
            $t[] = $buttons;
            $group = new MBaseGroup( '', $this->label, $t, 'vertical', 'css' );
        }
        elseif ( $this->layout == 'vertical2' )
        {
            $t[] = $cFields;
            $t[] = $buttons;
            $t[] = $select;
            $group = new MBaseGroup( '', $this->label, $t, 'vertical', 'css' );
        }
        elseif ( $this->layout == 'horizontal' )
        {
            $t[] = $cFields;
            $t[] = new MDiv( '', array( new MDiv( '', '&nbsp;', 'label' ), $buttons ), 'buttonPosH' );
            $t[] = new MDiv( '', $select, 'selectPosH' );
            $group = new MBaseGroup( '', $this->label, $t, 'horizontal', 'css' );
        }

        $div = new MDiv( "m_{$mtfName}", $group, 'mMultitextField' );
        $this->inner = array( $commentIn, $div, $commentOut );

        return $this->inner;
    }
}

?>
