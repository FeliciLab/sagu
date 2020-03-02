<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MError extends MControl
{
    /**
     * Attribute Description.
     */
    public $msg;

    /**
     * Attribute Description.
     */
    public $info;

    /**
     * Attribute Description.
     */
    public $href;

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $msg (tipo) desc
     * @param $info=null (tipo) desc
     * @param $href=null (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function __construct($msg = null, $info = null, $href = null)
    {
        $this->msg = $msg;
        $this->info = $info;
        $this->href = $href;

        if (!$this->msg)
            $this->msg = "Erro";

        //        if ( ! $this->info )
        //            $this->info = "Causa desconhecida";

        if (!$this->href)
        {
            $this->href = getenv("HTTP_REFERER");

            if (!$this->href)
                $this->href = "/index.php";
        }
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
        echo "<html>";
        echo "<body>";
        echo "<form action=\"{$this->href}\">";
        echo "<table width=\"50%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\" align=\"center\">\n";
        echo "  <tr>\n";
        echo "    <td class=\"boxTitle\" bgcolor=\"#990000\" colspan=\"2\"><font color=\"#FFFFFF\"><b>Fatal Error</b></font></td>\n";
        echo "  </tr>\n";
        echo "  <tr>\n";
        echo "    <td valign=\"top\" width=\"60\"><img src=\"images/error.gif\" hspace=\"20\"></td>\n";
        echo "    <td class=\"errorText\">";

        echo $this->msg;

        if (is_array($this->info))
        {
            echo "<ul>\n";

            foreach ($this->info as $i)
                echo "<li>$i</li>";

            echo "</ul>\n";
        }
        else
            echo $this->info;

        echo "</td>\n";
        echo "  </tr>\n";
        echo "  <tr>\n";
        echo "    <td colspan=\"2\" bgcolor=\"#AAAAAA\" align=\"center\">\n";
        echo "       <input type=\"button\" value=\"Voltar\">\n";
        echo "    </td>\n";
        echo "  </tr>\n";
        echo "</table>\n";
        echo "</form>";
        echo "</body></html>";
    }
}
?>
