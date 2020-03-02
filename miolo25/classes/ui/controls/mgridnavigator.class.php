<?php
define('PN_PAGE', 'pn_page');
/**
 * Uma implementaÃ§Ã£o de controles de navagaÃ§Ã£o de pÃ¡ginas para grids
 */
class MGridNavigator extends MControl
{
    public $pageLength;
    public $pageNumber;
    public $action;
    public $range;
    public $rowCount;
    public $gridCount;
    public $pageCount;
    public $idxFirst;
    public $idxLast;
    public $showPageNo = false;
    public $linktype; // hyperlink or linkbutton
    public $grid;

    public function __construct($length = 20, // Number of records per page
                           $total = 0,   // Number total of records 
                           $action = '?', // Action URL
                           $grid = NULL// The grid which contains this component
        )
    {
        parent::__construct();
        $this->pageLength = $length;
        $this->gridCount = $length;
        $this->setRowCount($total);
        $this->grid = $grid;
        $this->setPageNumber($this->page->getViewState("pn_page",$this->grid->name));
        $this->action = $action;
        $this->linktype = 'hyperlink';
    }

    public function setAction($url)
    {
        $this->action = $url;
    }

    public function setLinkType($linktype)
    {
        $this->linktype = $linktype;
    }

    public function setRowCount($rowCount)
    {
        $this->rowCount = $rowCount;
        $this->pageCount = ($this->pageLength > 0) ? (int)(($this->rowCount + $this->pageLength - 1) / $this->pageLength) : 1;
    }

    public function setGridCount($gridCount)
    {
        $this->gridCount = $gridCount;
    }

    public function setPageNumber($num)
    {
        $this->pageNumber = (int)($num ? $num : 1);
        $this->range = new MRange($this->pageNumber, $this->pageLength, $this->rowCount);
        $this->setIndexes();
    }

    public function setCurrentPage( $pageNumber )
    {
        $this->setPageNumber( $pageNumber );
    }

    public function setIndexes()
    {
      $this->range-> __construct($this->pageNumber, $this->pageLength, $this->rowCount);
      $this->idxFirst = $this->range->offset;
      $this->idxLast = $this->range->offset + $this->range->rows - 1;
      $this->setGridCount($this->range->rows);
    }

    public function setGridParameters($pageLength, $rowCount, $action, $grid)
    {
      $this->pageLength = $pageLength;
      $this->setRowCount($rowCount);
      $this->action = $action;
      $this->grid = $grid;
      $this->setIndexes();
    }

    public function getRowCount()
    {
        return $this->rowCount;
    }

    public function getGridCount()
    {
        return $this->gridCount;
    }

    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    public function getPageCount()
    {
        return $this->pageCount;
    }

    public function getPagePosition($showPage = true)
    {
        $position = '[' . ($showPage ? _M('Page') : '') . ' ' . $this->getPageNumber() . ' ' . _M('of') . ' '
                        . $this->getPageCount() . "]";
        return $position;
    }

    public function getPageLinks($showPage = true, $limit = 10)
    {           
        $pageCount = $this->getPageCount();
        $pageNumber = $this->getPageNumber();
        $pageLinks = array();

        $p = 0;

        if (!$this->getRowCount())
        {
            $pageLinks[$p] = new MLabel('&nbsp;&nbsp;&nbsp;');
            $pageLinks[$p++]->setClass('mGridNavigatorText');
        }
        else
        {
            if ($showPage)
            {
                $pageLinks[$p] = new MText('', '&nbsp;'._M("Page").'&nbsp;');
                $pageLinks[$p++]->setClass('mGridNavigatorText');
            }

            if ($pageNumber <= $limit)
            {
                $o = 1;
            }
            else
            {
                $o = ((int)(($pageNumber - 1) / $limit)) * $limit;
                $pageLinks[$p] = new MLinkButton('', '...', "$this->action&" . PN_PAGE . "=" . $o++ . "&gridName=". urlencode($this->grid->name).'&m_pagenavigating=1');
                $pageLinks[$p++]->setClass('mGridNavigatorLink');
            }

            for ($i = 0; ($i < $limit) && ($o <= $pageCount); $i++, $o++)
            {
                $pg = $o;
                if ($o != $pageNumber)
                {
                    $pageLinks[$p] = new MLinkButton('', $pg, "$this->action&" . PN_PAGE . "=" . $o . "&gridName=". urlencode($this->grid->name).'&m_pagenavigating=1');
                    $pageLinks[$p++]->setClass('mGridNavigatorLink');
//                    $pageLinks[$p++]->addEvent('mouseover', "top.status='PÃ¡gina $pg'");
                }
                else
                {
                    $pageLinks[$p] = new MSpan('', "$pg");
                    $pageLinks[$p++]->setClass('mGridNavigatorSelected');
                }
            }

            if ($o < $pageCount)
            {
                $pageLinks[$p++] = new MLabel('');
                $pageLinks[$p] = new MLinkButton('', '...', "$this->action&" . PN_PAGE . "=" . $o . "&gridName=". urlencode($this->grid->name).'&m_pagenavigating=1');
                $pageLinks[$p++]->setClass('mGridNavigatorLink');
            }
        }

        $d = new MDiv('', $pageLinks, 'mGridNavigatorLinks');
        return $d;
    }

