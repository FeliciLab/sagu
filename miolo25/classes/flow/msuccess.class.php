<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MSuccess
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
     * Attribute Description.
     */
    public $label;

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $info (tipo) desc
     * @param $msg=null (tipo) desc
     * @param $href=null (tipo) desc
     * @param $label=Continuar' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function success($info = null, $msg = null, $href = null, $label = 'Continuar')
    {
        $this->msg = $msg;
        $this->info = $info;
        $this->href = $href;
        $this->label = $label;

        if (!$this->msg)
            $this->msg = "Success";

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
        echo "    <td valign=\"top\" width=\"60\"><img src=\"images/information.gif\" hspace=\"20\"></td>\n";
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
        echo "       <form><input type=\"button\" onClick=\"MIOLO_GotoURL('$this->href')\" value=\"$this->label\"></form>\n";
        echo "    </td>\n";
        echo "  </tr>\n";
        echo "</table>\n";
    }
}
?>
