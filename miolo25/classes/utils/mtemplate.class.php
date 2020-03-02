<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MTemplate
{
    /**
     * Attribute Description.
     */
    public $text;

    /**
     * Attribute Description.
     */
    public $mimeType;

    /**
     * Attribute Description.
     */
    public $templateFile;

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $fileName (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function __construct($fileName)
    {
        include $fileName;
        $this->mimeType = $mimeType;
        $this->templateFile = $templateFile;
        $tpl = dirname($fileName) . '/' . $templateFile;
        $this->text = $this->templateFile($tpl, $vars);
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $file (tipo) desc
     * @param $vars (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function templateFile($file, $vars)
    {
        $str = implode("", file($file));
        $str = preg_replace(array_keys($vars), $vars, $str);
        return $str;
    }
}
?>
