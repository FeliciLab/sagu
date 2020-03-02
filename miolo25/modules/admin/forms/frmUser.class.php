<?php

class frmUser extends AdminForm
{
    protected $dbUser;
    protected $dbGroup;

    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $this->dbUser = $MIOLO->getBusiness($module, 'user');
        $idUser = MIOLO::_request('item');
        $this->dbUser->getById($idUser);
        $this->dbGroup = $MIOLO->getBusiness($module, 'group');

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

        $title = _M('Transactions', $module) . ' - ' . $title;

        parent::__construct($title);
    }

    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        parent::createFields();

        $fields[] = new MTextField('idUser', '', _M('Id', $module));
        $fields[] = new MTextField('username', '', _M('Username', $module), 20);
        $fields[] = new MPasswordField('password', '', _M('Password', $module), 20);
        $fields[] = new MTextField('fullname', '', _M('Name', $module), 20);
        $fields[] = new MTextField('nickname', '', _M('Nickname', $module), 20);

        $modules = $MIOLO->getBusiness($module, 'module');
        $fields[] = new MSelection('idModule', NULL, _M('Module', $module), $modules->listAll()->chunkResult());

        $groupsUser = $this->dbUser->getArrayGroups();
        $groups = $this->dbGroup->listAll()->chunkResult();

        foreach ( $groups as $idGroup => $group )
        {
            foreach ( $groupsUser as $grpUser )
            {
                if ( $group == $grpUser )
                {
                    $checked = true;
                }
            }

            $tableData[$count / 3][$count % 3] = new MCheckBox("check{$idGroup}", $idGroup, NULL, $checked, $group);

            $count++;
            $checked = false;
        }

        $table = new MTableRaw('', $tableData);
        $table->setAlternate(true);
        $baseGroup = new MBaseGroup('baseGroup', _M('Groups', $module), array( $table ));
        $fields[] = $baseGroup;

        $fields[] = new MButton('save', _M('Save', $module));

        $validators[] = new MRequiredValidator('username', '', _M('Username', $module));
        $validators[] = new MRequiredValidator('password', '', _M('Password', $module));

        $this->addFields($fields);
        $this->setValidators($validators);
        $this->setFieldAttr('idUser', 'visible', false);
    }

    public function save_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $idUser = parent::save_click('user');

        $groups = $this->dbGroup->listAll()->chunkResult();

        foreach ( $groups as $key => $group )
        {
            if ( $this->getFormValue("check{$key}") )
            {
                $selectedGroups[] = $key;
            }
        }

        $groupuser = $MIOLO->getBusiness($module, 'groupuser');

        $groupuser->updateUserGroups($idUser, $selectedGroups);
    }

    public function edit_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $this->toolbar->enableButtons(MToolBar::BUTTON_NEW);

        if ( $this->dbUser->idUser )
        {
            $this->setFieldValue('idUser', $this->dbUser->idUser);
            $this->setFieldValue('username', $this->dbUser->login);
            $this->setFieldValue('password', $this->dbUser->password);
            $this->setFieldValue('fullname', $this->dbUser->fullname);
            $this->setFieldValue('nickname', $this->dbUser->nickname);
            $this->setFieldValue('idModule', $this->dbUser->idModule);
            $this->setFieldAttr('idUser', 'visible', true);
            $this->setFieldAttr('idUser', 'readonly', true);
            $this->setFieldAttr('username', 'readonly', true);
        }
    }

    public function delete_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();

        parent::delete_click('user', $this->dbUser->idUser);
    }
}

?>
