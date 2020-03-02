<?php
class MMultiTextField3 extends MMultiTextField2
{

    public function __construct($name = '', $value = null, $label = '', $fields = '', $width = 200, $buttons = false, $layout = 'vertical', $hint = '')
    {
        parent::__construct($name, $value, $label, $fields, $width, $buttons, $layout, $hint);
        $this->page->addScript('m_multitext2.js');
        $this->page->addScript('m_multitext3.js');
    }

    public function getCodeValue()
    {
        $r = array
            (
            );

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
                        if ($this->fields[$j] instanceof MSelection)
                        {
                            $r[$i][$j] = MUtil::array_search_recursive($a[$j], $this->fields[$j]->options);
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
    
    public function setCodeValue($value)
    {
        $this->codevalue = $value;
        $r = array
            (
            );

        if (is_array($value))
        {
            foreach ($value as $i=>$v)
            {
                $a = $value[$i];

                if (is_array($a)) // varios valores => varios fields
                {
                    for ($j = 0; $j < count($a); $j++)
                    {
                        if ($this->fields[$j] instanceof MSelection)
                        {
                            $r[$i] .= '[' . $this->fields[$j]->getOption($value[$i][$j]) . '] ';
                        }
                        else
                        {
                            $r[$i] .= '[' . $value[$i][$j] . '] ';
                        }
                    }
                }
                else // valor unico => apenas um field (na posicao 0)
                {
                    if ($this->fields[$i] instanceof MSelection)
                    {
                        $r[$i] .= '[' . $this->fields[$i]->getOption($value[$i]) . '] ';
                    }
                    else
                    {
                        $r[$i] .= '[' . $value[$i] . '] ';
                    }
                }
            }
        }

        $this->value = $r;
    }

    public function generateInner()
    {
        $numFields = count($this->fields);
        $mtfName = "mtf_{$this->formId}_{$this->name}";
        $this->page->addJsCode("{$mtfName} = new Miolo.MultiTextField3('{$this->name}');"); 

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
//            if ($f->size == '') $f->width = "{$this->colWidth}px";
            $fields[] = $f;
            $n++;
        }

        // select
        $labelS = $this->info . "&nbsp;";
        $content = array();

        if (is_array($this->value))
        {
            foreach ($this->value as $v)
            {
                $multiValue = '';

                if (is_array($v))
                    foreach ($v as $v2)
                        $multiValue .= "[" . $v2 . "] ";
                else
                    $multiValue = $v;

                $content[] = new MOption('', $multiValue, $multiValue);
            }
        }

        $field = new MMultiSelection("{$this->name}[]", array(),$labelS,$content,'','', $this->numRows);
        $field->addStyle('width', "{$this->colWidth}px");
        $field->addEvent('keydown', "return {$mtfName}.onKeyDown(this,this.form,'{$this->name}',event,$numFields);");
        $field->addEvent('change', "{$mtfName}.onSelect('{$ref}');");
        $field->setClass('select', false);
        $field->formMode = 2; 
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

        $commentIn = $this->painter->comment('START OF Field MultiTextField3');
        $commentOut = $this->painter->comment('END OF Field MultiTextField3');

        // layout

        $t = array();
        $cFields = new MContainer('', $fields, 'vertical');
        $cFields->width = "{$this->fieldWidth}px";
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
			$fieldPosH =  new MDiv('', $cFields, 'fieldPosH');
            $fieldPosH->width = "{$this->fieldWidth}px";
            $t[] = $fieldPosH;
            $t[] = new MDiv('', array(new MDiv('', '&nbsp;', 'label'), $buttons), 'buttonPosH');
            $t[] = new MDiv('', $select, 'selectPosH');
            $group = new MBaseGroup('', $this->label, $t, 'horizontal', 'css');
        }

        $div = new MDiv('', $group, 'mMultitextField');
        $this->inner = array
            (
            $commentIn,
            $div,
            $commentOut
            );

        return $this->inner;
    }
}
?>