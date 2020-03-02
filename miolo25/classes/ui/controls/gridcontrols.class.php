<?php

class MGridHyperlink extends MGridColumn
{
    public $href; // link - replaces #?# with column's value
    public function __construct($title = '', $href, $width = 0, $visible = true, $options = null, $order = false,
                         $filter = false)
    {
        parent::__construct($title, null, false, $width, $visible, $options, $order, $filter);
        $this->align = 'left';
        $this->href = $href;
        $this->basecontrol = new MLink('', '', $href);
        $this->basecontrol->setClass('mGridLink');
    }

    public function generate()
    {
        $i = $this->grid->currentRow;
        $row = $this->grid->data[$i];
        $this->control[$i] = clone $this->basecontrol; // clonning
        $value = $row[$this->index];
        $n = count($row);
        $href = $this->href;

        for ($r = 0; $r < $n; $r++)
        {
            $href = str_replace("#$r#", trim($row[$r]), $href);
        }

        $href = str_replace('#?#', $value, $href);
        $this->control[$i]->href = $href;
//        $this->control[$i]->action = $href;
        $this->control[$i]->label = $value;
        return $this->control[$i];
    }
}

class MGridControl extends MGridColumn
{
    public $control; // web control for the column

    public function __construct($control, $title = '', $align = 'left', $nowrap = false, $width = 0, $visible = true)
    {
        parent::__construct($title, $align, $nowrap, $width, $visible);
        $this->basecontrol = $control;
    }

    public function generate()
    {
        $i = $this->grid->currentRow;
        $row = $this->grid->data[$i];
        $this->control[$i] = clone $this->basecontrol; // clonning
        $name = $this->control[$i]->getName();

        //se o nome nÃ£o Ã© um array acrescenta os colchetes para tornÃ¡-lo um
        if (strpos($name, "[") === false && strpos($name, "]") === false)
        {
            $name .= "[$i]";
        }
        else
        {
            //posiÃ§Ã£o do caracter identificador, que serÃ¡ substituÃ­do
            $pos = strpos($name, '%');

            //se o nome estÃ¡ de acordo com as regras de nomenclatura do grid. Numero da linha entre %'s
            if (!$pos === false)
            {
                $rowNumber = substr($name, $pos + 1, -2);
                $name = str_replace("%$rowNumber%", trim($row[$rowNumber]), $name);
            }
        }

        $this->control[$i]->setName($name);
        $this->control[$i]->setId($name);
        $n = count($row);

        for ($r = 0; $r < $n; $r++)
        {
            $this->control[$i]->setValue(str_replace("%$r%", trim($row[$r]), $this->control[$i]->getValue()));
        }

        return $this->control[$i];
    }
}

class MGridAction extends MControl
{
    public $grid; // grid which this action belongs to
    public $type; // "text", "image", "select" or "none"
    public $alt; // image alt
    public $value; // image/text label for on
    public $valueoff; // image/text label for off
    public $href; // link pattern - replaces
    // #n# with value of column "n"
    // %n% with urlencode(value) of column "n"
    // $id with value of column "index"
    public $index; // deprecated
    public $enabled;

    public function __construct($grid, $type, $alt, $value, $href, $enabled = true, $index = null)
    {
        parent::__construct();
        $this->grid = $grid;
        $this->type = $type;
        $this->alt = $alt;

        if (is_array($value))
        {
            $this->value = $value[0];
            $this->valueoff = $value[1];
        }
        else
        {
            $this->value = $this->valueoff = $value;
        }

        $this->href = $href;
        $this->index = $index;
        $this->enabled = $enabled;
    }

    public function enable()
    {
        $this->enabled = true;
    }

    public function disable()
    {
        $this->enabled = false;
    }

    public function generateLink($row)
    {
        $index = $row[$this->grid->index];
        $href = preg_replace('/\$id/', $index, $this->href);
        $n = count($row);

        // substitute positional parameters
        for ($r = 0; $r < $n; $r++)
        {
            if( is_object($row[$r]) )
            {
                $row[$r] = $row[$r]->generate( );
            }
            $href = str_replace("%$r%", urlencode($row[$r]), $href);
            $href = str_replace("#$r#", urlencode($row[$r]), $href);
        }

        return (($this->grid->linktype == 'hyperlink') ? 'go:' : '') . $href;
    }

    public function generate()
    {
    }

    public function setTarget($target)
    {
        $this->target = $target;
    }

}

class MGridActionIcon extends MGridAction
{
    public $path;

    public function __construct($grid, $value, $href, $alt = null)
    {
        parent::__construct($grid, 'image', $alt, $value, $href);
        $this->path[true] = $this->manager->getUI()->getIcon("{$this->value}-on");
        $this->path[false] = $this->manager->getUI()->getIcon("{$this->value}-off");
    }

    public function generate()
    {
        $path = $this->path[$this->enabled];
        $class = "mGridActionIcon";
        if ($this->enabled)
        {
            $row = $this->grid->data[$this->grid->currentRow];
            $href = $this->generateLink($row);
            $img = $this->grid->getImage($this->value);
            $control = new MImageButton('', $this->alt, $href, $path);
        }
        else
        {
            $control = new MImage('', $this->alt, $path);
        }
        $control->setClass($class);
        return $control;
    }
}

class MGridActionText extends MGridAction
{
     
    public function __construct($grid, $value, $href)
    {
        parent::__construct($grid, 'text', null, $value, $href);
        $this->attributes = "width=\"20\" align=\"center\"";
    }

