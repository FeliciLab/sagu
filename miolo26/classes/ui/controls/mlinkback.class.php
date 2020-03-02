<?php
class MLinkBack extends MLink
{
    public function __construct($text = 'Voltar', $href = '')
    {
        global $history;

        if ($href == '')
        {
            $href = $history->back('action');
        }

        parent::__construct('', '', $href, $text);
    }
}
?>