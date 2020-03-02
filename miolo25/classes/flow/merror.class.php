<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MError
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
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $msg' (tipo) desc
     * @param $goto='' (tipo) desc
     * @param $caption='' (tipo) desc
     * @param $event='' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function error($msg = '', $goto = '', $caption = '', $event = '')
    {
        if (!$this->msg)
            $this->msg = "Erro";

        if (!$this->info)
            $this->info = "Causa desconhecida";

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
        echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"8\" align=\"center\" height=\"50%\">\n";
        echo "  <tr>\n";
        echo "    <td class=\"boxTitle\" colspan=\"2\">$this->msg</td>\n";
        echo "  </tr>\n";
        echo "  <tr>\n";
        echo "    <td valign=\"top\" width=\"60\"><img src=\"images/error.gif\" hspace=\"20\"></td>\n";
        echo "    <td class=\"errorText\">";

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
        echo "    <td colspan=\"2\" align=\"center\">\n";
        echo "       <form><input type=\"button\" onClick=\"MIOLO_GotoURL('{$this->href}')\" value=\"Voltar\"></form>\n";
        echo "    </td>\n";
        echo "  </tr>\n";
        echo "</table>\n";
    }
}
?>
