<?php
class MActionHyperLink extends MLink
{
    public function __construct( $name, $label, $module = '', $action = '', $item = null, $args = null )
    {
        parent::__construct( $name, $label );

        $this->setAction( $module, $action, $item, $args );
    }
}

?>