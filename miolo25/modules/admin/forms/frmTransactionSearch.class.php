<?php

class frmTransactionSearch extends AdminSearchForm
{

    public function __construct()
    {
        $module = MIOLO::getCurrentModule();

        $title = _M('Transactions', $module) . ' - ' . _M('Search', $module);

        parent::__construct($title);
    }

    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        parent::createFields();

        $fields[] = new MTextField('transaction', '', 'Transaction', 15);

        $fields[] = new MButton('search', _M('Search', $module));

        $grid = $this->createGrid();
        $fields[] = new MDiv('divGrid', $grid);

        $this->addFields($fields);
    }

    public function createGrid($filters=NULL)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();

        $transaction = $MIOLO->getBusiness($module, 'transaction');

        $columns = array(
            new MDataGridColumn('idtransaction', _M('Id', $module), 'right', TRUE, '5%', TRUE),
            new MDataGridColumn('m_transaction', _M('Transaction', $module), 'left', true, '20%', true, null, true),
            new MDataGridColumn('nameTransaction', _M('Name', $module), 'left', true, '50%', true, null, true),
            new MDataGridColumn('idModule', _M('Module', $module), 'left', true, '20%', true, null, true),
            new MDataGridColumn('parentm_transaction', _M('Parent', $module), 'left', TRUE, '5%', TRUE),
            new MDataGridColumn('action', _M('Action', $module), 'left', TRUE, NULL, FALSE, NULL, TRUE)
        );
        $href_datagrid = $MIOLO->getActionURL($module, $action, '');
        $query = $transaction->listByTransaction($filters->transaction);
        $datagrid = new MDataGrid($query, $columns, $href_datagrid, 15);

        $href_edit = $MIOLO->getActionURL($module, $action, '%0%', Array(
                    'event' => 'edit:click',
                    'function' => 'update'
                ));
        $datagrid->addActionUpdate($href_edit);

        $href_dele = $MIOLO->getActionURL($module, $action, '%0%', Array(
                    'event' => 'delete:click'
                ));
        $datagrid->addActionDelete($href_dele);

        return $datagrid;
    }

    public function search_click()
    {
        $filters->transaction = $this->getFormValue('transaction');

        $data = $this->createGrid($filters);

        $this->setResponse($data, 'divGrid');
    }
}

?>

