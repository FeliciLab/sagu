<?php

class frmGroup extends AdminForm
{
    protected $dbGroupUser;
    protected $dbGroup;
    protected $dbAccess;
    protected $dbTransaction;

    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $this->dbGroup = $MIOLO->getBusiness($module, 'group');
        $idGroup = MIOLO::_request('item');
        $this->dbGroup->getById($idGroup);

        $this->dbGroupUser = $MIOLO->getBusiness($module, 'groupuser');
        $this->dbAccess = $MIOLO->getBusiness($module, 'access');
        $this->dbTransaction = $MIOLO->getBusiness($module, 'transaction');

        $function = MIOLO::_request('function');
        switch ( $function )
        {
            case 'update' :
                $title = _M('Update', $module);
                break;
            default :
                $title = _M('Insert', $module);
                break;
        }

        $title = _M('Groups', $module) . ' - ' . $title;

        parent::__construct($title);
    }

    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        parent::createFields();

        $fields[] = new MTextField('idGroup', '', _M('Id', $module));
        $fields[] = new MTextField('group', '', _M('Group', $module), 20);

        $modules = $MIOLO->getBusiness($module, 'module');
        $fields[] = new MSelection('idModule', NULL, _M('Module', $module), $modules->listAll()->chunkResult());

        /* Users */
        if ( $this->dbGroup->getId() )
        {
            $idUsers = $this->dbGroupUser->listUsersByGroup($this->dbGroup->getId())->result;
        }

        $lkpUser = new MLookupTextField('lkpUser', '', _M('User id', $module), 10);
        $lkpUser->setContext('admin', 'admin', 'user', 'filler', 'lkpUser, username');
        $userName = new MTextField('username', NULL, _M('Login', $module), 20);

        $users = array( $lkpUser, $userName );
        $mtUsers = new MMultiTextField3('mtUsers', $idUsers, _M('Users', $module), $users, 200, true, 'horizontal');
        $fields[] = $mtUsers;
        /* Users */

        /* Permissions */
        $transactions = $this->dbTransaction->listAll()->chunkResult();
        $perms = $MIOLO->getPerms()->perms;

        if ( $this->dbGroup->getId() )
        {
            $groupPerms = $this->dbGroup->listAccessByIdGroup($this->dbGroup->getId())->result;
        }
        //table header
        $c = 0;
        $tableTitle[$c++] = _M('Transaction', $module) . '/' . _M('Permission', $module);

        foreach ( $perms as $keyPerm => $perm )
        {
            $tableTitle[$c++] = $perm;
        }

        //table data
        $l = 0;
        $c = 0;

        foreach ( $transactions as $keyTransaction => $transaction )
        {
            $tableData[$l][$c++] = $transaction;

            foreach ( $perms as $keyPerm => $perm )
            {
                foreach ( (array) $groupPerms as $rights )
                {
                    //loads access saved
                    if ( $keyTransaction == $rights[0] && $keyPerm == $rights[1] )
                    {
                        $checked = true;
                    }
                }

                $tableData[$l][$c++] = new MCheckBox("transaction{$keyTransaction}perm{$keyPerm}", $keyPerm, NULL, $checked);

                $checked = false;
            }

            $l++;
            $c = 0;
        }

        $table = new MTableRaw('', $tableData, $tableTitle);
        $table->setAlternate(true);

        $fields[] = new MBaseGroup('baseGroupAccess', _M('Permission', $module), array( $table ));
        /* Permissions */

        $fields[] = new MButton('save', _M('Save', $module));

        $validators[] = new MRequiredValidator('group', '', _M('Group', $module));

        $this->addFields($fields);
        $this->setValidators($validators);
        $this->setFieldAttr('idGroup', 'visible', false);
    }

    public function save_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $idGroup = parent::save_click('group');

        /* Save users */
        $mtUsers = (array) $this->getFormValue('mtUsers');

        foreach ( $mtUsers as $mtUser )
        {
            $user = explode(' ', preg_replace('/\[|]/', '', $mtUser)); //removes character [ and ] and explode in array
            $users[] = $user[0];
        }
        $this->dbGroupUser->updateGroupUsers($idGroup, $users);
        /* End save Users */

        /* Save groups */
        $transactions = $this->dbTransaction->listAll()->chunkResult();
        $perms = $MIOLO->getPerms()->perms;

        foreach ( $transactions as $keyTransaction => $transaction )
        {
            foreach ( $perms as $keyPerm => $perm )
            {
                if ( $this->getFormValue("transaction{$keyTransaction}perm{$keyPerm}") )
                {
                    $access->transaction = $keyTransaction;
                    $access->rights = $keyPerm;
                    $selectedAccess[] = clone ($access);
                }
            }
        }

        $this->dbAccess->updateGroupAccess($idGroup, $selectedAccess);
        /* End save groups */
    }

    public function edit_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $this->toolbar->enableButtons(MToolBar::BUTTON_NEW);

        if ( $this->dbGroup->idGroup )
        {
            $this->setFieldValue('idGroup', $this->dbGroup->idGroup);
            $this->setFieldValue('group', $this->dbGroup->group);
            $this->setFieldValue('idModule', $this->dbGroup->idModule);
            $this->setFieldAttr('idGroup', 'visible', true);
            $this->setFieldAttr('idGroup', 'readonly', true);
        }
    }

    public function delete_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();

        $this->dbAccess->deleteAccessByGroup($this->dbGroup->idGroup);

        parent::delete_click('group', $this->dbGroup->idGroup);
    }
}

?>
