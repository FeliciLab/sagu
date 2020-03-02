<?php
class MCSSPForm extends MForm
{
    public $height;
    public $_top;
    public $_left;

    public function __construct($title = '', $action = '')
    {
        $MIOLO = MIOLO::getInstance();
        $page = $MIOLO->getPage();

        parent::__construct($title, $action);
        $this->heigth = 50;
        $this->_top = 0;
        $this->_left = 0;
    }

    public function setFieldPos($name, $x, $y, $w = 0)
    {
        $field = &$this->$name;

        if ($w)
            $field->setCSSPWidth($w);

        $field->setCSSPPosition($x, $y);
    }

    public function setHeight($value)
    {
        $this->height = $value;
    }

    public function setOrigin($top, $left)
    {
        $this->_top = $top;
        $this->_left = $left;
    }

    public function generateLayoutFields(&$hidden)
    {
        $MIOLO = MIOLO::getInstance();

        if (!is_array($this->fields))
            return;

        $t = new SimpleTable('');
        $t->attributes['table'] = "border=\"0\" height=\"{$this->height}\" width=\"100%\" cellpadding=\"0\" cellspacing=\"2\" class=\"formBody\" ";
        $row = 0;

        foreach ($this->fields as $f)
        {
            if (!$f->visible)
            {
                continue;
            }

            if ((($f->classname == 'textfield') && ($f->size == 0)) || ($f->classname == 'hiddenfield'))
            {
                $hidden[] = $f;
                continue;
            }

            if (($f->readonly || $f->maintainstate))
            {
                $hidden[] = $f;
            }

            $t->cell[0][0][] = $f;
        }

        return $t;
    }

    public function generate()
    {
        if (!isset($this->buttons))
        {
            if ($this->defaultButton)
            {
                $this->buttons[] = new FormButton(FORM_SUBMIT_BTN_NAME, _M("Send"), 'SUBMIT');
            }
        }

        $title = HtmlPainter::generateToString($this->generateTitle());
        $body = HtmlPainter::generateToString($this->generateBody());
        $footer = HtmlPainter::generateToString($this->generateFooter());

        $style = "style=\"\{height:{$form->height}px; position:absolute}\"";
        $b = new Div('', $body, 'formBox', $style);

        if (($form->_top) && ($form->_left))
            $style = "style=\"\{width:{$form->width}; top:{$form->_top}px; left:{$form->_left}px; position:absolute}\"";
        else
            $style = "style=\"\{width:{$form->width}; position:relative}\"";

        $f = new Div('', array($title, $b, $footer), 'formContainer', $style);
        HtmlPainter::generateElements($f);
    }
}
?>