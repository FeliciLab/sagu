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
// | Abstract: This file contains the statusbar elements definitions |
// |                                                                 |
// | Created: 2001/08/14 Vilson Cristiano GÃ¤rtner,                   |
// |                     Thomas Spriestersbach                       |
// |                                                                 |
// | History: Initial Revision                                       |
// +-----------------------------------------------------------------+

/**
 * Uma classe auxiliar para monitorar o desempenho 
 */
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MMioloStatus extends MControl
{
    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function generate()
    {
        global $HTTP_USER_AGENT;
        $MIOLO = MIOLO::getInstance();

        $MIOLO->profileExit('miolo.php');

        if (strstr($HTTP_USER_AGENT, "Konqueror"))
        {
            $layerTag = 'div';
            $visibility = 'hidden';
        }
        else
        {
            $layerTag = 'ilayer';
            $visibility = 'visible';
        }

        $left = 10;
        $width = 200;

        $dump = $MIOLO->dump->get();

        if ($dump['profile'])
        {
            if ($control)
                $control .= '&nbsp;::&nbsp;';

            $control .= "<a href=\"javascript:toggleLayer('stats1')\">Parsing&nbsp;Profile</a>";

            $style = "position:absolute; visibility:$visibility; left:{$left}px; "
                         . "width:{$width}px; height:200px; overflow:scroll;";

            $html .= "<$layerTag name=\"stats1\" style=\"$style\">" . "<div class=\"statisticsText\">\n"
                         . "<div align=\"right\"><a href=\"javascript:toggleLayer('stats1')\">"
                         . "<img src=\"images/close.gif\" border=\"0\"></a></div>\n" . $MIOLO->profileDump()
                         . "<br></div>\n" . "</$layerTag>\n";

            $left += $width + 10;
        }

        if ($dump['uses'])
        {
            if ($control)
                $control .= '&nbsp;::&nbsp;';

            $control .= "<a href=\"javascript:toggleLayer('stats2')\">Include&nbsp;Usage</a>";

            $style = "position:absolute; visibility:$visibility; left:{$left}; "
                         . "width:{$width}px; height:200px; overflow:scroll;";

            $html .= "<$layerTag name=\"stats2\" style=\"$style\">" . "<div class=\"statisticsText\">\n"
                         . "<div align=\"right\"><a href=\"javascript:toggleLayer('stats2')\">"
                         . "<img src=\"images/close.gif\" border=\"0\"></a></div>\n" . $MIOLO->usesDump()
                         . "<br></div>\n" . "</$layerTag>\n";

            $left += $width + 10;
        }

        if ($dump['trace'])
        {
            if ($control)
                $control .= '&nbsp;::&nbsp;';

            $control .= "<a href=\"javascript:toggleLayer('stats3')\">Tracing&nbsp;</a>";

            $style = "position:absolute; visibility:$visibility; left:{$left}px; "
                         . "width:{$width}px; height:200px; overflow:scroll;";

            $html .= "<$layerTag name=\"stats3\" style=\"$style\">" . "<div class=\"statisticsText\">\n"
                         . "<div align=\"right\"><a href=\"javascript:toggleLayer('stats3')\">"
                         . "<img src=\"images/close.gif\" border=\"0\"></a></div>\n" . $MIOLO->traceDump()
                         . "<br></div>\n" . "</$layerTag>\n";
        }

        if ($html)
        {
            echo "<!-- START STATISTIC INFORMATION -->\n";
            echo "<p><img src=\"images/information.gif\" border=\"0\"" . "width=\"24\" align=\"absolutemiddle\">&nbsp;"
                     . $control . "</p>\n";
            echo "<$layerTag style=\"position:relative; visibility:visible; width:100%; height:280px; "
                     . "overflow:visible;\">\n";
            echo $html;
            echo "</$layerTag>\n";
            echo "<!-- END STATISTIC INFORMATION -->\n";
            echo "<p>&nbsp;</p>\n";
        }
    }
}
?>
