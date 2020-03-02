<?php
class MMultiTextField4 extends MMultiTextField3
{
    public $tablevalue;
    public $listTitle = NULL;

    public function __construct($name = '', $value = null, $label = '', $fields = '', $width = 200, $buttons = false, $layout = 'vertical', $hint = '')
    {
        parent::__construct($name, $value, $label, $fields, $width, $buttons, $layout, $hint);
        $this->page->addScript('m_texttable.js');
        $this->page->addScript('m_multitext4.js');
    }

    public function getTableValue()
    {
        $r = array();

        $value = $this->value;

        if (is_array($value))
        {
            for ($i = 0; $i < count($value); $i++)
            {
                $s = substr($value[$i], 1, strlen(trim($value[$i])) - 2);
                $a = explode('] [', $s);

                if (is_array($a))
                {
                    for ($j = 0; $j < count($a); $j++)
                    {
                        $r[$i][$j] = $a[$j];
                    }
                }
            }
        }

        $this->tablevalue = $r;
        return $this->tablevalue;
    }

    public function setListTitle( $title = array() )
    {
        $this->listTitle = $title;
    }

    public function generateInner()
    {
        $mtfName = "mtf_{$this->formId}_{$this->name}";
        $this->page->addJsCode("{$mtfName} = new Miolo.MultiTextField4('{$this->name}');"); 

        $numFields = count($this->fields);

        $this->page->onSubmit("{$mtfName}.onSubmit('{$this->formId}','{$this->name}')");

        // fields
        $fields = '';
        $n = 1;
        $ref = '';

        foreach ($this->fields as $f)
        {
            $ref .= ($ref ? ',' : '') . $f->getName();
            $f->form = $this->form;
            $f->setLabel(htmlspecialchars($f->label)); 
            $f->formMode = 2;
//            if ($f->size == '') $f->_AddStyle('width', "{$this->colWidth}px");
            $fields[] = $f;
            $n++;
        }

        // select
        $labelS = $this->info . "&nbsp;";
        $content = array();

        if (!is_array($this->value))
        {
           $this->value = array(); 
        } 

        $field = new MTextTable("{$this->name}", $this->getTableValue(), $this->info,'',false);
        $field->setScrollHeight( ($this->numRows * 15) . 'px' );
        $field->setScrollWidth( $this->fieldWidth . 'px' );
        if ($this->listTitle)
        {
             $field->setTitle($this->listTitle);
        }
        $field->addCode("{$mtfName}.onSelect('{$ref}');");
        $select = $field;

        // buttons

        $disposition = ($this->layout == 'horizontal') ? 'vertical' : 'horizontal';
        $button[] = new MButton("{$mtfName}_add", _M("Add"), "{$mtfName}.add('{$ref}');");
        $button[] = new MButton("{$mtfName}_modify", _M("Modify"), "{$mtfName}.modify('{$ref}');");
        $button[] = new MButton("{$mtfName}_remove", _M("Delete"), "{$mtfName}.remove('{$ref}');");

        if ($this->shownav)
        {
            $button[] = new MButton("{$mtfName}_up", '/\\', "{$mtfName}.moveUp('{$ref}');");
            $button[] = new MButton("{$mtfName}_down", '\\/', "{$mtfName}.moveDown('{$ref}');");
        }

        foreach ($button as $b)
            $b->setClass('button');

        $buttons = new MContainer('', $button, $disposition);
		$buttons->setShowLabel(false);

        $commentIn = $this->painter->comment('START OF Field MultiTextField4');
        $commentOut = $this->painter->comment('END OF Field MultiTextField4');

        // layout

        $t = array();
        $cFields = new MContainer('', $fields, 'vertical');
        $cFields->addStyle('width', "{$this->fieldWidth}px");
        if ($this->layout == 'vertical')
        {
            $t[] = $select;
            $t[] = $cFields;
            $t[] = $buttons;
            $group = new MBaseGroup('', $this->label, $t, 'vertical', 'css');
        }
        elseif ($this->layout == 'vertical2')
        {
            $t[] = $cFields;
            $t[] = $buttons;
            $t[] = $select;
            $group = new MBaseGroup('', $this->label, $t, 'vertical', 'css');
        }
        elseif ($this->layout == 'horizontal')
        {
            $t[] = new MDiv('', $cFields, 'fieldPosH');
            $t[] = new MDiv('', array(new MDiv('', '&nbsp;', 'label'), $buttons), 'buttonPosH');
            $t[] = new MDiv('', $select, 'selectPosH');
            $group = new MBaseGroup('', $this->label, $t, 'horizontal', 'css');
        }

        $div = new MDiv('', $group, 'mMultitextField');
//        $hidden = new MHiddenField("{$this->id}",'');
        $this->inner = array
            (
//            $hidden,
            $commentIn,
            $div,
            $commentOut
            );

        return $this->inner;
    }
}
?>