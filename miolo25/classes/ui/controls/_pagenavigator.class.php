<?php
// +-----------------------------------------------------------------+
// | MIOLO - Miolo Development Team - UNIVATES Centro UniversitÃ¡rio  |
// +-----------------------------------------------------------------+
// | CopyLeft (L) 2001-2002 UNIVATES, Lajeado/RS - Brasil            |
// +-----------------------------------------------------------------+
// | Licensed under GPL: see COPYING.TXT or FSF at www.fsf.org for   |
// |                     further details                             |
// |                                                                 |
// | Site: http://miolo.codigolivre.org.br                          |
// | E-mail: vgartner@univates.br                                    |
// |         ts@interact2000.com.br                                  |
// +-----------------------------------------------------------------+
// | Abstract: This file contains the navigation elements definitions|
// |                                                                 |
// | Created: 2001/08/14 Thomas Spriestersbach,                      |
// |                     Vilson Cristiano GÃ¤rtner                    |
// |                                                                 |
// | History: Initial Revision                                       |
// +-----------------------------------------------------------------+

define('PN_PAGE', 'pn_page');

/**
 * Uma implementaÃ§Ã£o de uma barra de navagaÃ§Ã£o de pÃ¡ginas para resultados
 * de consultas
 */
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class PageNavigator
{
    /**
     * Attribute Description.
     */
    public $pageLength;

    /**
     * Attribute Description.
     */
    public $pageNumber;

    /**
     * Attribute Description.
     */
    public $pageLinks;

    /**
     * Attribute Description.
     */
    public $action;

    /**
     * Attribute Description.
     */
    public $queryRange;

    /**
     * Attribute Description.
     */
    public $rowCount;

    /**
     * Attribute Description.
     */
    public $pnCount;

    /**
     * Attribute Description.
     */
    public $showPageNo = false;

    /**
     * Attribute Description.
     */
    public $pageNo;

    /**
     * Attribute Description.
     */
    public $linktype; // hyperlink or linkbutton

    /**
     * Attribute Description.
     */
    public $aButtons;

    /**
     * Attribute Description.
     */
    public $aControls;

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $length20 (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function pageNavigator($length = 20, // Number of records per page
                           $action = '?' // Action URL
        )
    {
        $this->pageNumber = $_GET[PN_PAGE] ? $_GET[PN_PAGE] : $_POST[PN_PAGE];
        $this->pageLength = $length;
        $this->pnCount = $length;

        $this->action = $action;

        $this->queryRange = new QueryRange($this->pageNumber, $this->pageLength);
        $this->rowCount = $this->queryRange->total;
        $this->linktype = 'hyperlink';
        $aButtons = null;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $url (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setAction($url)
    {
        $this->action = $url;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $aButtons (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setButtons($aButtons)
    {
        if (!is_array($aButtons))
            $aButtons = array($aButtons);

        $this->aButtons = $aButtons;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $aControls (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setControls($aControls)
    {
        if (!is_array($aControls))
            $aControls = array($aControls);

        $this->aControls = $aControls;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function setShowPageNo()
    {
        $this->showPageNo = true;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $linktype (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setLinkType($linktype)
    {
        $this->linktype = $linktype;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function getPageNo()
    {
        $this->pageNo = '[' . _M('Page') . ' ' . ($this->getCurrentPage() + 1) . ' ' . _M('of') . ' '
                            . $this->getTotalPages() . "]";
        return ($this->pageNo);
    }

    public function &GetQueryRange()
    {
        return $this->queryRange;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function getTotalRows()
    {
        //        return $this->queryRange->total;
        return $this->rowCount;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function getCurrentPage()
    {
        return (int)$this->pageNumber;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $num (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setCurrentPage($num)
    {
        $this->pageNumber = (int)$num;

        $this->queryRange = new QueryRange($this->pageNumber, $this->pageLength);
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function getTotalPages()
    {
        return (int)(($this->rowCount + $this->pageLength - 1) / $this->pageLength);
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function generate()
    {
        //        $rowCount = $this->queryRange->total;
        $rowCount = $this->rowCount;

        /*
                $btn_first0 = '<img src="/images/pnfirst0.gif" border="0">';
                $btn_first1 = '<img src="/images/pnfirst.gif" border="0">';
                $btn_prev0  = '<img src="/images/pnprev0.gif" border="0">';
                $btn_prev1  = '<img src="/images/pnprev.gif" border="0">';
                $btn_next0  = '<img src="/images/pnnext0.gif" border="0">';
                $btn_next1  = '<img src="/images/pnnext.gif" border="0">';
                $btn_last0  = '<img src="/images/pnlast0.gif" border="0">';
                $btn_last1  = '<img src="/images/pnlast.gif" border="0">';
        */
        $btn_first0 = '<img src="/images/but_pg_primeira.gif" border="0">';
        $btn_first1 = '<img src="/images/but_pg_primeira_x.gif" border="0">';
        $btn_prev0 = '<img src="/images/but_pg_anterior.gif" border="0">';
        $btn_prev1 = '<img src="/images/but_pg_anterior_x.gif" border="0">';
        $btn_next0 = '<img src="/images/but_pg_proxima.gif" border="0">';
        $btn_next1 = '<img src="/images/but_pg_proxima_x.gif" border="0">';
        $btn_last0 = '<img src="/images/but_pg_ultima.gif" border="0">';
        $btn_last1 = '<img src="/images/but_pg_ultima_x.gif" border="0">';

        echo "<!-- PageNavigator -->\n";
        echo "<table class=\"pageNavigator\" align=\"center\">\n";
        echo "  <tr>\n";

        if ($this->pageNumber > 0)
        {
            if ($this->linktype == 'hyperlink')
            {
                echo "    <td><a href=\"$this->action&" . PN_PAGE . "=0\">$btn_first1</a></td>\n";
                echo "    <td><a href=\"$this->action&" . PN_PAGE . "=" . ($this->pageNumber
                                                                              - 1) . "\">$btn_prev1</a></td>\n";
            }
            else
            {
                $action = "$this->action&" . PN_PAGE . "=0";
                echo "    <td><a href=\"javascript:_MIOLO_LinkButton(document.forms[0].name, '{$action}','')\">$btn_first1</a></td>\n";
                $action = "$this->action&" . PN_PAGE . "=" . ($this->pageNumber - 1);
                echo "    <td><a href=\"javascript:_MIOLO_LinkButton(document.forms[0].name, '{$action}','')\">$btn_prev1</a></td>\n";
            }
        }
        else
        {
            echo "    <td>$btn_first0</td>\n";
            echo "    <td>$btn_prev0</td>\n";
        }

        //Total number of pages
        $pageCount = (int)(($rowCount + $this->pageLength - 1) / $this->pageLength);

        echo "    <td width=\"100%\" align=\"center\">";

        if ($pageCount >= 2)
        {
            if ($this->pageNumber < 10)
            {
                $o = 0;
            }
            else
            {
                $o = $this->pageNumber - 10;

                if ($o > 0)
                {
                    $this->pageLinks
                        .= "&nbsp;<a class=\"pageNavigatorLink\" href=\"$this->action&" . PN_PAGE . "=" . ($o
                                                                                                              - 1)
                               . "\">...</a> ";
                }
            }

            for ($i = 0; $i < 11 && $o < $pageCount; $i++, $o++)
            {
                if ($i > 0)
                {
                    $this->pageLinks .= "&nbsp;";
                }

                $p = $o + 1;

                if ($o != $this->pageNumber)
                {
                    if ($this->linktype == 'hyperlink')
                    {
                        $this->pageLinks
                            .= "<a href=\"$this->action&" . PN_PAGE
                                   . "=$o\" class=\"pageNavigatorLink\" onMouseOver=\"top.status='PÃ¡gina $p'\">$p</a>";
                    }
                    else
                    {
                        $action = "$this->action&" . PN_PAGE . "=$o";
                        $this->pageLinks
                            .= "<a href=\"javascript:_MIOLO_LinkButton(document.forms[0].name,'{$action}','')\" class=\"pageNavigatorLink\" onMouseOver=\"top.status='PÃ¡gina $p'\">$p</a>";
                    }
                }
                else
                {
                    $this->pageLinks .= "<span class=\"pageNavigatorSelected\">$p</span>";
                }
            }

            if ($o < $pageCount)
            {
                $this->pageLinks
                    .= "&nbsp;<a class=\"pageNavigatorLink\" href=\"$this->action&" . PN_PAGE . "=$p\">...</a>";
            }

            if ($this->showPageNo)
            {
                echo $this->pageLinks;
            }
        }
        else
        {
            echo "&nbsp;";
        }

        if ($this->aButtons)
        {
            for ($i = 0; $i < count($this->aButtons); $i++)
            {
                echo $this->aButtons[$i]->generate();
                echo "&nbsp;";
            }
        }

        if ($this->aControls)
        {
            for ($i = 0; $i < count($this->aControls); $i++)
            {
                echo $this->aControls[$i]->generate();
                echo "&nbsp;";
            }
        }

        echo "</td>\n";

        $maxRow = $this->pageNumber * $this->pageLength + $this->pageLength - 1;

        if ($maxRow + 1 < $rowCount)
        {
            if ($this->linktype == 'hyperlink')
            {
                echo "    <td><a href=\"$this->action&" . PN_PAGE . "=" . ($this->pageNumber
                                                                              + 1) . "\">$btn_next1</a></td>\n";
                echo "    <td><a href=\"$this->action&" . PN_PAGE . "=" . ($pageCount - 1) . "\">$btn_last1</a></td>\n";
            }
            else
            {
                $action = "$this->action&" . PN_PAGE . "=" . ($this->pageNumber + 1);
                echo "    <td><a href=\"javascript:_MIOLO_LinkButton(document.forms[0].name, '{$action}','')\">$btn_next1</a></td>\n";
                $action = "$this->action&" . PN_PAGE . "=" . ($pageCount - 1);
                echo "    <td><a href=\"javascript:_MIOLO_LinkButton(document.forms[0].name, '{$action}','')\">$btn_last1</a></td>\n";
            }
        }
        else
        {
            echo "    <td>$btn_next0</td>\n";
            echo "    <td>$btn_last0</td>\n";
        }

        echo "  </tr>\n";
        echo "  <tr>\n";
        echo "    <td align=\"center\" colspan=\"5\" class=\"pageNavigatorText\">\n";

        if ($this->showPageNo)
        {
            echo "      " . $this->pageNo . "\n";
        }

        echo "    </td>\n";
        echo "  </tr>\n";
        echo "</table>\n";
    }
}
?>