    public function generate()
    {
        $value = $this->value;

        if ($this->enabled)
        {
            $row = $this->grid->data[$this->grid->currentRow];
            $n = count($row);

            for ($r = 0; $r < $n; $r++)
            {
                $value = str_replace("%$r%", $row[$r], $value);
            }

            $href = $this->generateLink($row);
            $control = ($this->grid->linktype == 'hyperlink') ? new MLink('', $value, $href) : new MLink('', $value, $href);
            $control->setClass('mGridLink');
        }
        else
        {
            $control = new MSpan('', $value, 'mGridLinkDisable');
        }

        return $control;
    }
}

class MGridActionDefault extends MGridActionText
{
     
    public function generate()
    {
        if ($this->href == '#')
		{
			return '&nbsp;&nbsp;';
		}
		else
		{
			$control = parent::generate();
		    $control->setClass('mGridLinkActionDefault',false);
            $formId = $this->page->getFormId();
            return preg_match('/^HTTPS{0,1}\:\/\//i', $control->href) ? "javascript:miolo.doLinkButton('{$control->href}','','','$formId');" : $control->href;
        }
    }
}

class MGridActionDetail extends MGridActionIcon
{
    public function generate()
    {
        $class = "mGridActionIconDetail";
        $row = $this->grid->data[$this->grid->currentRow];
        $n = count($row);

        $href = $this->href;
        for ($r = 0; $r < $n; $r++)
        {
            $href = str_replace("%$r%", $row[$r], $href);
        }
        $href = str_replace("%r%", $this->grid->currentRow, $href);
        $hrefOn = str_replace("%s%", '1', $href);
        $hrefOff = str_replace("%s%", '0', $href);
        $controlOn = new MImage('', '', $this->path[true]);
        $controlOff = new MImage('', '', $this->path[false]);

        $hrefOn = "this.style.display = 'none'; dojo.byId('$controlOff->id').style.display = null; $hrefOn";
        $hrefOff = "this.style.display = 'none'; dojo.byId('$controlOn->id').style.display = null; $hrefOff";

        $controlOn->addAttribute('onclick',$hrefOn);
        $controlOn->setClass($class);

        $controlOff->addAttribute('onclick',$hrefOff); 
        $controlOff->setClass($class);
        $controlOff->addStyle('display', 'none');

        $control = new MDiv('',array($controlOn, $controlOff),'detail');
        return $control;
    }
}

class MGridActionSelect extends MGridAction
{
    
    public function __construct($grid, $index = 0)
    {
        parent::__construct($grid, 'select', null, null, null, true, $index);
    }

    public function generate()
    {
        $i = $this->grid->currentRow;
        $row = $this->grid->data[$i];
        $index = $row[$this->grid->index];
        $control = new MCheckBox("select".$this->grid->name."[$i]", $index, '');
        $control->addAttribute('onclick', "miolo.grid.check(this,'".$this->grid->name."[$i]"."');", false);
        return $control;
    }
}

class MGridHeaderLink extends MLink
{
    public function __construct($id, $label, $href)
    {
        parent::__construct($id, "[$label]", $href);
        $this->setClass('mGridLink');
    }
}

class MGridFilter extends MControl
{
    public $grid; // grid which this filter belongs to
    public $type; // "text", "selection"
    public $label; // image alt
    public $value; // image/text label for on/off
    public $index; // column index in the data array
    public $enabled;
    public $control;

    public function __construct($grid, $type, $label, $value, $index, $enabled = false)
    {
        parent::__construct();
        $this->grid = $grid;
        $this->type = $type;
        $this->label = $label;
        $this->index = $index;
        $this->enabled = $enabled;
        $this->control = null;
    }

    public function generate()
    {
        if ($this->enabled)
        {
            $array[] = new MSpan('', $this->label . '&nbsp;', 'mGridFont');
            $this->control->setValue(($this->grid->getFiltered()) ? $this->value : NULL);
            $array[] = $this->control->generate();
            $array[] = '&nbsp;&nbsp;&nbsp;';
        }
        return $array;
    }
}

class MGridFilterText extends MGridFilter
{
    public function __construct($grid, $label, $value = '', $index = 0, $enabled = false)
    {
        parent::__construct($grid, 'text', $label, $value, $index, $enabled);
        $this->control = new MTextField("mGridFilterText$index", $value, $label, 20);
        $this->value = $this->page->request($this->control->name) ? $this->page->request($this->control->name) : $value;
    }
}

class MGridFilterSelection extends MGridFilter
{
    
    public function __construct($grid,      $label, $options = array(
        ),               $index = 0, $enabled = false)
    {
        parent::__construct($grid, 'selection', $label, '', $index, $enabled);
        $this->control = new MSelection("mGridFilterSel$index", '', $label, $options);
        $this->value = $this->page->request($this->control->name) ? $this->page->request($this->control->name) : $value;
    }
}

class MGridFilterControl extends MGridFilter
{
    
    public function __construct($grid, &$control, $type = 'text', $index = 0, $enabled = false)
    {
        parent::__construct($grid, $type, $control->label, $control->value, $index, $enabled);
        $this->control = $control;
        $this->value = $this->page->request(
                           $this->control->name) ? $this->page->request($this->control->name) : $control->value;
    }
}

class MBaseGrid extends MContainerControl
{
}


class MGrid extends MBaseGrid
{
    public $title; // table display title
    public $filters; // array of grid filter controls
    public $filtered; // is filtered?
    public $filter; // show/hide filters 
    public $orderby; // base column to sort
    public $ordered; // is ordered?
    public $order;

    /**
     * @var string Mask for sorting date values of the current ordered column.
     */
    public $orderMask;

