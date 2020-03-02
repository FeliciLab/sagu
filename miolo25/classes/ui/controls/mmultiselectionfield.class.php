<?php
class MMultiSelectionField extends MListControl
{
    public $buttons;
    public $fields;
    public $showcode;
    public $shownav;
    public $layout;
    public $codevalue;
    public $info;
    public $colWidth;
    public $numRows;
    public $fieldWidth;

    public function __construct($name = '', $value = null, $label = '', $fields = '', $width = 200, $buttons = false,
                         $info = '', $hint = '')
    {
        parent::__construct($name, $label, null, null, $hint);
        $this->page->addScript('m_multitext2.js');
        $this->page->addScript('m_multiselection.js');
        $this->fields = $fields;
        $this->colWidth = $width;
        $this->buttons = $buttons;
        $this->info = $info;
        $this->layout = 'horizontal';
        $this->fieldWidth = $width - 20;
        $this->showcode = false;
        $this->shownav = false;
        $this->numRows = 5; 
        $this->formMode = 0;
    }

    public function getCodeValue()
    {
        $value = $this->value;

        if (is_array($value))
        {

            foreach ($value as $v)
            {
                $n = count($this->fields);

                for ($j = 0; $j < $n; $j++)
                {
                    $options = $this->fields[$j]->options;
//                    $r = array_search($v, $options);
                    $r = MUtil::array_search_recursive($v, $options);

                    if ($r !== false)
                        $this->codevalue[] = $r;
                }
            }
        }

        return $this->codevalue;
    }

    public function setCodeValue($value)
    {
        $this->codevalue = $value;
        $r = array();

        if (is_array($value))
        {
            for ($i = 0; $i < count($value); $i++)
            {
                $a = $value[$i];

                $n = count($this->fields);
                for ($j = 0; $j < $n; $j++)
                {
                    $options = $this->fields[$j]->options;
                    $keys = array_keys($options);
                    if (is_array($a))
                    {
                        $k = array_search($a[0], $keys);
                    }
                    else
                    {
                        $k = array_search($a, $keys);
                    }
                    if ($k !== false)
                       $r[$i] = $options[$keys[$k]];
                }
            }
        }

        $this->value = $r;
    }

    public function generateInner()
    {
        $numFields = count($this->fields);
        $mtsName = "mts_{$this->formId}_{$this->name}";
        $this->page->addJsCode("{$mtsName} = new Miolo.MultiSelection('{$this->name}');"); 

        $this->page->onSubmit("{$mtsName}.onSubmit('{$this->formId}','{$this->name}')");

        // select
        $labelS = $this->info . "&nbsp;";
        $content = array
            (
            );

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

        $field = new MMultiSelection("{$this->name}[]", array(), $labelS, $content, '', '', $this->numRows);
        $field->addStyle('width', "{$this->colWidth}px");        
        $field->addEvent('keydown', "return {$mtsName}.onKeyDown(this,this.form,'{$this->name}',event,$numFields);");
        $field->setClass('select', false);
        $field->formMode = 2;
        $select = $field;

        // fields - cada field Ã© um selection
        $fields = array();

        $n = 1;

        foreach ($this->fields as $f)
        {
            $f->setId("{$this->name}_options{$n}");
            $f->setName("{$this->name}_options{$n}");
            $f->setClass('combo');
            $btnAdd[$n] = new MButton("{$this->name}_add{$n}", _M("Add"), "{$mtsName}.add('$n');");
            $btnAdd[$n]->setClass('button');
            $b = new MDiv('', array(new MDiv('', '&nbsp;', 'label'), $btnAdd[$n]));
            $f->addStyle('width', "{$this->fieldWidth}px");
            $f->formMode = 2;
            $fields[] = new MHContainer('',array($f,$b));
            $n++;
        }

        // buttons
        $b = $buttons[] = new MButton("{$this->name}_remove", _M("Delete"), "{$mtsName}.remove($numFields);");
        $b->setClass('button');

        if ($this->shownav)
        {
            $b = $buttons[] = new MButton("{$this->name}_up", '/\\', "{$mtsName}.moveUp($numFields);");
            $b->setClass('button');
            $b = $buttons[] = new MButton("{$this->name}_down", '\\/',"{$mtsName}.moveDown($numFields);");
            $b->setClass('button');
        }
        $btnSel = new MDiv('',$buttons); 

        $commentIn = $this->painter->comment('START OF Field MultiSelectionField');
        $commentOut = $this->painter->comment('END OF Field MultiSelectionField');

        // layout

        $cFields = new MVContainer('', $fields);
        $width = $this->fieldWidth + 85;
        $cFields->addStyle('width', "{$width}px");
        $cSelect = new MVContainer('', array($select, $btnSel)); 
        $t = array($cFields,$cSelect);

        $group = new MBaseGroup('', $this->label, $t, 'horizontal', 'css');

        $div = new MDiv('', $group, 'mMultitextField');
        $this->inner = array (
            $commentIn,
            $div,
            $commentOut
        );

        return $this->inner;
    }
}
?>