<?php

/**
 * MSpecialGrid component.
 * A MGrid component with some extra features, like row selection by clicking
 * and JS hiding columns.
 *
 * @author Armando Taffarel Neto [taffarel@solis.coop.br]
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2010/08/17
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2010-2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */

$MIOLO = MIOLO::getInstance();
$MIOLO->page->addScript('m_specialgrid.js');

class MSpecialGrid extends MGrid
{
    /**
     * Default argument name.
     */
    const DEFAULT_ARGUMENT = 'id';

    /**
     * @var array Define which column(s) data must be sent by post. To send the third column data as 'id', you should
     * set the arguments to: array( 'id' => '%2%' ).
     */
    public $arguments;

    /**
     * @var array 
     */
    public $selectedData;

    /**
     * @var boolean 
     */
    public $showVisibleColumnsMenu;

    /**
     * @var array Column indexes to hide.
     */
    private $invisibleColumns;

    /**
     * MSpecialGrid constructor.
     *
     * @param array $data Data to fill the grid.
     * @param array $columns MGridColumn array.
     * @param string $name Id.
     * @param integer $pageLength Number of lines per page.
     * @param boolean $showCheckBoxes Whether the the check boxes must be shown.
     * @param array $arguments Array with the names for the selected lines in post.
     */
    public function __construct($data, $columns, $name, $pageLength = 25, $showCheckBoxes = true, $arguments = NULL)
    {
        $this->showVisibleColumnsMenu = true;

        parent::__construct($data, $columns, MIOLO::getCurrentURL(), $pageLength, 0, $name, true, true);

        if ( $showCheckBoxes )
        {
            $this->select = new MSpecialGridActionSelect($this);
        }

        $this->arguments = $arguments ? $arguments : array( self::DEFAULT_ARGUMENT => '%0%' );

        // Control and shift keys selection
        $this->selectKeys($this->id);
    }

    /**
     * Returns the selected rows.
     *
     * @return array Array with the selected rows.
     */
    public function getSelectedData($name = null)
    {
        if ( !$name )
        {
            $name = $this->name;
        }

        $selected = $this->page->request('select' . $name);

        return self::getSelectedAsIndexedArray($selected);
    }

    /**
     * Set the check boxes that must be checked.
     *
     * @param array $selectedData Array with the row numbers to select.
     */
    public function setSelectedData($selectedData)
    {
        $this->selectedData = $selectedData;
    }

    /**
     * Returns selected rows as an indexed array.
     *
     * @param array $selectedRows Post result for selected rows.
     * @return array Object array.
     */
    public static function getSelectedAsIndexedArray($selectedRows)
    {
        foreach ( $selectedRows as $selected )
        {
            foreach ( explode('&', $selected) as $argument )
            {
                if ( $argument )
                {
                    list ($key, $value) = explode('|', $argument);
                    $line[$key] = $value;
                }
            }

            $data[] = $line;
        }

        return $data;
    }

    /**
     * Removes the grid check boxes·
     */
    public function removeSelected()
    {
        $this->select = null;
    }

    /**
     * Sets an indexed array to be returned by the getSelectedData method
     *
     * @param array $arguments Indexed array like: array('id' => '%0%')
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @param array $invisibleColumns Set the column indexes to be hidden.
     */
    public function setInvisibleColumns($invisibleColumns)
    {
        $this->invisibleColumns = $invisibleColumns;
    }

    /**
     * @return array The column indexes that will be hidden.
     */
    public function getInvisibleColumns()
    {
        return $this->invisibleColumns;
    }

    /**
     * Overrides grid navigation header method
     *
     * @param boolean $showVisibleColumnsMenu 
     * @return object MDiv
     */
    public function generateNavigationHeader($showVisibleColumnsMenu = true)
    {
        $MIOLO = MIOLO::getInstance();

        if ( $showVisibleColumnsMenu && $this->showVisibleColumnsMenu )
        {
            $content[] = $this->generateVisibleColumns();
        }

        if ( $this->pn )
        {
            if ( $this->pn->getPageCount() > 1 )
            {
                $pageInfo = new MSpan('', _M('Page') . ': ' . $this->pn->getPageNumber() . '/' . $this->pn->getPageCount());
                $pageInfo->addStyle('margin', '5px');
            }

            if ( $this->pn->getPageCount() > 1 && ( $this->pn->getPageNumber() != 1 ) )
            {
                $nav[1] = $this->pn->getPageFirst();
                $nav[1]->setClass('mGridNavigationControl mGridNavigatorImageFirstOn');

                $nav[2] = $this->pn->getPagePrev();
                $nav[2]->setClass('mGridNavigationControl mGridNavigatorImagePrevOn');
            }
            else
            {
                $nav[1] = new MDiv(NULL, NULL, 'mGridNavigationControl mGridNavigatorImageFirstOff');
                $nav[2] = new MDiv(NULL, NULL, 'mGridNavigationControl mGridNavigatorImagePrevOff');
            }

            $pages = $this->pn->getPageLinks(false);

            foreach ( $pages->getInner() as $page )
            {
                $page->setLabel('[' . $page->label . ']');
                $page->setClass('mGridNavigationControl');
                $newPages[] = $page;
            }

            $pages = new MSpan('', $newPages);
            $pages->addStyle('margin-right', '10px');
            $nav[3] = $pages;

            if ( $this->pn->getPageCount() > 1 && ( $this->pn->getPageNumber() != $this->pn->getPageCount() ) )
            {
                $nav[4] = $this->pn->getPageNext();
                $nav[4]->setClass('mGridNavigationControl mGridNavigatorImageNextOn');

                $nav[5] = $this->pn->getPageLast();
                $nav[5]->setClass('mGridNavigationControl mGridNavigatorImageLastOn');
            }
            else
            {
                $nav[4] = new MDiv(NULL, NULL, 'mGridNavigationControl mGridNavigatorImageNextOff');
                $nav[5] = new MDiv(NULL, NULL, 'mGridNavigationControl mGridNavigatorImageLastOff');
            }

            $gridInfo['pageInfo'] = new MDiv(NULL, $pageInfo);
            $gridInfo['pageInfo']->addStyle('float', 'left');

            $gridInfo['navigation'] = new MDiv(NULL, $nav);
            $gridInfo['navigation']->addStyle('float', 'right');

            $content['navigator'] = new MDiv('', $gridInfo, 'mPageNavigator');
        }

        if ( $content )
        {
            return new MDiv('', $content, 'mGridNavigation');
        }
    }

