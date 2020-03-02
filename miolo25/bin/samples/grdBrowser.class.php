<?php

class grdBrowser extends MGrid
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();

        $columns[] = new MGridColumn(_M('Identifier', $module));
        $columns[] = new MGridColumn(_M('Description', $module));

        parent::__construct(NULL, $columns, NULL);

        $args = array(
            'event' => 'actionUpdate:click',
            'function' => 'edit',
        );
        $hrefUpdate = $MIOLO->getActionURL($module, $action, '%0%', $args);
        $args = array(
            'event' => 'actionDelete:click',
            'function' => 'search',
        );
        $hrefDelete = $MIOLO->getActionURL($module, $action, '%0%', $args);

        $this->addActionUpdate($hrefUpdate);
        $this->addActionDelete($hrefDelete);
        $this->setTitle(_M('Browsers', $module));
    }
}
?>