    public $lastorderby;
    public $data; // table data cells
    public $actions; // array with actions controls
    public $select; // a column for select action
    public $showid; // show ids or not?
    public $columns; // array with columns
    public $icons; // action icons
    public $errors; // array of errors
    public $pageLength; // max number of rows to show - 0 to all rows
    public $rowCount; // total number of rows
    public $href; // grid url
    public $pn; // gridnavigator
    public $headerLinks; // array of headerlinks
    public $linktype; // hyperlink or linkbutton (forced post)
    public $width; // table width for the grid
    public $rowmethod; // method to execute (callback) at each row
    public $index; // the column to act as index of grid
    public $controls;
    public $emptyMsg;
    public $currentRow; // index of row to renderize
    public $box;
    public $selecteds;
    public $allSelecteds;
    public $pageNumber;
    public $prevPage;
    public $name;
    public $css;
    public $footer;
    public $hasDetail;
	var $actionDefault;
    public $alternateColors;
    public $buttonSelectClass;
    protected $isShowHeaders= true;
    protected $scrollable   = false;
    protected $scrollWidth  = '99%';
    protected $scrollHeight = '99%';

    /**
     * @var integer Current offset. It is defined only when setQuery method is used.
     */
    private $offset = NULL;

/*    
      Grid constructor
         $data - the data array
         $columns - array of columns objects
         $href - base url of this grid
         $pageLength - max number of rows to show (0 to show all)
*/    
    /**
     * Grid constructor.
     *
     * @param array $data Data array.
     * @param array $columns Columns array.
     * @param string $href Base URL to use in pagination.
     * @param integer $pageLength Number of items per page.
     * @param integer $index The column which acts as grid index.
     * @param string $name Grid name.
     * @param boolean $useCheckBoxes Whether to use check boxes. 
     */
    public function __construct($data, $columns, $href=NULL, $pageLength=15, $index=0, $name='', $useCheckBoxes=TRUE)
    {
        parent::__construct(NULL);
        $this->setName($name);
        $this->setColumns($columns);
        $this->href = $href ? $href : $this->manager->getCurrentURL();
        $this->pageLength = $pageLength;
        $this->headerLinks = array();
        $this->width = '';
        $this->setLinkType('linkbutton');
        $this->box = new MBox('', 'backContext', '');
        $this->rowmethod = null;
        $this->data = $data;
        $this->index = $index;
        $this->emptyMsg = _M("No records found!");
        $this->data = $data;
        $this->rowCount = count($this->data);
        $this->controls = array();
        $this->select = NULL;
        $this->hasDetail = false;

        if ( urldecode($this->page->request('gridName')) == $this->name )
        {
            $this->page->setViewState('pn_page', $this->page->request('pn_page'), $this->name);
        }

        $this->pageNumber = MUtil::NVL($this->page->getViewState('pn_page', $this->name), '1');
        $this->prevPage = MUtil::NVL($this->page->getViewState('grid_page', $this->name), '1');

        $this->page->setViewState('grid_page', $this->pageNumber, $this->name);
        $this->selecteds = array();
        $this->allSelecteds = array();
        $this->actionDefault = new MGridActionDefault($this, '&nbsp;&nbsp;', NULL);
        $this->alternateColors = true;
        $this->buttonSelectClass = 'linkbtn';
        $this->currentRow = 0;

        $this->setUseSelecteds($useCheckBoxes);

        $this->handlerSelecteds();
        $this->page->addScript('m_grid.js');
    }

    public function setShowHeaders( $show=true )
    {
        $this->isShowHeaders = $show;
    }

    public function showHeaders( )
    {
        return $this->isShowHeaders;
    }

    public function setCurrentPage($pageNumber)
    {
        $this->pageNumber = $pageNumber;
        $this->page->setViewState('grid_page', $pageNumber, $this->name);
        $this->prevPage = MUtil::NVL($this->page->setViewState('grid_page', $this->name),'1');
    }

    public function getURL($filter = false, $order = false, $item = '')
    {
        $url = $this->href;
        $url = preg_replace("/&pn_page=(.*)[^&]/i", "", $url);
        $url = preg_replace("/&__filter=(.*)[^&]/i", "", $url);
        $url = preg_replace("/&orderby=(.*)[^&]/i", "", $url);
        $url .= ($filter) ? "&__filter=1" : "&__filter=0";

        if ( $order )
        {
            $url .= "&orderby={$this->orderby}&order={$this->order}&lastorderby={$this->lastorderby}&orderMask={$this->orderMask}";
        }

        if ( $item )
        {
            $url .= $item;
        }

        return $url;
    }

    public function setTitle($title)
    {
        $this->caption = $this->title = $title;
    }

    public function setPageLength($pageLength)
    {
        $this->pageLength = $pageLength;
    }

    public function getPageLength()
    {
        return $this->pageLength;
    }

    public function setFooter($footer)
    {
        $this->footer = $footer;
    }

    public function setColumns($columns)
    {
        $this->columns = array();

        if (!is_array($columns))
            $columns = array($columns);

        foreach ($columns as $k => $c)
        {
            $this->columns[$k] = $c;
            $this->columns[$k]->index = $k;
            $this->columns[$k]->grid = $this;
        }
    }

    public function setLinkType($linktype)
    {
        $this->linktype = strtolower($linktype);
    }

    public function setControls($controls)
    {
        if (!is_array($controls))
        {
            $controls = array($controls);
        }
        $this->controls = array_merge($this->controls, $controls);
    }