    public function getPageRange($subject = '')
    {
        if (!$this->getRowCount())
        {
            $range = 'Nenhum dado';
        }
        else
        {
            $first = $this->idxFirst + 1;
            $last = $this->idxLast + 1;
            $range = '[' . $first . '..' . $last . '] de ' . $this->getRowCount() . $subject;
        }

        return new MDiv('', $range, 'mGridNavigatorRange');
    }

    public function getPageRows($subject = '')
    {
        $rows = $this->getGridCount() . '&nbsp;' . $subject;
        return $rows;
    }

    public function getPageFirst()
    {
        $pageNumber = $this->getPageNumber();

        if ( $pageNumber > 1 )
        {
            $image = new MLinkButton('', '&nbsp;', "$this->action&" . PN_PAGE . "=1" . "&gridName=". urlencode($this->grid->name).'&m_pagenavigating=1');
            $image->setClass('mGridNavigatorImage mGridNavigatorImageFirstOn');
        }
        else
        {
            $image = new MSpan();
            $image->setClass('mGridNavigatorImage mGridNavigatorImageFirstOff');
        }

        return $image;
    }

    public function getPagePrev()
    {
        $pageNumber = $this->getPageNumber();
        $pagePrev = $pageNumber - 1;

        if ( $pageNumber > 1 )
        {
            $image = new MLinkButton('', '&nbsp;', "$this->action&" . PN_PAGE . "=" . $pagePrev . "&gridName=". urlencode($this->grid->name).'&m_pagenavigating=1');
            $image->setClass('mGridNavigatorImage mGridNavigatorImagePrevOn');
        }
        else
        {
            $image = new MSpan();
            $image->setClass('mGridNavigatorImage mGridNavigatorImagePrevOff');
        }

        return $image;
    }

    public function getPageNext()
    {
        $pageNumber = $this->getPageNumber();
        $pageNext = $pageNumber + 1;
        $pageCount = $this->getPageCount();

        if ( $pageNumber < $pageCount )
        {
            $image = new MLinkButton('', '&nbsp;', "$this->action&" . PN_PAGE . "=" . $pageNext . "&gridName=". urlencode($this->grid->name).'&m_pagenavigating=1');
            $image->setClass('mGridNavigatorImage mGridNavigatorImageNextOn');
        }
        else
        {
            $image = new MSpan();
            $image->setClass('mGridNavigatorImage mGridNavigatorImageNextOff');
        }

        return $image;
    }

    public function getPageLast()
    {
        $pageNumber = $this->getPageNumber();
        $pageCount = $this->getPageCount();

        if ( $pageNumber < $pageCount )
        {
            $image = new MLinkButton('', '&nbsp;', "$this->action&" . PN_PAGE . "=" . $pageCount . "&gridName=". urlencode($this->grid->name).'&m_pagenavigating=1');
            $image->setClass('mGridNavigatorImage mGridNavigatorImageLastOn');
        }
        else
        {
            $image = new MSpan();
            $image->setClass('mGridNavigatorImage mGridNavigatorImageLastOff');
        }

        return $image;
    }

    public function getPageImages()
    {
        $array[0] = $this->getPageFirst();
        $array[1] = $this->getPagePrev();
        $array[2] = $this->getPageRange();
        $array[3] = $this->getPageNext();
        $array[4] = $this->getPageLast();
        $d = new MDiv('', $array, 'mGridNavigatorImages');
        return $d;
    } 



}
?>
