<?php

class BusinessAdminUser extends MBusiness implements IUser
{

    public $idUser;

    public $login;

    public $fullname;

    public $nickname;

    public $password;

    public $hash;

    /**
     * @var array An indexed array of Group objects.
     */
    public $groups;

    /**
     * @var string Module name.
     */
    public $idModule;

    public function __construct($data = NULL)
    {
        parent::__construct( 'admin', $data );
    }

    public function getName()
    {
        return $this->fullname;
    }

    public function getId()
    {
        return $this->idUser;
    }

    public function getIdModule()
    {
        return $this->idModule;
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
        $criteria = $this->getCriteria();
        $criteria->addCriteria( 'login', '=', "'$login'" );
        $this->retrieveFromCriteria( $criteria );
        $this->getGroups();
        return $this;
    }

    public function getByLoginPass($login, $pass)
    {
        $criteria = $this->getCriteria();
        $criteria->addCriteria( 'login', '=', "'$login'" );
        $criteria->addCriteria( 'password', '=', "'$pass'" );
        $this->retrieveFromCriteria( $criteria );
        $this->getGroups();
        return $this;
    }

    public function getNewId()
    {
        $MIOLO = MIOLO::getInstance();
        $db = $MIOLO->getDatabase('admin');
        //        $sql = "update miolo_sequence set value = value + 1 where sequence = 'seq_miolo_user'";
        $sql = "select (value) from miolo_sequence where sequence = 'seq_miolo_user'";
        $rs = $db->query( $sql )->result;
        $id = $rs[0][0] + 1;
        $sql = "update miolo_sequence set value = '" . $id . "' where sequence = 'seq_miolo_user'";
        $rs = $db->execute( $sql );
        return $id;
    }

    /**
     * Created by Fernando Kochhann
     * 30/11/2006
     */
    public function insert()
    {
        $MIOLO = MIOLO::getInstance();
        $db = $MIOLO->getDatabase( 'admin' );
        $this->hash = md5( $this->password );

        $sql = "INSERT INTO miolo_user VALUES (
            '$this->idUser', 
            '$this->login',
            '$this->fullname',
            '$this->nickname',
            '$this->password',
            '$this->hash',
            '',
            '$this->idModule'
        )";

