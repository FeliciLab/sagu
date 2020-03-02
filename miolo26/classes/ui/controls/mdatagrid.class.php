<?php
class MDataGridColumn extends MGridColumn
{
    public $field; // field of query ("field" or "table.field")
    public $table; // owner of field

    public function __construct($field,          $title = '',    $align = 'left', $nowrap = false, $width = 0, $visible = true,
                         $options = null, $order = false, $filter = false)
    {
        parent::__construct($title, $align, $nowrap, $width, $visible, $options, $order, $filter);
        $this->field = $field;
        $f = explode('.', $field);

        if (count($f) == 2)
        {
            $this->field = $f[1];
            $this->table = $f[0];
        }
    }
}

class MDataGridHyperlink extends MGridHyperlink
{
    public $field; // field of query ("field" or "table.field")
    public $table; // owner of field
    public function __construct($field, $title = '', $href, $width = 0, $visible = true, $options = null, $order = false,
                         $filter = false)
    {
        parent::__construct($title, $href, $width, $visible, $options, $order, $filter);
        $this->field = $field;
        $f = explode('.', $field);

        if (count($f) == 2)
        {
            $this->field = $f[1];
            $this->table = $f[0];
        }
    }
}

class MDataGridControl extends MGridControl
{
    public $field; // field of query ("field" or "table.field")
    public $table; // owner of field

    public function __construct(&$control, $field, $title = '', $alinhamento = null, $nowrap = false, $width = 0,
                         $visible = true)
    {
        parent::__construct($control, $title, $alinhamento, $nowrap, $width, $visible);
        $this->field = $field;
        $f = explode('.', $field);

        if (count($f) == 2)
        {
            $this->field = $f[1];
            $this->table = $f[0];
        }
    }
}

class MDataGridAction extends MGridAction
{
    public function __construct($type, $alt, $value, $href, $index = null, $enabled = true)
    {
        parent::__construct($type, $alt, $value, $href, $enabled, $index);
    }
}

class MDataGrid extends MGrid
{
    public $query; // base object query 
    public $database; // database conf where execute the query
    public $sql; // sql object
    public $sqlcmd; // sql command text (select ...)
    public $db; // database object where execute the query

    /**
      DataGrid2 constructor
         $query - a query object
         $columns - array of columns objects
         $href - base url of this datagrid
         $pageLength - max number of rows to show (0 to show all)
    */
    public function __construct($query, $columns, $href, $pageLength = 15, $index = 0, $name = '', $useSelecteds = true)
    {
        $this->query = $query;
        parent::__construct(NULL, $columns, $href, $pageLength, $index, $name, $useSelecteds);

        if ( $this->pageLength )
        {
            $this->pn = new MGridNavigator($this->pageLength, $this->rowCount,
            $this->getURL($this->filtered, $this->ordered), $this);
        }

    }

    public function setColumns($columns)
    {
        $this->columns = array
            (
            );
        
        if (is_null($columns)) return;

        if (!is_array($columns))
            $columns = array($columns);
        foreach ($columns as $k => $c)
        {
            $this->columns[$c->field] = $c;
            $this->columns[$c->field]->index = $this->query->getColumnNumber($c->field);
            $this->columns[$c->field]->grid = $this;
        }
    }

    public function applyFilter()
    {
        $MIOLO = MIOLO::getInstance();
        $page = $MIOLO->getPage();

        if ($this->data == NULL)
            return;

        if ($this->filters)
        {
            foreach ($this->filters as $f)
            {
                if ($f->enabled)
                    //                 $this->query->addFilter($f->index,'like',$page->request($f->control->name));
                    $this->query->addFilter($f->index, 'like', $f->value);
            }

            $this->query->applyFilter();
            $this->data = $this->query->result;
        }
    }

    public function generateData()
    {
        global $state;

        $this->data = $this->query->result;
        $this->orderby = $this->page->request('orderby');

        if ($this->ordered = isset($this->orderby))
        {
            $this->query->setOrder($this->orderby);
            $state->set('orderby', $this->orderby, $this->name);
        }

        if ($this->getFiltered())
        {
            $this->applyFilter();
        }

        $this->rowCount = $this->query->getRowCount();

        if ($this->pageLength)
        {
            $this->pn->setGridParameters($this->pageLength, $this->rowCount, $this->getURL($this->filtered, $this->ordered), $this);
            $this->query->setpageLength($this->pageLength);
            $this->data = $this->query->getPage($this->pn->getPageNumber());
        }
        else
        {
            $this->pn = null;
        }
    }

    public function callRowMethod()
    {
        if (isset($this->rowmethod))
        {
            $i = $this->currentRow;
            $row = $this->data[$i];
            call_user_func($this->rowmethod, $i, $row, $this->actions, $this->columns, $this->query);
        }
    }
}

class MDataGrid2 extends MDataGrid
{
}

?>
