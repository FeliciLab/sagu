<?php
class MAreaContainer extends MControl
{
    public $top = array();
    public $left = array();
    public $center = array();
    public $right = array();
    public $bottom = array();

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    #+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    # Adds an element at the specified position
    #---------------------------------------------------------------------
    public function addElement($element, $where = 'center')
    {
        $MIOLO = MIOLO::getInstance();

        if ($where == 'top')
        {
            $this->top[] = $element;
        }
        else if ($where == 'left')
        {
            $this->left[] = $element;
        }
        else if ($where == 'center')
        {
            $this->center[] = $element;
        }
        else if ($where == 'right')
        {
            $this->right[] = $element;
        }
        else if ($where == 'bottom')
        {
            $this->bottom[] = $element;
        }
        else
        {
            $MIOLO->error(_M("Container: Illegal positioning '$where' parameter!"));
        }
    }

    #+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    # Obtains the number of table rows
    #---------------------------------------------------------------------
    public function getRowCount()
    {
        $num_rows = 0;

        if ($this->top)
        {
            $num_rows++;
        }

        if ($this->left || $this->center || $this->right)
        {
            $num_rows++;
        }

        if ($this->bottom)
        {
            $num_rows++;
        }

        return $num_rows;
    }

    #+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    # Obtains the number of table columns
    #---------------------------------------------------------------------
    public function getColumnCount()
    {
        $num_cols = 0;

        if ($this->left)
        {
            $num_cols++;
        }

        if ($this->center)
        {
            $num_cols++;
        }

        if ($this->right)
        {
            $num_cols++;
        }

        if (!$num_cols)
        {
            if ($this->top)
            {
                $num_cols++;
            }
            else if ($this->bottom)
            {
                $num_cols++;
            }
        }

        return $num_cols;
    }

    #++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    # Generate HTML
    #----------------------------------------------------------------------
    public function generateInner()
    {
        $num_rows = $this->getRowCount();
        $num_cols = $this->getColumnCount();

        if ($num_rows && $num_cols)
        {
            $table = new MSimpleTable($obj->name);
            $table->setAttribute('width', '100%');
            $table->setCell(0, 0, $this->top, "align=\"center\" colspan=$num_cols");
            $table->setCell(1, 0, $this->left, "align=\"center\" valign=\"top\"");
            $table->setCell(1, 1, $this->center, "align=\"center\" valign=\"top\" width=\"100%\"");
            $table->setCell(1, 2, $this->right, "align=\"center\" valign=\"top\"");
            $table->setCell(2, 0, $this->bottom, "align=\"center\" colspan=$num_cols");
            $this->inner = $table;
        }
    }
}
?>