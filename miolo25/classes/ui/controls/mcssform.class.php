<?php
class MCSSForm extends MForm
{
    public $structure;
    public $altColor;
    public $alternate;

    public function __construct($title = '', $action = '', $close = 'backContext', $icon = '')
    {
        parent::__construct($title, $action, $close, $icon);
        $this->altColor = array
            (
            '#FFFFFF',
            '#EEEEEE'
            );

        $this->alternate = false;
    }

    public function setAlternateColors($color0, $color1)
    {
        $this->altColor = array
            (
            $color0,
            $color1
            );
    }

    public function setAlternate($alternate = false)
    {
        $this->alternate = $alternate;
    }

    public function setField($row, $col, $width, $field = NULL, $style = array())
    {
        $this->structure[$row][$col]['width'] = $width;
        $this->structure[$row][$col]['field'] = $field;
        $this->structure[$row][$col]['style'] = $style;
        $this->addField($field);
    }

    public function generateLayoutFields(&$hidden)
    {
        if (!is_array($this->fields))
            return;

        $t = array();

        ksort($this->structure, SORT_NUMERIC);
        $rowNumber = 0;

        foreach ($this->structure as $row)
        {
            $rowContent = array();

            $lastCol = count($row) - 1;
            $colNumber = $nWidth = 0;

            foreach ($row as $col)
            {
                $f = $col['field'];

                if (count($col['style']))
                    foreach ($col['style'] as $s => $v)
                        $f->addStyle($s, $v);

                $c = $rowContent[] = new MDiv('', $f);
                $width = (($colNumber == $lastCol) && ($colNumber != 0)) ? 98 - $nWidth : $col['width'];
                $nWidth += $width;
                $c->addStyle('width', "{$width}%");
                $c->addStyle('float', 'left');
                $c->addStyle('padding', '1px');
                $colNumber++;
            }

//            $rowContent[] = new Spacer();
            $d = $t[$rowNumber] = new MDiv('', $rowContent, 'mContainerControls');
            $d->addStyle('clear', 'both');
            $d->addStyle('width', '98%');
            $d->addStyle('padding', '2px 1px 1px 1px');

            if ($this->alternate)
            {
                $d->addStyle('backgroundColor', $this->altColor[$rowNumber % 2]);
            }

            $rowNumber++;
        }

        foreach ($this->fields as $f)
        {
            if ((($f->className == 'textfield') && ($f->size == 0)) || ($f->className == 'hiddenfield'))
            {
                $hidden[] = $f;
                continue;
            }

            if (($f->readonly || $f->maintainstate))
            {
                $hidden[] = $f;
            }
        }

        return $t;
    }

    public function generate()
    {
        if (!isset($this->buttons))
        {
            if ($this->defaultButton)
            {
                $this->buttons[] = new FormButton(FORM_SUBMIT_BTN_NAME, _M('Send'), 'SUBMIT');
            }
        }

        $body = new MDiv('',$this->generateBody(),'mFormBody');
        if (!is_null($this->bgColor))
        {
            $body->addStyle('background-color',$this->bgColor);
        }
        $this->formBox->setControls(array($body, $footer));
        $id = $this->getUniqueId();
        $this->formBox->setClass("mFormOuter");
        $form = new MDiv("frm$id", $this->formBox,"mForm");
        if (!is_null($this->align))
        {
            $form->addBoxStyle('text-align',$this->align);
        }
        return $form->generate();

    }
}
?>