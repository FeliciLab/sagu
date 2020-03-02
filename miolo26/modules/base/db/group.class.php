<?php

class BusinessBaseGroup extends MBusiness implements IGroup
{
    public $idGroup;
    public $group;

    /**
     * @var array Access objects indexed by idTransaction.
     */
    public $access;

    /**
     * @var array User objects indexed by idUser.
     */
    public $users;

    /**
     * @var string Module name.
     */
    public $idModule;

    public function __construct($data = NULL)
    {
        parent::__construct('base', $data);
    }

    public function setData($data)
    {
        $this->idGroup = $data->idGroup;
        $this->group = $data->group;
        $this->idModule = $data->idModule;
        $this->setAccess($data->access);
    }

    public function getId()
    {
        return $this->idGroup;
    }

    public function getIdModule()
    {
        return $this->idModule;
    }

    public function getNewId()
    {
        $MIOLO = MIOLO::getInstance();
        $db = $MIOLO->getDatabase('base');
        $sql = "select (value) from miolo_sequence where sequence = 'seq_miolo_group'";
        $rs = $db->query($sql)->result;
        $id = $rs[0][0] + 1;
        return $id;
    }

    public function getById($id)
    {
        $this->idGroup = $id;
        $this->retrieve();
        return $this;
    }

    public function save()
    {
        parent::save();
    }

    public function deleteGroup()
    {
        $MIOLO = MIOLO::getInstance();
        $db = $MIOLO->getDatabase('base');
        try
        {
            // Removes access
            if ( $this->access )
            {
                foreach ( $this->access as $access )
                {
                    $where = "idgroup = '" . $access->idGroup . "' and idtransaction = '" . $access->idTransaction . "'
                              and rights = '" . $access->rights . "'";
                    $sql = new MSQL('*', 'miolo_access', $where);
                    $db->execute($sql->delete());
                }
            }
            // Removes group/user relation
            $where = "idgroup = '" . $this->idGroup . "'";
            $sql = new MSQL('*', 'miolo_groupuser', $where);
            $db->execute($sql->delete());

            // Removes group
            $where = "idgroup = '" . $this->idGroup . "'";
            $sql = new MSQL('*', 'miolo_group', $where);
            $db->execute($sql->delete());
            return true;
        }
        catch ( DatabaseException $e )
        {
            return false;
        }
    }

    public function delete()
    {
        parent::delete();
    }

    public function listRange($range = NULL)
    {
        $criteria = $this->getCriteria();
        $criteria->setRange($range);
        return $criteria->retrieveAsQuery();
    }

    public function listAll()
    {
        $criteria = $this->getCriteria();
        return $criteria->retrieveAsQuery();
    }

    public function listByGroup($group = '')
    {
        $criteria = $this->getCriteria();
        $criteria->addCriteria('group', 'LIKE', "'$group%'");
        return $criteria->retrieveAsQuery();
    }

    public function listByModule($module)
    {
        $criteria = $this->getCriteria();
        $criteria->addCriteria('idModule', '=', "'$module'");
        return $criteria->retrieveAsQuery();
    }

    public function listByFilters($filters)
    {
        $criteria = $this->getCriteria();

        if ( $filters->group )
        {
            $criteria->addCriteria( 'group', 'LIKE', "'{$filters->group}%'" );
        }

        if ( $filters->idModule )
        {
            $criteria->addCriteria('idModule', '=', "'$filters->idModule'");
        }

        return $criteria->retrieveAsQuery();
    }

    public function listUsersByIdGroup($idGroup)
    {
        $criteria = $this->getCriteria();
        $criteria->setDistinct(true);
        $criteria->addColumnAttribute('users.login');
        $criteria->addColumnAttribute('group');
        $criteria->addCriteria('idGroup', '=', "$idGroup");
        $criteria->addOrderAttribute('users.login');
        return $criteria->retrieveAsQuery();
    }

    public function listAccessByIdGroup($idGroup)
    {
        $criteria = $this->getCriteria();
        $criteria->addColumnAttribute('access.idTransaction');
        $criteria->addColumnAttribute('access.rights');
        $criteria->addCriteria('idGroup', '=', "$idGroup");
        return $criteria->retrieveAsQuery();
    }

    private function setAccess($access)
    {
        $this->access = NULL;
        if ( count($access) )
        {
            foreach ( $access as $a )
            {
                $this->access[] = $obj = $this->_miolo->getBusiness('base', 'access');
                $obj->idGroup = $this->idGroup;
                $obj->idTransaction = $a[0];
                $obj->rights = $a[1];
            }
        }
    }
    
    public function getGroups($login)
    {
        $MIOLO = MIOLO::getInstance();
        $db = $MIOLO->getDatabase('base');
        
        $sql = new MSQL();
        $sql->setColumns('upper(G.m_group)');
        $sql->setTables('
            miolo_group G LEFT JOIN 
            miolo_groupuser GU ON (GU.idgroup = G.idgroup) LEFT JOIN 
            miolo_user U ON (U.iduser = GU.iduser)
        ');
        $sql->setWhere('U.login = ?');
        $sql->addParameter($login);
        
        $result = $db->query($sql->select());
        
        $groups = array();
        foreach($result as $group)
        {
            $groups[$group[0]] = $group[0];
        }
        
        return $groups;
    }
}

?>
