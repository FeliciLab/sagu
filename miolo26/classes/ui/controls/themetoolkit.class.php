<?php
// +-----------------------------------------------------------------+
// | MIOLO - Miolo Development Team - UNIVATES Centro UniversitÃ¡rio  |
// +-----------------------------------------------------------------+
// | CopyLeft (L) 2001-2002 UNIVATES, Lajeado/RS - Brasil            |
// +-----------------------------------------------------------------+
// | Licensed under GPL: see COPYING.TXT or FSF at www.fsf.org for   |
// |                     further details                             |
// |                                                                 |
// | Site: http://miolo.codigolivre.org.br                           |
// | E-mail: vgartner@univates.br                                    |
// |         ts@interact2000.com.br                                  |
// +-----------------------------------------------------------------+
// | Abstract: This file contains the toolkit elements definitions   |
// |                                                                 |
// | Created: 2001/08/14 Vilson Cristiano GÃ¤rtner,                   |
// |                     Thomas Spriestersbach                       |
// |                                                                 |
// | History: Initial Revision                                       |
// +-----------------------------------------------------------------+

/**
 *
 */
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class ThemeToolkit
{
    /**
     *
     */
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $borders (tipo) desc
     * @param $title&nbsp (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function generateImageBorderBox($borders, $title = '&nbsp;', $content = '&nbsp;', $width = '100%')
    {
        $tlc = $borders['top-left'];
        $tc = $borders['top-center'];
        $trc = $borders['top-right'];

        $lb = $borders['center-left'];
        $rb = $borders['center-right'];

        $blc = $borders['bottom-left'];
        $bc = $borders['bottom-center'];
        $brc = $borders['bottom-right'];

        echo "<table cellspacing=0 cellpadding=0 border=0 width=\"$width\">\n";

        //Top line
        echo " <tr>\n";
        echo "  <td><img src=\"$tlc\"></td>\n";
        echo "  <td width=\"100%\" background=\"$tc\">$title</td>\n";
        echo "  <td><img src=\"$trc\"></td>\n";
        echo " </tr>\n";

        //Center line
        echo " <tr>\n";
        echo "  <td background=\"$lb\"><img src=\"$lb\"></td>\n";
        echo "  <td width=\"100%\">$content</td>\n";
        echo "  <td background=\"$rb\"><img src=\"$rb\"></td>\n";
        echo " </tr>\n";

        //Bottom line
        echo " <tr>\n";
        echo "  <td><img src=\"$blc\"></td>\n";
        echo "  <td width=\"100%\" background=\"$bc\"><img src=\"$bc\"></td>\n";
        echo "  <td><img src=\"$brc\"></td>\n";
        echo " </tr>\n";

        echo "</table>\n";
    }

    /**
     *
     */
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $colors (tipo) desc
     * @param $title (tipo) desc
     * @param $content=&nbsp (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function generateSimpleBox($colors, $title = false, $content = '&nbsp;', $width = '100%', $borderWidth = 1)
    {
        // outer table for border
        echo "<table width=\"$width\" cellspacing=\"0\" cellpadding=\"$borderWidth\" border=\"0\" bgcolor=\"{$colors[border]}\">\n";
        echo "  <tr>\n";
        echo "    <td><table border=\"0\" cellspacing=\"0\" cellpadding=\"4\" width=\"100%\">\n";

        if ($title)
        {
            echo "      <tr>\n";
            echo "        <td bgcolor=\"{$colors[title]}\">$title</td>\n";
            echo "      </tr>\n";
        }

        echo "      <tr>\n";
        echo "        <td bgcolor=\"{$colors[content]}\">$content</td>\n";
        echo "      </tr>\n";
        echo "    </table>\n";
        echo "  </tr>\n";
        echo "</table>\n";
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $title (tipo) desc
     * @param $filter (tipo) desc
     * @param $body (tipo) desc
     * @param $footer (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function generateFormBox($title, $filter, $body, $footer)
    {
        echo "  <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"" . "align=\"center\" width=\"100%\">\n";
        echo "    <tr>\n";
        echo "      <td>\n";
        echo $title;
        echo "      </td>\n";
        echo "    </tr>\n";

        if ($filter)
        {
            echo "    <tr>\n";
            echo "      <td>\n";
            echo $filter;
            echo "      </td>\n";
            echo "    </tr>\n";
        }

        echo "    <tr>\n";
        echo "      <td>\n";
        echo $body;
        echo "      </td>\n";
        echo "    </tr>\n";
        echo "    <tr>\n";
        echo "      <td>\n";
        echo $footer;
        echo "      </td>\n";
        echo "    </tr>\n";
        echo "      </td>\n";
        echo "    </tr>\n";
        echo "  </table>\n";
    }
}
?>