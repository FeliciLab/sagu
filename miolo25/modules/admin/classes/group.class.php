<?php

class BusinessAdminGroup extends MBusiness  implements IGroup
{
    public $idGroup;
	var $group;
    public $access; // an array of Access objects indexed by idTransaction
    public $users;  // an array of User objects indexed by idUser

    public function __construct($data = NULL)
    {
       parent::__construct('admin',$data);
    }

	public function aetData($data)
	{
		$this->idGroup = $data->idGroup;
		$this->group = $data->group;
        // $data->access: an array of array(idTransaction, rights)
        $this->setAccess($data->access);
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
    
    public function delete()
    {
        parent::delete();
    }

    public function listRange($range = NULL)
    {
        $criteria =  $this->getCriteria();
        $criteria->setRange($range);
        return $criteria->retrieveAsQuery();
    }

    public function listAll()
    {
        $criteria =  $this->getCriteria();
        return $criteria->retrieveAsQuery();
    }

    public function listUsersByIdGroup($idGroup)
    {
        $criteria = $this->getCriteria();
        $criteria->setDistinct(true);
        $criteria->addColumnAttribute('users.login');
        $criteria->addColumnAttribute('group');
        $criteria->addCriteria('idGroup','=', "$idGroup");
        $criteria->addOrderAttribute('users.login');
        return $criteria->retrieveAsQuery();
    }

    public function listAccessByIdGroup($idGroup)
    {
        $criteria =  $this->getCriteria();
        $criteria->addColumnAttribute('access.idTransaction');
        $criteria->addColumnAttribute('access.rights');
        $criteria->addCriteria('idGroup','=', "$idGroup");
        $criteria->addOrderAttribute('access.transaction.transaction');
        return $criteria->retrieveAsQuery();
    }

    private function setAccess($access)
    {
        $this->access = NULL;
        if (count($access))
        {
            foreach($access as $a)
            {
                $this->access[] = $obj = $this->_miolo->getBusiness('admin','access');
                $obj->idGroup = $this->idGroup;
                $obj->idTransaction = $a[0];
                $obj->rights = $a[1];
            }
        }
    }
}
?>