    /**
     * @param array $columns Array of column names.
     */
    public function setHeaderColumns($columns)
    {
        if ( is_array($columns) )
        {
            foreach ( $columns as $column )
            {
                $jsCode .= "dojo.forEach(dojo.byId('row{$this->name}[$column]').childNodes, function (element) { if (element.style) element.className ='mGridHeader' } );";
            }
        }
        else
        {
            $jsCode = "dojo.forEach(dojo.byId('row{$this->name}[$columns]').childNodes, function (element) { if (element.style) element.className ='mGridHeader' } );";
        }

        $this->page->onload($jsCode);
    }

     /**
     * @return object MDiv instance.
     */
    public function generateNavigationFooter()
    {
        return $this->generateNavigationHeader(false);
    }

    /**
     *
     * @param string $id 
     */
    public function selectKeys($id)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->onload("if ( dojo.byId('{$id}') ) { dojo.connect(dojo.byId('{$id}'), 'onclick', mspecialgrid.selectKeys); }");
    }

    /**
     * Returns the icon to alternate the column visibility.
     *
     * @return object MDiv containing the menu icon and the hidden menu.
     */
    public function generateVisibleColumns()
    {
        $div = new MDiv($this->name . '_columnsDiv', '', 'mGridVisibleColumns');
        $menu = new MContextMenu($this->name . '_columnsMenu');

        $index = 0;

        foreach ( $this->columns as $key => $col )
        {
            $visible = $col->visible;

            if ( $visible )
            {
                $icon = 'mGridVisibleColumnsChecked';
                $action = "mspecialgrid.showHideColumn('{$this->id}', '{$index}');";
                $menu->addCustomItem($col->title, $action, $icon);
                $index++;
            }
        }

        $menu->setLeftClickOpen(true);
        $menu->setTarget($div);

        return new MDiv('', array( $menu, $div ));
    }

    /**
     * @return array Header data.
     */
    public function generateHeader()
    {
        $header[] = $this->generateFilter();

        if ( $this->data )
        {
            $header[] = $this->generateNavigationHeader();
        }

        $header[] = $this->generateLinks();

        return $header;
    }

    /**
     * @return string The generated HTML of this MSpecialGrid instance.
     */
    public function generate()
    {
        $this->generateData();
        $header = $this->painter->generateToString($this->generateHeader());
        $body = $this->painter->generateToString($this->generateBody());
        $footer = $this->painter->generateToString($this->generateFooter());

        $invisibleColumns = new MTextField($this->name . '_invisibleColumns');
        $invisibleColumns->addStyle('display', 'none');

        $this->setInner( array($header, $body, $footer, $invisibleColumns) );
        $this->setClass('mGrid');

        if ($this->width != '')
        {
            $this->addStyle('width', $this->width);
        }

        /*
         * FIXME: removes the cells and columns size computation
         * analyze the possibility of removing it from MIOLO
         */
        unset($this->page->onload->items[$this->page->onload->find("miolo.grid.ajustTHead();")]);
        unset($this->page->onload->items[$this->page->onload->find("miolo.grid.ajustSelect('linkbtn');")]);

        // Hide the columns that was hidden before by js
        $iColumns = $this->invisibleColumns;

        if ( !MUtil::isFirstAccessToForm() && MIOLO::_REQUEST( $this->name . '_invisibleColumns' ) )
        {
            $iColumns = explode(',', trim(MIOLO::_REQUEST($this->name . '_invisibleColumns'), ','));
        }

        // Using isset to consider zero string ('0') a valid value
        if ( isset($iColumns) )
        {
            $js = '';
            $invisibleColumns = $iColumns;

            foreach ( $invisibleColumns as $column )
            {
                $js .= "mspecialgrid.showHideColumn('{$this->id}', '{$column}');";
            }

            $this->page->onload($js);
        }

        $this->generateInner();
        $this->generateEvent();

        return $this->getRender('div');
    }
}

?>