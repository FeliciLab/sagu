<?php

class BusinessAdminTransaction extends MBusiness implements ITransaction
{
    public $idTransaction;
    public $transaction;
    public $idModule;
    public $nameTransaction;
    public $parentTransaction;
    public $action;

    /**
     * @var array Access objects indexed by idTransaction.
     */
    public $access;

    public function __construct($data = NULL)
    {
        parent::__construct('admin', $data);
    }

    public function setData($data)
    {
        $this->idTransaction = $data->idTransaction;
        $this->transaction = strtoupper($data->transaction);
        $this->idModule = $data->idModule;
        $this->nameTransaction = $data->nameTransaction;
        $this->parentTransaction = $data->parentTransaction;
        $this->action = $data->transactionAction;
        $this->setAccess($data->access);
    }

    public function getId()
    {
        return $this->idTransaction;
    }

    public function getById($id)
    {
        $this->idTransaction = $id;
        $this->retrieve();
        return $this;
    }

    public function getByName($transaction)
    {
        $criteria = $this->getCriteria();
        $criteria->addCriteria('transaction', '=', "'$transaction'");
        return $this->retrieveFromCriteria($criteria);
    }

    public function listByGroup($group)
    {
        $criteria = $this->getCriteria();
        $criteria->addCriteria('access.group.group', '=', "'$group'");
        return $this->retrieveAsQuery($criteria);
    }

    public function save()
    {
        parent::save();
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

    public function listByTransaction($transaction = '')
    {
        $criteria = $this->getCriteria();
        $criteria->addCriteria('transaction', 'LIKE', "'$transaction%'");
        return $criteria->retrieveAsQuery();
    }

    public function listByModule($module)
    {
        $criteria = $this->getCriteria();
        $criteria->addCriteria('idModule', '=', "'$module'");
        return $criteria->retrieveAsQuery();
    }

    public function getArrayGroups()
    {
        $aGroups = array( );
        if ( $this->access != NULL )
        {
            if ( !is_array($this->access) )
            {
                $this->access = array(
                    $this->access
                );
            }
            foreach ( $this->access as $a )
            {
                $aGroups[] = array(
                    $a->idGroup,
                    $a->rights
                );
            }
        }
        return $aGroups;
    }

    public function listAccess()
    {
        $criteria = $this->getCriteria();
        $criteria->addColumnAttribute('access.idGroup');
        $criteria->addColumnAttribute('access.group.group');
        $criteria->addColumnAttribute('access.rights');
        $criteria->addCriteria('idTransaction', '=', "'{$this->idTransaction}'");
        $criteria->addOrderAttribute('access.idGroup');
        return $criteria->retrieveAsQuery();
    }

    public function listAccessByIdTransaction($idTransaction)
    {
        $criteria = $this->getCriteria();
        $criteria->addColumnAttribute('access.idGroup');
        $criteria->addColumnAttribute('access.group.group');
        $criteria->addColumnAttribute('access.rights');
        $criteria->addCriteria('idTransaction', '=', "'{$idTransaction}'");
        $criteria->addOrderAttribute('access.idGroup');
        return $criteria->retrieveAsQuery();
    }

    private function setAccess($access)
    {
        $this->access = NULL;
        if ( count($access) )
        {
            foreach ( $access as $a )
            {
                $this->access[] = $obj = $this->_miolo->getBusiness('admin', 'access');
                $obj->idGroup = $this->idGroup;
                $obj->idTransaction = $a[0];
                $obj->rights = $a[1];
            }
        }
    }

    public function getUsersAllowed($action = A_ACCESS)
    {
    }

    public function getGroupsAllowed($action = A_ACCESS)
    {
    }
}

?>