<?php

class BusinessAdminGroupUser extends MBusiness implements IGroup
{

    public $idUser;

    public $idGroup;

    public function __construct($data = NULL)
    {
        parent::__construct( 'admin', $data );
    }

    public function setData($data)
    {
        $this->idUser = $data->idUser;
        $this->idGroup = $data->idGroup;
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

    public function getById($id)
    {
    }

    public function updateUserGroups($idUser, $groups)
    {
        $MIOLO = MIOLO::getInstance();
        $db = $MIOLO->getDatabase( 'admin' );
        
        $sql = 'BEGIN';
        $db->execute( $sql );
        
        $sql = new MSQL( '', 'miolo_groupuser', "idUser = {$idUser}" );
        $db->execute( $sql->delete() );
        
        foreach ( (array) $groups as $group )
        {
            $sql = new MSQL( 'idUser, idGroup', 'miolo_groupuser' );
            $db->execute( $sql->insert( array( 
                    $idUser, 
                    $group 
            ) ) );
        }
        
        $sql = 'COMMIT';
        $db->execute( $sql );
    
    }
    
    public function updateGroupUsers($idGroup, $users)
    {
        $MIOLO = MIOLO::getInstance();
        $db = $MIOLO->getDatabase( 'admin' );
        
        $sql = 'BEGIN';
        $db->execute( $sql );
        
        $sql = new MSQL( '', 'miolo_groupuser', "idGroup = {$idGroup}" );
        $db->execute( $sql->delete() );
        
        foreach ( (array) $users as $user )
        {
            $sql = new MSQL( 'idUser, idGroup', 'miolo_groupuser' );
            $db->execute( $sql->insert( array( 
                    $user, 
                    $idGroup 
            ) ) );
        }
        
        $sql = 'COMMIT';
        $db->execute( $sql );
    
    }
    
    public function listUsersByGroup($idGroup)
    {
        $criteria = $this->getCriteria();
        $criteria->setDistinct(true);
        $criteria->addColumnAttribute('users.idUser');
        $criteria->addColumnAttribute('users.login');
        $criteria->addCriteria('idGroup','=', "$idGroup");
        return $criteria->retrieveAsQuery();
    }
}
?>
