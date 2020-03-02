<?php

class grdBrowser extends MGrid
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();

        $columns[] = new MGridColumn(_M('Identificador', $module));
        $columns[] = new MGridColumn(_M('Descrição', $module));

        parent::__construct(NULL, $columns, NULL);

        $args = array(
            'event' => 'actionUpdate:click',
            'function' => 'edit',
        );
        $hrefUpdate = $MIOLO->getActionURL($module, $action, '%0%', $args);

        $this->addActionUpdate($hrefUpdate);
        $this->addActionDelete("miolo.doAjax('actionDelete','item=%0%','{$MIOLO->page->getFormId()}');");
        $this->setTitle(_M('Navegadores', $module));
    }
}
?>
