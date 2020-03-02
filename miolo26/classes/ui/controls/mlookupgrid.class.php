<?php
class MLookupGrid extends MGrid
{
    /**
        Base class to Lookup Grids

        $href - base url of this lookupgrid
        $pageLength - max number of rows to show (0 to show all)
        $index - columns acting as index
    */
    
    public $href;
    public $pageLength;
    public $index;
    public $emptyMsg;

    public function __construct($columns, $href, $pageLength = 15, $index = 0)
    {
        parent::__construct(NULL,$columns,$href, $pageLength = 15, $index = 0);
        $this->emptyMsg = _M('No records found!');
        $this->setFiltered(true);
        $filtered = $this->getFiltered();
        $this->pn = new MGridNavigator($this->pageLength, $this->rowCount, $this->getURL($filtered, $this->ordered), $this);
        $this->setClass('mLookupGrid', true);
    }

    public function generateTitle()
    {
        $divs[] = new MDiv(NULL, $this->title, 'mPopupTitle');
        $divs['close'] = new MDiv(NULL, NULL, 'mPopupClose');
        $divs['close']->addAttribute('onclick', "miolo.getWindow('{$this->formName}').close();");

        return new MDiv(NULL, $divs, 'mBoxTitle mPopupTitleDiv');
    }

    public function generateFilter()
    {
        $MIOLO = MIOLO::getInstance();
        $page = $MIOLO->getPage();

        if (!$this->filter)
            return null;

        foreach ($this->filters as $k => $f)
        {
            $array[] = $f->generate();
        }

        $url = $this->getURL(true, $this->ordered);
        $url = str_replace('&amp;','&', $url);
        $formId = $this->page->getFormId();
        $event = "miolo.doLinkButton('$url','','', '$formId');";

        $array[] = $filterButton = new MImageButton('', _M('Filter'), "javascript: $event", "images/button_select.png");
        $filterButton->href = NULL;

        $this->page->onLoad("miolo.registerEvent('{$formId}', 'keypress', \"if (event.keyCode==dojo.keys.ENTER) { event.preventDefault();{$event};}\",false);");

        return new MDiv('', $array, 'mGridFilter');
    }

    public function generateHeader()
    {
        $header[] = $this->generateFilter();
        return $header;
    }

    public function generateFooter()
    {
        if (!$this->data)
        {
            $footer[] = $this->generateEmptyMsg();
        }
        $footer[] = $this->generateNavigationFooter();
        if ( $this->controls )
        {
            $footer[] = $this->generateControls();
        }
        return $footer;
    }


    /**
     * Overrides the method which draws the grid body, for being able to select
     * a register by clicking over the line
     */
    public function generateBody()
    {
        global $SCRIPT_NAME;
        $MIOLO = MIOLO::getInstance();

        if ( $this->hasErrors() )
        {
            $this->generateErrors();
        }

        $tblData = new MSimpleTable('', "cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" class=\"mGridBody\"");
        $this->generateColumnsHeading($tblData);

        if ( $this->data )
        {
            $i = 0;

            foreach ( $this->data as $row )
            {
                $this->currentRow = $i;
                $rowId = ($i % 2) + 1;
                $rowClass = $this->alternateColors ? "row$rowId" : "row0";
                $c = $this->hasDetail ? $i + $this->currentRow : $i;
                $i++;
                $tblData->setRowClass($c, $rowClass);

                // adds the default onclick action on <tr> element
                $tblData->setRowAttribute($i - 1, 'onclick', $this->actionDefault->generate());
                $tblData->setRowAttribute($i - 1, 'style', 'cursor: pointer;');

                $this->generateColumnsControls();
                $this->callRowMethod();
                $this->generateActions($tblData);
                $this->generateColumns($tblData);
            }
        }

        $tblData->setRowAttribute(0, "id", 'tbody' . $this->id . 'first');

        if ( $this->scrollable )
        {
            $bodyHeaderStyle = "style=\"width: {$this->scrollWidth}; overflow-x: hidden;\"";
            $bodyHeader = new MDiv('head' . $this->id, $tblDataHeader, 'mGridHead', $bodyHeaderStyle);
            $bodyStyle = "style=\"width:{$this->scrollWidth}; height: {$this->scrollHeight}; overflow: auto;\" ";
            $body = new MDiv('body' . $this->id, $tblData, 'mGridBody', $bodyStyle );
            $body = new MDiv('', array( $bodyHeader, $body ));
        }
        else
        {
            $body = $tblData;
        }

        return $body;
    }

    /**
     * Overrides the method which draws the actions to remove them
     */
    public function generateActions(&$tbl)
    {
        $i = $this->currentRow;

        if ( $this->hasDetail )
        {
            $i += $this->currentRow;
        }

        $c = 0;

        // adds the selection icon without onclick action
        $spanClass = ($this->select != NULL) ? ' tall' : '';
        $control = new MSpan('', '&nbsp;');
        $control->setClass($this->buttonSelectClass, false);
        $tbl->setCell($i, $c, $control);
        $tbl->setCellClass($i, $c, 'btn');

        if ( $this->hasDetail )
        {
            $tbl->setCell($i + 1, $c, new MDiv('ddetail' . $this->currentRow, NULL));
            $tbl->setCellClass($i + 1, $c, 'action-default');
        }
        $c++;
        if ( $n = count($this->actions) )
        {
            // generate action links
            while ( $c < ($n + 1) )
            {
                $action = $this->actions[$c - 1];
                $tbl->setCell($i, $c, $action->generate(), $action->attributes());
                if ( $this->hasDetail )
                {
                    $tbl->setCell($i + 1, $c, new MDiv('adetail' . $this->currentRow, NULL));
                }
                $tbl->setCellClass($i, $c, $c == ($n) ? 'data action' : 'action');
                $c++;
            }
        }

        if ( $this->select != NULL )
        {
            $tbl->setRowAttribute($i, 'id', "row" . $this->name . "[{$this->currentRow}]");
            $tbl->setCellClass($i, $c, 'data select');
            $select = $this->select->generate();
            $select->checked = (array_search($i, $this->selecteds) !== false);
            $tbl->cell[$i][$c] = $select;
            if ( $this->hasDetail )
            {
                $tbl->setCell($i + 1, $c, new MDiv('sdetail' . $this->currentRow, NULL));
            }
        }
    }
}

?>
