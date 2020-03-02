<?php
class BusinessAdminUser extends MBusiness implements IUser
{
    public $idUser;
    public $login;
    public $name;
    public $nick;
    public $password;
    public $hash;
    public $groups;  // a indexed array of Group objects

    public function __construct($data = NULL)
    {
       parent::__construct('admin',$data);
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->idUser;
    }

    public function getById($id)
    {
        $this->idUser = $id;
        $this->retrieve();
        $this->getGroups();
        return $this;
    }

    public function getByLogin($login)
    {
        $criteria =  $this->getCriteria();
        $criteria->addCriteria('login','=', "'$login'");
        $this->retrieveFromCriteria($criteria);
        $this->getGroups();
        return $this;
    }

    public function getByLoginPass($login,$pass)
    {
        $criteria =  $this->getCriteria();
        $criteria->addCriteria('login','=', "'$login'");
        $criteria->addCriteria('password','=', "'$pass'");
        $this->retrieveFromCriteria($criteria);
        $this->getGroups();
        return $this;
    }

    public function save()
    {
        $this->hash = md5($this->password);
        parent::save();
    }
    
    public function updatePassword($password)
    {
        $this->password = $password;
        $this->hash = md5($this->password);
        $this->save();
    }

    public function updateHash($hash)
    {
        $this->hash = $hash;
        $this->save();
    }

    public function delete()
    {
        parent::delete();
    }

    public function listByLogin($login)
    {
        $criteria =  $this->getCriteria();
        $criteria->addCriteria('login','LIKE', "'{$login}%'");
        $criteria->addOrderAttribute('login');
        return $criteria->retrieveAsQuery();
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

    public function listGroupsByIdUser($idUser)
    {
        $criteria =  $this->getCriteria();
        $criteria->setDistinct(true);
        $criteria->addColumnAttribute('groups.idGroup');
        $criteria->addColumnAttribute('groups.group');
        $criteria->addCriteria('idUser','=', "$idUser");
        return $criteria->retrieveAsQuery();
    }

    public function getTransactionRights($transaction, $login = NULL)
    {
        if (is_null($login))
        {
            $login = $this->login;
        }
        $transaction = strtoupper($transaction);
        $rights = 0;
        $criteria =  $this->getCriteria();
        $criteria->addColumnAttribute('max(groups.access.rights)','rights');
        $criteria->addCriteria('login','=', "'$login'");
        $criteria->addCriteria('groups.access.transaction.transaction','=', "'$transaction'");
        $query = $criteria->retrieveAsQuery();
        if ( $query )
        {
            $rights = $query->fields('rights');
        }
        return $rights;
    }

    public function getRights()
    {
        $criteria =  $this->getCriteria();
        $criteria->addColumnAttribute('groups.access.transaction.transaction');
        $criteria->addColumnAttribute('max(groups.access.rights)','rights');
        $criteria->addCriteria('login','=', "'{$this->login}'");
        $criteria->addGroupAttribute('groups.access.transaction.transaction');
        $criteria->addOrderAttribute('groups.access.transaction.transaction');
        $query = $criteria->retrieveAsQuery();
        return $query->chunkResult(0,1,false);
    }

    public function getGroups()
    {
        if (is_null($this->groups))
        {
           $this->retrieveAssociation('groups');        
        }
    }

    public function getArrayGroups()
    {
        $aGroups = array();
        $this->getGroups();

        if (count($this->groups))
        {
            foreach($this->groups as $group)
            {
                $aGroups[$group->group] = $group->group;
            }
        }
        return $aGroups; 
    }

    public function setArrayGroups($aGroups)
    {
        $this->groups = NULL;
        if (count($aGrupos))
        {
            foreach($aGrupos as $g)
            {
                $grupo = $this->_miolo->getBusiness('admin','group', $g);
                $this->groups[$g] = $group;
            }
        }
    }

    public function validatePassword($password)
    {
       if ($this->password != $password)
       {
           throw new ESecurityException('Passwords dont matches');
       }
       return true;     
    }

    public function validatePasswordMD5($challenge,$response)
    {
       $hash_pass = md5(trim($this->login) . ':' . trim($this->hash) . ":" . $challenge);
       if ($hash_pass == $response)
       {
           throw new ESecurityException('Passwords dont matches');
       }
       return true;     
    }

    public function isMemberOf($group)
    {
        $ok = false;
        if (count($this->groups))
        {
            foreach($this->groups as $group)
            {
                $ok = $ok || ($group == $group->group);
            }
        }
        return $ok;
    }
}
?>