    public function setButtons($aButtons) //backward compatibility
    {
        $this->setControls($aButtons);
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function setIndex($index)
    {
        $this->index = $index;
    }
    
    public function setRowMethod($class, $method)
    {
        $this->rowmethod = array($class,$method);
    }

    public function setIsScrollable($scrollable=true, $width='99%', $height='99%')
    {
        $this->scrollable   = $scrollable;
        $this->scrollWidth  = $width;
        $this->scrollHeight = $height;
    }

    public function setScrollWidth($width='99%')
    {
        $this->scrollWidth = $width;
    }

    public function setScrollHeight($height='99%')
    {
        $this->scrollHeight = $height;
    }

    public function headerLink($id, $label, $href)
    {
        $this->headerLinks[$id] = new MGridHeaderLink($id, $label, $href);
    }

    public function setColumnAttr($col, $attr, $value)
    {
        $this->columns[$col]->$attr = $value;
    }

    public function setButtonSelectClass($class='')
    {
        $this->buttonSelectClass = $class;
    }

    public function setAlternate($status = true)
    {
        $this->alternateColors = $status;
    }

    public function setData($data)
    {
        $this->data = $data;
        $this->rowCount = count($this->data);
    }

    public function getData()
    {
        return $this->data;
    }

    public function getDataValue($row, $col)
    {
        return $this->data[$row][$col];
    }

    public function getPage()
    {
        if ( count($this->data) && is_array($this->data) )
        {
            return array_slice($this->data, $this->pn->idxFirst, $this->pn->gridCount);
        }
    }

    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    public function getPrevPage()
    {
        return $this->prevPage;
    }

    public function getCurrentRow()
    {
        return $this->currentRow;
    }

    public function setActionDefault($href)
    {
        $this->actionDefault = new MGridActionDefault($this, '&nbsp;&nbsp;', $href);
    }

	function addActionSelect()
    {
        $this->select = new MGridActionSelect($this);
    }
     
    public function addActionIcon($alt, $icon, $href, $index = 0)
    {
        if ($p = strpos($icon,'.')) $icon = substr($icon,0,$p);
        $this->actions[] = new MGridActionIcon($this, $icon, $href, $alt);
    }

    public function addActionText($alt, $text, $href, $index = 0)
    {
        $this->actions[] = new MGridActionText($this, $text, $href);
    }

    public function addActionUpdate($href)
    {
//        $this->addActionIcon('Editar', array('button_edit.png', 'button_noedit.png'), $href);
        $this->addActionIcon(_M("Edit"), 'edit', $href);
    }

    public function addActionDelete($href)
    {
//        $this->addActionIcon('Excluir', array('button_drop.png', 'button_noempty.png'), $href);
        $this->addActionIcon(_M("Delete"), 'delete', $href);
    }

    public function addActionDetail($href)
    {
        $this->hasDetail = true;
        $this->actions[] = new MGridActionDetail($this, 'detail', $href);
    }

    public function addFilterSelection($index, $label, $options, $value = '')
    {
        $f = new MGridFilterSelection($this, $label, $options, $index, $this->getFilter());
        $this->filters[$index] = $f;
    }

    public function addFilterText($index, $label, $value = '')
    {
        $f = new MGridFilterText($this, $label, $value, $index, $this->getFilter());
        $this->filters[$index] = $f;
    }

    public function addFilterControl($index, $control, $type = 'text')
    {
        $this->filters[$index] = new MGridFilterControl($this, $control, $type, $index, $this->getFilter());
    }

    public function getFilterValue($index)
    {
        return $this->filters[$index]->value;
    }

    public function getFilterControl($index)
    {
        return $this->filters[$index]->control;
    }

    public function setFiltered($value = false)
    {
        $this->filtered = $value;
    }

    public function getFiltered()
    {
        if (($f = $this->page->request('__filter')) != '')
        {
            $this->filtered = ($f == '1');
        }
        return $this->filtered;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function setFilter($status)
    {
        $this->filter = $status;

        if ($this->filters)
        {
            foreach ($this->filters as $k => $f)
            {
                $this->filters[$k]->enabled = $status;
            }
        }
    }

    public function applyFilter()
    {
        if ($this->filters)
        {
            foreach ($this->filters as $f)
            {
                $value[$f->index] = $f->value;
            }

            foreach ($this->data as $row)
            {
                $ok = true;

                foreach ($value as $k => $v)
                {
                    $n = strlen(trim($v));
                    $ok = $ok && (strncmp($row[$k], $v, $n) == 0);
                }

                if ($ok)
                    $data[] = $row;
            }
            $this->data = $data;
            $this->rowCount = count($this->data);
        }
    }

    public function applyOrder($column)
    {
        $this->lastorderby = $lastOrderBy = $this->page->request('lastorderby');
        $this->order = $this->page->request('order');
        $this->orderMask = $this->page->request('orderMask');

        if ( !$this->page->request('m_pagenavigating') )
        {
            if ( isset($this->lastorderby) && $this->lastorderby == $this->orderby )
            {
                if ( $this->order == 'ASC' )
                {
                    $this->order = 'DESC';
                }
                elseif ( $this->order == 'DESC' )
                {
                    $this->order = 'ASC';
                }
                // Set the default order and also prevent the use of other values
                else
                {
                    $this->order = 'ASC';
                }
            }
            elseif ( $this->lastorderby != $this->orderby )
            {
                if ( !$this->order && $this->orderby === '0' )
                {
                    $this->order = 'DESC';
                }
                else
                {
                    $this->order = 'ASC';
                }

                $this->lastorderby = $this->orderby;
            }
        }
        elseif ( !$this->order )
        {
            $this->lastorderby = $this->orderby;
            $this->order = 'ASC';
        }

        $p = $this->columns[$column]->index;

        // Date ordering
        if ( $this->orderMask )
        {
            if ( $lastOrderBy !== NULL )
            {
                $sorter = new MSort($this->order, $lastOrderBy, $this->columns[$lastOrderBy]->orderMask);
                usort($this->data, array($sorter, 'compareDate'));
            }

            $sorter = new MSort($this->order, $p, $this->orderMask);
            usort($this->data, array($sorter, 'compareDate'));
        }
        else
        {
            $n = count($this->data[0]);

            foreach ( $this->data as $key => $row )
            {
                for ( $i = 0; $i < $n; $i++ )
                {
                    $arr[$i][$key] = $row[$i];
                }
            }

            $order = $this->order == 'DESC' ? 'SORT_DESC' : 'SORT_ASC';

            $sortcols = "\$arr[$p], $order";

            for ($i = 0; $i < $n; $i++)
            {
                if ($i != $p)
                {
                    $sortcols .= ",\$arr[$i], $order";
                }
            }

            eval("array_multisort($sortcols);");

            $this->data = array();

            for ($i = 0; $i < $n; $i++)
            {
                foreach ($arr[$i] as $key => $row)
                {
                    $this->data[$key][$i] = $row;
                }
            }
        }

        $this->page->setViewState('orderby', $this->orderby, $this->name);
        $this->page->setViewState('lastorderby', $this->lastorderby, $this->name);
        $this->page->setViewState('order', $this->order, $this->name);
    }

    public function addError($err)
    {
        if ($err)
        {
            if (is_array($err))
            {
                if ($this->errors)
                {
                    $this->errors = array_merge($this->errors, $err);
                }
                else
                {
                    $this->errors = $err;
                }
            }
            else
            {
                $this->errors[] = $err;
            }
        }
    }
     
    public function showID($state)
    {
        $this->showid = $state;
    }

    public function setClose($action)
    {
        $this->box->setClose($action);
    }

    public function setSelecteds($s)
    {
        $selecteds = $this->page->getViewState("selecteds",$this->name);
        $selecteds[$this->pageNumber] = $s;
        $this->page->setViewState("useselecteds", true,$this->name);
        $this->page->setViewState("selecteds",$selecteds,$this->name);
    }

    public function setUseSelecteds($opt)
    {
        $this->page->setViewState("useselecteds", $opt,$this->name);
    }

    public function handlerSelecteds()
    {
        $selecteds = $this->page->getViewState("selecteds",$this->name);
        $useSelecteds = $this->page->getViewState("useselecteds",$this->name);
/*
        if (urldecode($this->page->request('gridName')) == $this->name)
        {
            $this->page->setViewState("pn_page", $this->page->request('pn_page'),$this->name);
        }
        $this->pageNumber = MUtil::NVL($this->page->getViewState("pn_page",$this->name),1);
        $this->prevPage   = MUtil::NVL($this->page->getViewState("grid_page",$this->name),1);
*/
        $this->selecteds = array();

        if ($useSelecteds)
        {
            $selecteds[$this->prevPage] = array();
            if ($select = $this->page->request('select'.$this->name))
            {
                foreach($select as $k=>$v)
                {
                    $selecteds[$this->prevPage][] = $k;
                }
            }
            if (is_array($selecteds[$this->pageNumber]))
            {
                $this->selecteds = $selecteds[$this->pageNumber];
            }
            $this->allSelecteds = $selecteds;
        }

        $this->page->setViewState("grid_page", $this->pageNumber,$this->name);

        $this->page->setViewState("useselecteds", $useSelecteds,$this->name);
        $this->page->setViewState("selecteds",$selecteds,$this->name);
    }

    public function clearSelecteds()
    {
        $this->page->setViewState("selecteds",NULL,$this->name);
    }

    public function generateTitle()
    {
        if ($this->caption != '')
        {
            $this->box->setCaption($this->caption);
            return $this->box->boxTitle->generate();
        } 
    }

    public function generateNavigationHeader()
    {
        if (!$this->pn)
        {
            return null;
        }

        $d1 = $this->pn->getPageLinks();
        $d2 = $this->pn->getPageImages();
        $d = new MDiv('', array($d1, $d2), 'mGridNavigation');
        return $d;
    }

    public function generateNavigationFooter()
    {
        if (!$this->pn)
        {
            return null;
        }

        $d1 = $this->pn->getPageLinks();
        $d2 = $this->pn->getPageImages();
        $d = new MDiv('', array($d1, $d2), 'mGridNavigation');
        return $d;
    }

    public function generateLinks()
    {
        if (!count($this->headerLinks))
        {
            return NULL;
        } 

        foreach ($this->headerLinks as $link)
        {
            $link->float = 'left';
        }

        $div = new MDiv('', $this->headerLinks, 'mGridHeaderLink');
        return $div;
    }

    public function generateControls()
    {
        if (!count($this->controls))
        {
            return NULL;
        }

        $i = 0;

        foreach ($this->controls as $c)
        {
            $array[$i++] = $c->generate();
            $array[$i++] = '&nbsp;&nbsp;';
        }

        return new MDiv('', $array, 'mGridcontrols');
    }

    public function generateFilter()
    {
        if (!$this->filter)
        {
            return null;
        }
 
        foreach ($this->filters as $k => $f)
        {
            $array[] = $f->generate();
        }

        $img = new MImageButton('', _M("Filter"), $this->getURL(true, $this->ordered), "images/button_select.png");
        $array[] = $img->generate();
        $array[] = '&nbsp;&nbsp;';
        $img = new MImageButton('', _M("Remove Filter"), $this->getURL(false, $this->ordered), "images/button_browse.png");
        $array[] = $img->generate();
        return new MDiv('', $array, 'mGridFilter');
    }

    public function hasErrors()
    {
        return count($this->errors);
    }

    public function generateErrors()
    {
        $MIOLO = MIOLO::getInstance();

        $caption = ('Erros');

        $t = new MSimpleTable('');
        $t->setAttributes(
            "class=\"m-prompt-box-error\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\"  border=\"0\"");
        $t->attributes['cell'][0][0] = "colspan=\"2\" class=\"m-prompt-box-error-title\"";
        $t->cell[0][0] = $caption;
        $t->attributes['cell'][1][0] = "valign=\"top\" width=\"60\"";
        $t->cell[1][0] = new ImageForm('', '', 'images/error.gif');
        $t->attributes['cell'][1][1] = "class=\"m-prompt-box-error-text\"";
        $leftmargin = '&nbsp;&nbsp;&nbsp;&nbsp;';

        foreach ($this->errors as $e)
        {
            $msg .= $leftmargin . "-&nbsp;$e<br>";
        }

        $t->cell[1][1] = $msg;
        return $t;
    }

    public function generateHeader()
    {
        $header[] = $this->generateFilter();
        if ($this->data)
        {
            $header[] = $this->generateNavigationHeader();
        }
        $header[] = $this->generateLinks();

        return $header;
    }

    public function generateColumnsHeading(&$tbl)
    {
        $spanClass = ''; // adjusted via javascript
        $p = 0;
        $this->page->onLoad("miolo.grid.ajustSelect('linkbtn');"); 
        $this->page->onLoad("miolo.grid.ajustTHead();"); 
        $tbl->setColGroup($p);
        $span = new MSpan('','&nbsp;',$this->buttonSelectClass);
        $tbl->setHead($p, $span ); 
        $tbl->setHeadClass($p++, 'btn'); 
        if ($n = count($this->actions))
        {
            $tbl->setColGroup($p,"span={$n}");
            $tbl->setHead($p,new MSpan('',_M('Action'),$spanClass));
            $tbl->setHeadAttribute($p,'colspan',$n);
            $tbl->setHeadClass($p++,'action');
        }
        if ($this->select != NULL)
        {
            $rowCount = count($this->data);
            $this->page->onLoad("miolo.grid.checkEachRow($rowCount,'".$this->name."');");
            $tbl->setColGroup($p);
            $check = new MCheckBox("chkAll", 'chkAction', '');
            $check->addAttribute('onclick',"miolo.grid.checkAll(this,$rowCount,'".$this->name."');");
            $check->_addStyle('padding','0px');
            $tbl->setHead($p,new MSpan('',$check,'select'));
            $tbl->setHeadClass($p++,'select');
        }

        // generate column headings
        $tbl->setColGroup($p); $c = 0;
        $last = count($this->columns) - 1;
        foreach ($this->columns as $k => $col)
        {
            if ((!$col->visible) || (!$col->title))
            {
                continue;
            }

            if ($col->order)
            {
                $this->orderby = $k;
                $this->orderMask = $col->orderMask;
                $action = $this->getURL($this->filtered, true);
                $link = new MLinkButton('', $col->title, $action);
                $link->setClass('order');

                $img = NULL;
                if ( $this->page->request('orderby') == $k )
                {
                    $imgId = "{$this->name}_orderImage$k";

                    if ( $this->order == 'DESC' )
                    {
                        $img = new MImage($imgId, NULL, 'images/grid_order_desc.png');
                    }
                    else
                    {
                        $img = new MImage($imgId, NULL, 'images/grid_order_asc.png');
                    }
                    
                    $img->addStyle('line-height', '25px');
                }

                $colTitle = new MSpan('', array($link, $img), $spanClass);
                $tbl->setHeadClass($p+$c,'order');
            }
            else
            {
                $colTitle = new MSpan('',$col->title,$spanClass);
                $tbl->setHeadClass($p+$k,'data');
            }

            if (($col->width))
            {
                $attr =  ($k != $last) ? " width=\"$col->width\"" : " width=\"100%\"";
            }
            else
            {
                // scrollable tables need col width
                if( $this->scrollable )
                {
                    $this->manager->logMessage( _M("[WARNING] Using scrollable table, it's necessary to inform column width. ") );
                }
            }

            $tbl->setColGroupCol($p,$c,$attr);
            $tbl->setHead($p+$c++,$colTitle);
        }
    }

    // This method corrects a problem when using scrollable table having
    // only one or few records. Adds a colspaned row.
    public function correctActionColSpan($tbl)
    {
        if ($cntActions = count( $this->actions) )
        {
            $tbl->attributes['cell'][0][0] = "width=\"15\" align=\"left\" colspan=$cntActions";
            $tbl->cell[0][0] = '';
        }
    }

    public function generateActions(&$tbl)
    {
        $i = $this->currentRow;

        if ($this->hasDetail)
        {
           $i += $this->currentRow;
        }
        $c = 0; // colNumber

        $spanClass = ($this->select != NULL) ? ' tall' : '';
        $control = new MSpan('', '&nbsp;');
        if ($this->actionDefault->href)
        {
            $control->addAttribute('onclick',$this->actionDefault->generate());
        }
        $control->setClass($this->buttonSelectClass,false);
        $tbl->setCell($i, $c, $control); 
        $tbl->setCellClass($i, $c, 'btn'); 

        if ($this->hasDetail)
        {
           $tbl->setCell($i+1,$c,new MDiv('ddetail' . $this->currentRow, NULL));
           $tbl->setCellClass($i+1,$c,'action-default');
        }
        $c++; 
        if ($n = count($this->actions))
        {
            // generate action links
            while ($c < ($n + 1))
            {
				$action = $this->actions[$c-1]; 
                $tbl->setCell($i,$c,$action->generate(),$action->attributes());
                if ($this->hasDetail)
                {
                    $tbl->setCell($i+1,$c,new MDiv('adetail' . $this->currentRow, NULL));
                }
                $tbl->setCellClass($i,$c,$c == ($n) ? 'data action' : 'action');
                $c++; 
            }
        }

        if ($this->select != NULL)
        {
            $tbl->setRowAttribute($i,'id',"row".$this->name."[{$this->currentRow}]");
            $tbl->setCellClass($i,$c,'data select');
            $select = $this->select->generate();
            $select->checked = (array_search($i, $this->selecteds) !== false);
            $tbl->cell[$i][$c] = $select;
            if ($this->hasDetail)
            {
                $tbl->setCell($i+1,$c,new MDiv('sdetail' . $this->currentRow, NULL));
            }
        }
    }

    public function generateColumnsControls()
    {
        foreach ($this->columns as $k => $col)
        {
            $col->generate();
        }
    }

    public function generateColumns(&$tbl)
    {
        $i = $this->currentRow;
        if ($this->hasDetail)
        {
           $i += $this->currentRow;
        }
        $p = count($this->actions) + 1;

        if ($this->select != NULL)
            $p++;

        $colspan = 0;
        $first = $p;

        foreach ($this->columns as $k => $col)
        {
            if ((!$col->title) || (!$col->visible))
            { 
                continue;
            }

            ++$colspan;

            $control = $col->control[$this->currentRow];
            $attr = "";

            if ($col->nowrap)
            {
                $tbl->setCellAttribute($i,$p,"nowrap");
            }

            if ($col->width)
            {
//                $tbl->setCellAttribute($i,$p,"width",$col->width);
            }

            if ($col->align)
            {
                $tbl->setCellAttribute($i,$p,"align",$col->align);
            }
            $class = $col->getClass();
            $tbl->setCellClass($i,$p,$class == '' ? 'data' : $class);
            $tbl->setCell($i,$p++,$control);
        }
        if ($this->hasDetail)
        {
            $tbl->setCell(++$i,$first,new MDiv('detail' . $this->currentRow, NULL));
            $tbl->setCellAttribute($i,$first,"colspan",$colspan);
            $tbl->setRowClass($i,'detail');
        }
    }

    public function generateEmptyMsg()
    {
        $div = new MDiv('', $this->emptyMsg, 'mGridAttention');
        return $div;
    }

    public function generateData()
    {
        if (!$this->data)
        {
            return;
        }

        $this->orderby = $this->page->request('orderby');
        $this->ordered = isset($this->orderby);

        if ( $this->ordered && is_null($this->offset) )
        {
            $this->applyOrder($this->orderby);
        }

        if ($this->getFiltered())
        {
            $this->applyFilter();
        }
        if ($this->pageLength)
        {
            $this->pn = new MGridNavigator($this->pageLength, $this->rowCount,
                                          $this->getURL($this->filtered, $this->ordered), $this);

            if ( is_null($this->offset) )
            {
                $this->data = $this->getPage();
            }
        }
        else
            $this->pn = null;
    }

    public function callRowMethod()
    {
        if (isset($this->rowmethod))
        {

            $i = $this->currentRow;
            $row = $this->data[$i];
            call_user_func($this->rowmethod, $i, $row, $this->actions, $this->columns);
        }
    }
    
    public function generateBody()
    {
        global $SCRIPT_NAME;
        $MIOLO = MIOLO::getInstance();

        if ($this->hasErrors())
        {
            $this->generateErrors();
        }

        $tblData = new MSimpleTable('', "cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" class=\"mGridBody\"");
        $this->generateColumnsHeading($tblData);

        if ($this->data)
        {
            // generate data rows
            $i = 0;

            foreach ($this->data as $row) // foreach row
            {
                $this->currentRow = $i;
                $rowId = ($i % 2) + 1;
                $rowClass = $this->alternateColors ? "row$rowId" : "row0";
                $c = $this->hasDetail ? $i+$this->currentRow : $i;
                $i++;
                $tblData->setRowClass($c,$rowClass);
                $this->generateColumnsControls();
                $this->callRowMethod();
                $this->generateActions($tblData);
                $this->generateColumns($tblData);
            } // end foreach row
        }// end if

        $tblData->setRowAttribute(0, "id", 'tbody'.$this->id.'first');

        if( $this->scrollable )
        {
            $bodyHeader = new MDiv('head'.$this->id, $tblDataHeader, 'mGridHead', 'style="'.
                                                       'width:'.$this->scrollWidth.';'.
                                                       'overflow-x:hidden;"');
            $body = new MDiv('body'.$this->id, $tblData,'mGridBody','style="'.
                                                       'width:'.$this->scrollWidth.';'.
                                                       'height:'.$this->scrollHeight.';'.
                                                       'overflow:auto;" '
                                                    );
            $body = new MDiv('', array($bodyHeader, $body) );
        }
        else
        {
            $body = $tblData;
        }

        return $body;
    }

    public function generateFooter()
    {
        $footer = is_array($this->footer) ? $this->footer : array($this->footer);

        if ( ! $this->data )
        {
            $footer[] = $this->generateEmptyMsg();
        }

        if ( $this->data )
        {
            $footer[] = $this->generateNavigationFooter();
        }

        $footer[] = $this->generateControls();

        return $footer;
    }

    public function getImage($src)
    {
        $url = $this->icons[$src];
        if (!$url)
        {
            if (substr($src, 0, 1) == '/' || substr($src, 0, 5) == 'http:')
            {
                $url = $src;
            }
            else
            {
                $file = $this->manager->getConf('home.themes')  . '/' . $this->manager->getConf('theme.main')  . '/images/' . $src;
                if (file_exists($file))
                {
                    $url = $this->manager->getUI()->getImageTheme($this->manager->getConf('theme.main'), $src);
                }
                else
                {
                    $url = $this->manager->getUI()->getImage('', $src);
                }
            }

            $this->icons[$src] = $url;
        }
        return $url;
    }

    public function generate()
    {
        $this->generateData();
        $header = $this->painter->generateToString($this->generateHeader());
        $title  = $this->painter->generateToString($this->generateTitle());
        $body   = $this->painter->generateToString($this->generateBody());
        $footer = $this->painter->generateToString($this->generateFooter());
        $this->setInner( array($title, $header, $body, $footer) );
        $this->setClass('mGrid');
        if ($this->width != '')
        {
            $this->addStyle('width', $this->width);
        }
        return parent::generate();
    }

    /**
     * @return boolean Return whether the grid has an offset (returns true only when the setQuery method is used).
     */
    public function hasOffset()
    {
        return $this->offset != NULL;
    }
    
    
     /**
     * Corta textos muito grandes, adaptando-os ao tamanho da grid e evitando distorções.
     * @param array $this->data.
     * @return array editada.
     */
     function cutLongString($elemento)
     {
         //reduz o tamanho de cada elemento da array
         
         if ($elemento != '')
         {
            foreach ($elemento as $key => $e)
            {

               unset($subRetorno);
               foreach (array_keys($e) as $key2)
               {
                   if (strlen($e[$key2]) > 60)
                   {
                       $aux = substr($e[$key2], 0, 60);  
                       $subRetorno[] = $aux. '...';  
                   }
                   else
                   {
                       $subRetorno[] = $e[$key2];
                   }
               } 

               $retorno[$key] = $subRetorno;
            }
         }   
         
        return $retorno;  
     }

    

    /**
     * Define the SQL to let grid manage the database queries.
     * Use this to make the database query use LIMIT and OFFSET.
     *
     * @global object $state MState instance.
     * @param string $sql SQL query string.
     * @param string $dbconf Database configuration name.
     */
    public function setQuery($sql, $dbconf)
    {
        if ( $sql == '' )
        {
            return;
        }
        
        $MIOLO = MIOLO::getInstance();
        $db = $MIOLO->getDatabase($dbconf);

        $lastSQL = $this->page->getViewState('lastSQL', $this->name);
        
        if ( $sql instanceof MSQL )
        {            
            if ( $this->page->request('orderby') || $this->page->request('orderby') === '0' )
            {                                
                $this->getOrder();
                $sql->clearOrderBy();
                $sql->setOrderBy($this->orderby + 1 . " " . $this->order);
            }
            
            $this->orderby = $sql->getOrderBy();
            
            $sql = $sql->select();
        }
        else
        {
            $this->getOrder();

            if ( !substr_count(strtolower($sql), 'order by') )
            {
                $column = $this->orderby + 1;
                $orderby = "ORDER BY $column $this->order";            
            }
        }

        // Calculate the current offset
        $this->offset = ($this->pageNumber - 1) * $this->pageLength;

        // Get the data from the current offset until the page length
        $sqlWithLimit = "$sql $orderby LIMIT $this->pageLength OFFSET $this->offset";
        $query = $db->query($sqlWithLimit);
        $this->data = is_array($query) ? $query : $query->result;

        //reduz o tamanho da string, caso ela estrapole o tamanho esperado da coluna
        $this->data = $this->cutLongString($this->data);
    
        $this->rowCount = $this->page->getViewState('rowCount', $this->name);
        
        if ( MIOLO::_REQUEST('__EVENTTARGETVALUE') || !$this->rowCount || $lastSQL != $sql )
        {
            // Prepare the same SQL to count the registers without limit
            $query = $db->query("SELECT COUNT(*) FROM ($sql) AS MGRID_COUNT");
            $this->rowCount = is_array($query) ? $query[0][0] : $query->result[0][0];


            // Store the rowCount to make the query count only once
            $this->page->setViewState('rowCount', $this->rowCount, $this->name);

        }
        
        if ( ( $this->pageNumber - 1) > ($this->rowCount / $this->pageLength) )
        {
            $this->pageNumber = round(($this->rowCount / $this->pageLength), 0, PHP_ROUND_HALF_UP);
            $this->page->setViewState('pn_page', $this->pageNumber, $this->name);
            $this->page->setViewState('grid_page', $this->pageNumber, $this->name);
            $this->prevPage = MUtil::NVL($this->page->setViewState('grid_page', $this->name), $this->pageNumber - 1);
        }

        $this->page->setViewState('lastSQL', $sql, $this->name);
        $this->page->setViewState('orderby', $this->orderby, $this->name);
        $this->page->setViewState('lastorderby', $this->lastorderby, $this->name);
        $this->page->setViewState('order', $this->order, $this->name);
    }
    
    private function getOrder()
    {
        $this->orderby = $this->page->request('orderby');
        $this->lastorderby = $this->page->getViewState('lastorderby', $this->name);
        $this->order = $this->page->getViewState('order', $this->name);

        if ( !$this->page->request('m_pagenavigating') )
        {
            if ( isset($this->lastorderby) && $this->lastorderby == $this->orderby )
            {
                if ( $this->order == 'ASC' )
                {
                    $this->order = 'DESC';
                }
                elseif ( $this->order == 'DESC' )
                {
                    $this->order = 'ASC';
                }
                // Set the default order and also prevent the use of other values
                else
                {
                    $this->order = 'ASC';
                }
            }
            elseif ( $this->lastorderby != $this->orderby )
            {
                if ( !$this->order && $this->orderby === '0' )
                {
                    $this->order = 'DESC';
                }
                else
                {
                    $this->order = 'ASC';
                }

                $this->lastorderby = $this->orderby;
            }
        }
        elseif ( !$this->order )
        {
            $this->lastorderby = $this->orderby;
            $this->order = 'ASC';
        }

        if ( $this->orderby === NULL || $this->orderby === '' )
        {
            $this->orderby = 0;
        }
    }
}
?>
