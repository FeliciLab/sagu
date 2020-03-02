<?php

class frmUserSearch extends AdminSearchForm
{

    public function __construct()
    {
        $module = MIOLO::getCurrentModule();

        $title = _M('Users', $module) . ' - ' . _M('Search', $module);

        parent::__construct($title);
    }

    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        parent::createFields();

        $fields[] = new MTextField('username', '', _M('Login', $module), 20);
        $fields[] = new MTextField('fullname', '', _M('Name', $module), 30);
        $fields[] = new MTextField('nickname', '', _M('Nick', $module), 20);

        $modules = $MIOLO->getBusiness($module, 'module');
        $fields[] = new MSelection('idModule', NULL, _M('Module', $module), $modules->listAll()->chunkResult());

        $fields[] = new MButton('search', _M('Search', $module));

        $grid = $this->createGrid();
        $fields[] = new MDiv('divGrid', $grid);

        $this->addFields($fields);
    }

    public function createGrid($filters=array( ))
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();

        $user = $MIOLO->getBusiness($module, 'user');

        $columns = array(
            new MDataGridColumn('iduser', _M('Id', $module), 'right', true, '10%', true),
            new MDataGridColumn('login', _M('Username', $module), 'left', true, '15%', true, null, true),
            new MDataGridColumn('name', _M('Name', $module), 'left', true, '40%', true, null, true),
            new MDataGridColumn('nickname', _M('Nickname', $module), 'left', true, '20%', true, null, true),
            new MDataGridColumn('idModule', _M('Module', $module), 'left', true, '15%', true, null, true)
        );

        $href_datagrid = $MIOLO->getActionURL($module, $action, '');
        $query = $user->listByFilters($filters);
        $datagrid = new MDataGrid($query, $columns, $href_datagrid, 15);

        $href_edit = $MIOLO->getActionURL($module, $action, '%0%', array(
            'event' => 'edit:click',
            'function' => 'update'
        ));
        $datagrid->addActionUpdate($href_edit);

        $href_dele = $MIOLO->getActionURL($module, $action, '%0%', array(
            'event' => 'delete:click',
            'function' => 'delete'
        ));
        $datagrid->addActionDelete($href_dele);

        return $datagrid;
    }

    public function search_click()
    {
        $filters->login = $this->getFormValue('username');
        $filters->fullname = $this->getFormValue('fullname');
        $filters->nickname = $this->getFormValue('nickname');
        $filters->idModule = $this->getFormValue('idModule');

        $data = $this->createGrid($filters);

        $this->setResponse($data, 'divGrid');
    }
}

?>