        return $db->execute( $sql );
    }

    /**
     * Created by Fernando Kochhann
     * 30/11/2006
     */
    public function update()
    {
        $MIOLO = MIOLO::getInstance();
        $db = $MIOLO->getDatabase( 'admin' );
        $this->hash = md5( $this->password );

        $sql = "UPDATE miolo_user 
                   SET login = '$this->login',
                       name = '$this->fullname',
                       nickname = '$this->nickname',
                       m_password = '$this->password',
                       confirm_hash = '$this->hash',
                       idmodule = '$this->idModule'
                 WHERE iduser = '$this->idUser'";

        return $db->execute( $sql );
    }

    public function save()
    {
        $this->hash = md5( $this->password );
        
        parent::save();
    }

    public function updatePassword($password)
    {
        $this->password = $password;
        $this->hash = md5( $this->password );
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
        $criteria = $this->getCriteria();
        $criteria->addCriteria( 'login', 'LIKE', "'{$login}%'" );
        $criteria->addOrderAttribute( 'login' );
        return $criteria->retrieveAsQuery();
    }

    public function listByModule($module)
    {
        $criteria = $this->getCriteria();
        $criteria->addCriteria('idModule', '=', "'$module'" );
        return $criteria->retrieveAsQuery();
    }

    public function listByFilters($filters)
    {
        $criteria = $this->getCriteria();
        
        if ( $filters->login )
        {
            $criteria->addCriteria( 'login', 'LIKE', "'{$filters->login}%'" );
        }
        
        if ( $filters->fullname )
        {
            $criteria->addCriteria( 'fullname', 'LIKE', "'{$filters->fullname}%'" );
        }
        
        if ( $filters->nickname )
        {
            $criteria->addCriteria( 'nickname', 'LIKE', "'{$filters->nickname}%'" );
        }

        if ( $filters->idModule )
        {
            $criteria->addCriteria('idModule', '=', "'$filters->idModule'");
        }

        return $criteria->retrieveAsQuery();
    }

    public function listRange($range = NULL)
    {
        $criteria = $this->getCriteria();
        $criteria->setRange( $range );
        return $criteria->retrieveAsQuery();
    }

    public function listAll()
    {
        $criteria = $this->getCriteria();
        return $criteria->retrieveAsQuery();
    }

    public function listGroupsByIdUser($idUser)
    {
        $criteria = $this->getCriteria();
        $criteria->setDistinct( true );
        $criteria->addColumnAttribute( 'groups.idGroup' );
        $criteria->addColumnAttribute( 'groups.group' );
        $criteria->addCriteria( 'idUser', '=', "$idUser" );
        return $criteria->retrieveAsQuery();
    }

    public function setData($data)
    {
        $this->idUser = $data->idUser;
        $this->login = $data->username; //name adjust
        $this->password = $data->password;
        $this->fullname = $data->fullname;
        $this->nickname = $data->nickname;
        $this->idModule = $data->idModule;
    }

    public function getTransactionRights($transaction, $login = NULL)
    {
        if ( is_null( $login ) )
        {
            $login = $this->login;
        }
        $transaction = strtoupper( $transaction );
        $rights = 0;
        $criteria = $this->getCriteria();
        $criteria->addColumnAttribute( 'max(groups.access.rights)', 'rights' );
        $criteria->addCriteria( 'login', '=', "'$login'" );
        $criteria->addCriteria( 'groups.access.transaction.transaction', '=', "'$transaction'" );
        $query = $criteria->retrieveAsQuery();
        if ( $query )
        {
            $rights = $query->fields( 'rights' );
        }
        return $rights;
    }

    public function getRights()
    {
        $criteria = $this->getCriteria();
        $criteria->addColumnAttribute( 'groups.access.transaction.transaction' );
        $criteria->addColumnAttribute( 'max(groups.access.rights)', 'rights' );
        $criteria->addCriteria( 'login', '=', "'{$this->login}'" );
        $criteria->addGroupAttribute( 'groups.access.transaction.transaction' );
        $criteria->addOrderAttribute( 'groups.access.transaction.transaction' );
        $query = $criteria->retrieveAsQuery();
        return $query->chunkResult( 0, 1, false );
    }

    public function getGroups()
    {
        if ( is_null( $this->groups ) )
        {
            $this->retrieveAssociation( 'groups' );
        }

    }

    public function getArrayGroups()
    {
        $aGroups = array();
        $this->getGroups();
        
        if ( count( $this->groups ) )
        {
            foreach ( $this->groups as $group )
            {
                $aGroups[$group->group] = $group->group;
            }
        }
        return $aGroups;
    }

    public function setArrayGroups($aGroups)
    {
        $this->groups = NULL;
        if ( count( $aGrupos ) )
        {
            foreach ( $aGrupos as $g )
            {
                $grupo = $this->_miolo->getBusiness( 'admin', 'group', $g );
                $this->groups[$g] = $group;
            }
        }
    }

    public function validatePassword($password)
    {
        if ( $this->password != $password )
        {
            throw new ESecurityException( 'Passwords dont matches' );
        }
        return true;
    }

    /**
     * Compare given password's md5 with stored hash.
     *
     * @param string $challenge Clean password.
     * @param string $response @deprecated
     * @return boolean Whether password is valid.
     */
    public function validatePasswordMD5($password, $response=NULL)
    {
        if ( md5($password) != $this->password )
        {
            throw new ESecurityException(_M('Password doesn\'t match'));
        }
        return true;
    }

    public function isMemberOf($group)
    {
        $ok = false;
        if ( count( $this->groups ) )
        {
            foreach ( $this->groups as $group )
            {
                $ok = $ok || ($group == $group->group);
            }
        }
        return $ok;
    }

    public function weakPassword()
    {
        return false;
    }
}
?>
